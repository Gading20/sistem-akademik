<?php

namespace App\Http\Controllers\Academic;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreAssignmentRequest;
use App\Http\Requests\Academic\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Assignment::class);

        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');

        $assignments = Assignment::with(['subject', 'classRoom', 'teacher.user'])
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->latest()
            ->paginate(15);

        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        return view('academic.assignments.index', compact('assignments', 'classes', 'subjects', 'classId', 'subjectId'));
    }

    public function create(): View
    {
        $this->authorize('create', Assignment::class);

        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::with('user')->active()->get();
        $isGuru = auth()->user()->hasRole(RoleEnum::GURU->value);

        return view('academic.assignments.create', compact('classes', 'subjects', 'teachers', 'isGuru'));
    }

    public function store(StoreAssignmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Assignment::class);

        $assignment = Assignment::create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            Assignment::class,
            $assignment->id,
            null,
            $assignment->toArray()
        );

        return redirect()->route('academic.assignments.index')
            ->with('success', 'Tugas berhasil dibuat.');
    }

    public function edit(Assignment $assignment): View
    {
        $this->authorize('update', $assignment);

        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::with('user')->active()->get();
        $isGuru = auth()->user()->hasRole(RoleEnum::GURU->value);

        return view('academic.assignments.edit', compact('assignment', 'classes', 'subjects', 'teachers', 'isGuru'));
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $this->authorize('update', $assignment);

        $oldData = $assignment->toArray();
        $assignment->update($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            Assignment::class,
            $assignment->id,
            $oldData,
            $assignment->fresh()->toArray()
        );

        return redirect()->route('academic.assignments.index')
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->authorize('delete', $assignment);

        $assignment->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Assignment::class,
            $assignment->id
        );

        return redirect()->route('academic.assignments.index')
            ->with('success', 'Tugas berhasil dihapus.');
    }

    public function submissions(Assignment $assignment): View
    {
        $this->authorize('view', $assignment);

        $submissions = AssignmentSubmission::with('student.user', 'grader')
            ->where('assignment_id', $assignment->id)
            ->latest('submitted_at')
            ->get();

        return view('academic.assignments.submissions', compact('assignment', 'submissions'));
    }

    public function grade(Request $request, Assignment $assignment, AssignmentSubmission $submission): RedirectResponse
    {
        $this->authorize('update', $assignment);

        $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:' . $assignment->max_score],
            'feedback' => ['nullable', 'string'],
        ]);

        $submission->update([
            'score' => $request->score,
            'feedback' => $request->feedback,
            'graded_by' => Auth::id(),
            'graded_at' => now(),
        ]);

        $this->auditLogService->log(
            Auth::user(),
            'graded',
            AssignmentSubmission::class,
            $submission->id,
            null,
            $submission->toArray()
        );

        return back()->with('success', 'Penilaian berhasil disimpan.');
    }
}
