<?php

namespace App\Http\Controllers\Academic;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreJournalRequest;
use App\Http\Requests\Academic\UpdateJournalRequest;
use App\Models\ClassRoom;
use App\Models\Journal;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Journal::class);

        $teacherId = $request->input('teacher_id');
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $date = $request->input('date');

        $user = Auth::user();
        $isRestricted = $user->hasRole(RoleEnum::GURU->value, RoleEnum::WALI_KELAS->value);

        $query = Journal::with(['teacher.user', 'classRoom', 'subject', 'schedule.teachingAssignment']);

        if ($isRestricted) {
            $query->where('teacher_id', $user->teacher?->id);
        } else {
            $query->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId));
        }

        $query->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($date, fn ($q) => $q->whereDate('date', $date));

        $journals = $query->latest('date')->latest('id')->paginate(15)->withQueryString();

        $teachers = Teacher::with('user')->active()->orderBy('nip')->get();
        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        return view('academic.journals.index', compact(
            'journals',
            'teachers',
            'classes',
            'subjects',
            'teacherId',
            'classId',
            'subjectId',
            'date',
            'isRestricted'
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Journal::class);

        $user = Auth::user();
        $isGuru = $user->hasRole(RoleEnum::GURU->value);

        $teachers = Teacher::with('user')->active()->get();
        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $schedules = $this->schedulesForUser($user);

        return view('academic.journals.create', compact('teachers', 'classes', 'subjects', 'schedules', 'isGuru'));
    }

    public function store(StoreJournalRequest $request): RedirectResponse
    {
        $this->authorize('create', Journal::class);

        $journal = Journal::create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            Journal::class,
            $journal->id,
            null,
            $journal->toArray()
        );

        return redirect()->route('academic.journals.index')
            ->with('success', 'Jurnal berhasil dibuat.');
    }

    public function edit(Journal $journal): View
    {
        $this->authorize('update', $journal);

        $user = Auth::user();
        $isGuru = $user->hasRole(RoleEnum::GURU->value);

        $teachers = Teacher::with('user')->active()->get();
        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $schedules = $this->schedulesForUser($journal->teacher);

        return view('academic.journals.edit', compact('journal', 'teachers', 'classes', 'subjects', 'schedules', 'isGuru'));
    }

    public function update(UpdateJournalRequest $request, Journal $journal): RedirectResponse
    {
        $this->authorize('update', $journal);

        $oldData = $journal->toArray();
        $journal->update($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            Journal::class,
            $journal->id,
            $oldData,
            $journal->fresh()->toArray()
        );

        return redirect()->route('academic.journals.index')
            ->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(Journal $journal): RedirectResponse
    {
        $this->authorize('delete', $journal);

        $journal->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Journal::class,
            $journal->id
        );

        return redirect()->route('academic.journals.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }

    /**
     * Schedules relevant to a teacher record (fall back to all schedules for admins).
     */
    protected function schedulesForUser($userOrTeacher): \Illuminate\Support\Collection
    {
        $teacher = $userOrTeacher?->teacher ?? $userOrTeacher;

        $query = Schedule::query()
            ->with(['teachingAssignment.classRoom', 'teachingAssignment.subject', 'room'])
            ->orderBy('day');

        if ($teacher) {
            $query->whereHas('teachingAssignment', fn ($q) => $q->where('teacher_id', $teacher->id));
        }

        return $query->get();
    }
}
