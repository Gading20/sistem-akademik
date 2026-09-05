<?php

namespace App\Http\Controllers\Exam;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\StoreQuestionBankRequest;
use App\Http\Requests\Exam\UpdateQuestionBankRequest;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QuestionBankController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', QuestionBank::class);

        $search = $request->input('search');
        $subjectId = $request->input('subject_id');

        $questionBanks = QuestionBank::with(['subject', 'teacher.user'])
            ->withCount('questions')
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15);

        $subjects = Subject::active()->orderBy('name')->get();

        return view('exam.question-banks.index', compact('questionBanks', 'subjects', 'search', 'subjectId'));
    }

    public function create(): View
    {
        $this->authorize('create', QuestionBank::class);

        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::with('user')->active()->get();
        $isGuru = auth()->user()->hasRole(RoleEnum::GURU->value);

        return view('exam.question-banks.create', compact('subjects', 'teachers', 'isGuru'));
    }

    public function store(StoreQuestionBankRequest $request): RedirectResponse
    {
        $this->authorize('create', QuestionBank::class);

        $questionBank = QuestionBank::create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            QuestionBank::class,
            $questionBank->id,
            null,
            $questionBank->toArray()
        );

        return redirect()->route('exam.question-banks.index')
            ->with('success', 'Bank soal berhasil dibuat.');
    }

    public function edit(QuestionBank $questionBank): View
    {
        $this->authorize('update', $questionBank);

        $subjects = Subject::active()->orderBy('name')->get();
        $teachers = Teacher::with('user')->active()->get();
        $isGuru = auth()->user()->hasRole(RoleEnum::GURU->value);

        return view('exam.question-banks.edit', compact('questionBank', 'subjects', 'teachers', 'isGuru'));
    }

    public function update(UpdateQuestionBankRequest $request, QuestionBank $questionBank): RedirectResponse
    {
        $this->authorize('update', $questionBank);

        $oldData = $questionBank->toArray();
        $questionBank->update($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            QuestionBank::class,
            $questionBank->id,
            $oldData,
            $questionBank->fresh()->toArray()
        );

        return redirect()->route('exam.question-banks.index')
            ->with('success', 'Bank soal berhasil diperbarui.');
    }

    public function destroy(QuestionBank $questionBank): RedirectResponse
    {
        $this->authorize('delete', $questionBank);

        $questionBank->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            QuestionBank::class,
            $questionBank->id
        );

        return redirect()->route('exam.question-banks.index')
            ->with('success', 'Bank soal berhasil dihapus.');
    }
}
