<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreTeacherRequest;
use App\Http\Requests\Master\UpdateTeacherRequest;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AuditLogService;
use App\Services\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherService $teacherService,
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Teacher::class);

        $search = $request->input('search');
        $subjectId = $request->input('subject_id');

        $teachers = Teacher::with(['user', 'subject'])
            ->when($search, function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            })
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->active()
            ->latest()
            ->paginate(15);

        $subjects = Subject::orderBy('name')->get();

        return view('master.teachers.index', compact('teachers', 'subjects', 'search', 'subjectId'));
    }

    public function create(): View
    {
        $this->authorize('create', Teacher::class);

        $subjects = Subject::active()->orderBy('name')->get();

        return view('master.teachers.create', compact('subjects'));
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        $this->authorize('create', Teacher::class);

        $teacher = $this->teacherService->create($request->validated());

        return redirect()->route('master.teachers.index')
            ->with('success', 'Guru berhasil dibuat.');
    }

    public function edit(Teacher $teacher): View
    {
        $this->authorize('update', $teacher);

        $subjects = Subject::active()->orderBy('name')->get();

        return view('master.teachers.edit', compact('teacher', 'subjects'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $this->authorize('update', $teacher);

        $teacher = $this->teacherService->update($teacher, $request->validated());

        return redirect()->route('master.teachers.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $this->authorize('delete', $teacher);

        $teacher->user()->delete();
        $teacher->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Teacher::class,
            $teacher->id
        );

        return redirect()->route('master.teachers.index')
            ->with('success', 'Guru berhasil dihapus.');
    }
}
