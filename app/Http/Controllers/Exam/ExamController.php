<?php

namespace App\Http\Controllers\Exam;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\StoreExamRequest;
use App\Http\Requests\Exam\UpdateExamRequest;
use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AuditLogService;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(
        protected ExamService $examService,
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Exam::class);

        $search = $request->input('search');
        $type = $request->input('type');
        $status = $request->input('status');

        $user = Auth::user();

        $exams = Exam::with(['subject', 'teacher.user', 'classes'])
            ->withCount('examQuestions')
            ->when($user->hasRole(RoleEnum::GURU->value), fn ($q) => $q->where('teacher_id', $user->teacher?->id))
            ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        return view('exam.exams.index', compact('exams', 'search', 'type', 'status'));
    }

    public function create(): View
    {
        $this->authorize('create', Exam::class);

        $user = Auth::user();
        $isGuru = $user->hasRole(RoleEnum::GURU->value);

        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::with('user')->active()->get();
        $academicYears = \App\Models\AcademicYear::orderByDesc('start_date')->get();
        $semesters = \App\Models\Semester::orderByDesc('start_date')->get();
        $classes = \App\Models\ClassRoom::active()->orderBy('name')->get();
        $questionBanks = QuestionBank::with(['questions' => fn ($q) => $q->where('is_active', true)->orderBy('id')])
            ->withCount('questions')
            ->orderBy('name')
            ->get();

        $defaultAcademicYearId = $academicYears->firstWhere('is_active', true)?->id ?? $academicYears->first()?->id;
        $defaultSemesterId = $semesters->firstWhere('is_active', true)?->id ?? $semesters->first()?->id;

        return view('exam.exams.create', compact(
            'subjects',
            'teachers',
            'academicYears',
            'semesters',
            'classes',
            'questionBanks',
            'isGuru',
            'defaultAcademicYearId',
            'defaultSemesterId'
        ));
    }

    public function store(StoreExamRequest $request): RedirectResponse
    {
        $this->authorize('create', Exam::class);

        $exam = $this->examService->create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            Exam::class,
            $exam->id,
            null,
            $exam->toArray()
        );

        return redirect()->route('exam.exams.index')
            ->with('success', 'Ujian berhasil dibuat.');
    }

    public function edit(Exam $exam): View
    {
        $this->authorize('update', $exam);

        $user = Auth::user();
        $isGuru = $user->hasRole(RoleEnum::GURU->value);

        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::with('user')->active()->get();
        $academicYears = \App\Models\AcademicYear::orderByDesc('start_date')->get();
        $semesters = \App\Models\Semester::orderByDesc('start_date')->get();
        $classes = \App\Models\ClassRoom::active()->orderBy('name')->get();
        $questionBanks = QuestionBank::with(['questions' => fn ($q) => $q->where('is_active', true)->orderBy('id')])
            ->withCount('questions')
            ->orderBy('name')
            ->get();

        return view('exam.exams.edit', compact('exam', 'subjects', 'teachers', 'academicYears', 'semesters', 'classes', 'questionBanks', 'isGuru'));
    }

    public function update(UpdateExamRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam);

        $oldData = $exam->toArray();
        $exam = $this->examService->update($request->validated(), $exam);

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            Exam::class,
            $exam->id,
            $oldData,
            $exam->fresh()->toArray()
        );

        return redirect()->route('exam.exams.index')
            ->with('success', 'Ujian berhasil diperbarui.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $this->authorize('delete', $exam);

        $exam->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Exam::class,
            $exam->id
        );

        return redirect()->route('exam.exams.index')
            ->with('success', 'Ujian berhasil dihapus.');
    }

    public function publish(Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam);

        try {
            $exam = $this->examService->publish($exam);

            $this->auditLogService->log(
                Auth::user(),
                'published',
                Exam::class,
                $exam->id
            );

            return redirect()->route('exam.exams.index')
                ->with('success', 'Ujian berhasil dipublikasikan.');
        } catch (\Exception $e) {
            // Jika belum ada soal terpilih, arahkan langsung ke halaman edit
            // agar guru bisa mencentang soal di bagian "Pilih Soal".
            if ($exam->examQuestions()->count() === 0) {
                return redirect()->route('exam.exams.edit', $exam)
                    ->withErrors(['questions' => $e->getMessage()]);
            }

            return back()->withErrors(['exam' => $e->getMessage()]);
        }
    }

    public function results(Exam $exam): View
    {
        $this->authorize('view', $exam);

        $results = $this->examService->getExamResult($exam);

        return view('exam.exams.results', array_merge($results, ['exam' => $exam]));
    }
}
