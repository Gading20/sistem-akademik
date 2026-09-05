<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\StoreQuestionRequest;
use App\Http\Requests\Exam\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request, QuestionBank $questionBank): View
    {
        $this->authorize('viewAny', Question::class);

        $search = $request->input('search');
        $type = $request->input('type');
        $difficulty = $request->input('difficulty');

        $questions = Question::with('options')
            ->where('question_bank_id', $questionBank->id)
            ->when($search, fn ($q) => $q->where('question', 'like', "%{$search}%"))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($difficulty, fn ($q) => $q->where('difficulty', $difficulty))
            ->latest()
            ->paginate(15);

        return view('exam.questions.index', compact('questions', 'questionBank', 'search', 'type', 'difficulty'));
    }

    public function create(QuestionBank $questionBank): View
    {
        $this->authorize('create', Question::class);

        return view('exam.questions.create', compact('questionBank'));
    }

    public function store(StoreQuestionRequest $request, QuestionBank $questionBank): RedirectResponse
    {
        $this->authorize('create', Question::class);

        return DB::transaction(function () use ($request, $questionBank) {
            $data = $request->validated();
            $optionsData = $data['options'] ?? [];
            unset($data['options']);

            $data['question_bank_id'] = $questionBank->id;

            $question = Question::create($data);

            foreach ($optionsData as $index => $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $optionData['is_correct'] ?? false,
                    'order' => $index + 1,
                ]);
            }

            $this->auditLogService->log(
                Auth::user(),
                'created',
                Question::class,
                $question->id,
                null,
                $question->toArray()
            );

            return redirect()->route('exam.questions.index', $questionBank)
                ->with('success', 'Soal berhasil dibuat.');
        });
    }

    public function edit(QuestionBank $questionBank, Question $question): View
    {
        $this->authorize('update', $question);

        $question->load('options');

        return view('exam.questions.edit', compact('questionBank', 'question'));
    }

    public function update(UpdateQuestionRequest $request, QuestionBank $questionBank, Question $question): RedirectResponse
    {
        $this->authorize('update', $question);

        return DB::transaction(function () use ($request, $questionBank, $question) {
            $data = $request->validated();
            $optionsData = $data['options'] ?? null;
            unset($data['options']);

            $oldData = $question->toArray();
            $question->update($data);

            if ($optionsData !== null) {
                $question->options()->delete();

                foreach ($optionsData as $index => $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'order' => $index + 1,
                    ]);
                }
            }

            $this->auditLogService->log(
                Auth::user(),
                'updated',
                Question::class,
                $question->id,
                $oldData,
                $question->fresh()->toArray()
            );

            return redirect()->route('exam.questions.index', $questionBank)
                ->with('success', 'Soal berhasil diperbarui.');
        });
    }

    public function destroy(QuestionBank $questionBank, Question $question): RedirectResponse
    {
        $this->authorize('delete', $question);

        $question->options()->delete();
        $question->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Question::class,
            $question->id
        );

        return redirect()->route('exam.questions.index', $questionBank)
            ->with('success', 'Soal berhasil dihapus.');
    }
}
