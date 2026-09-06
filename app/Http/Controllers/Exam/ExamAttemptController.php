<?php

namespace App\Http\Controllers\Exam;

use App\Enums\ExamAttemptStatusEnum;
use App\Enums\ExamStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Services\AuditLogService;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExamAttemptController extends Controller
{
    public function __construct(
        protected ExamService $examService,
        protected AuditLogService $auditLogService,
    ) {}

    public function available(Request $request): View
    {
        $this->authorize('viewAny', Exam::class);

        $student = Auth::user()->student;
        $classIds = $student->activeClassIds();

        // Semua ujian published/active yang ditugaskan ke kelas siswa — termasuk
        // yang jadwalnya sudah lewat tapi belum dikerjakan, agar terlihat sebagai
        // "Tidak tersedia" (ujian terlewat).
        $exams = Exam::with(['subject', 'teacher.user'])
            ->withCount('examQuestions')
            ->whereIn('status', [ExamStatusEnum::PUBLISHED, ExamStatusEnum::ACTIVE])
            ->where(function ($q) use ($classIds) {
                $q->whereDoesntHave('examClasses')
                    ->orWhereHas('examClasses', fn ($cq) => $cq->whereIn('class_id', $classIds));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%');
            })
            ->latest()
            ->get();

        $now = now();
        $studentAttemptCounts = ExamAttempt::where('student_id', $student->id)
            ->whereIn('status', [ExamAttemptStatusEnum::SUBMITTED, ExamAttemptStatusEnum::GRADED])
            ->selectRaw('exam_id, COUNT(*) as attempt_count')
            ->groupBy('exam_id')
            ->pluck('attempt_count', 'exam_id');

        $studentLastAttempts = ExamAttempt::where('student_id', $student->id)
            ->whereIn('status', [ExamAttemptStatusEnum::SUBMITTED, ExamAttemptStatusEnum::GRADED])
            ->latest()
            ->get()
            ->groupBy('exam_id')
            ->map(fn ($attempts) => $attempts->first());

        // Percobaan yang masih berjalan (sedang dikerjakan), agar bisa dilanjutkan.
        $studentInProgressAttempts = ExamAttempt::where('student_id', $student->id)
            ->where('status', ExamAttemptStatusEnum::IN_PROGRESS)
            ->latest()
            ->get()
            ->groupBy('exam_id')
            ->map(fn ($attempts) => $attempts->first());

        $exams = $exams->map(function ($exam) use ($studentAttemptCounts, $studentLastAttempts, $studentInProgressAttempts, $now) {
            $attemptCount = $studentAttemptCounts->get($exam->id, 0);
            $exam->has_attempted = $attemptCount > 0;
            $exam->last_attempt = $studentLastAttempts->get($exam->id);
            $exam->in_progress_attempt = $studentInProgressAttempts->get($exam->id);
            $exam->can_attempt = $attemptCount < $exam->attempt_limit;
            $exam->not_started = $exam->start_at && $now->lt($exam->start_at);
            $exam->ended = $exam->end_at && $now->gt($exam->end_at);
            $exam->student_status = $this->resolveStudentStatus($exam);

            return $exam;
        });

        // Filter status (dihitung per ujian, jadi difilter setelah transform).
        $allowedStatuses = ['available', 'in_progress', 'completed', 'unavailable'];
        if ($request->filled('status') && in_array($request->status, $allowedStatuses, true)) {
            $exams = $exams->where('student_status', $request->status);
        }

        $exams = $exams->values();

        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $exams = new LengthAwarePaginator(
            $exams->forPage($page, $perPage)->values(),
            $exams->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('exam.exams.available', compact('exams'));
    }

    /**
     * Status ujian dari sudut pandang siswa: sedang dikerjakan, selesai,
     * tersedia (belum dikerjakan), atau tidak tersedia.
     */
    protected function resolveStudentStatus($exam): string
    {
        if ($exam->in_progress_attempt) {
            return 'in_progress';
        }

        if ($exam->has_attempted) {
            return 'completed';
        }

        if ($exam->not_started || $exam->ended || ! $exam->can_attempt) {
            return 'unavailable';
        }

        return 'available';
    }

    public function start(Exam $exam): RedirectResponse
    {
        $this->authorize('take', $exam);

        try {
            $student = Auth::user()->student;
            $attempt = $this->examService->startAttempt($exam, $student);

            $this->auditLogService->log(
                Auth::user(),
                'started_exam',
                ExamAttempt::class,
                $attempt->id
            );

            return redirect()->route('exam.exams.attempt', $attempt);
        } catch (\Exception $e) {
            return back()->withErrors(['exam' => $e->getMessage()]);
        }
    }

    public function show(ExamAttempt $examAttempt): RedirectResponse|View
    {
        if (Auth::id() !== $examAttempt->student->user_id) {
            abort(403);
        }

        // Waktu pengerjaan dihitung dari sisi server (tidak bergantung pada JavaScript).
        $startedAt = $examAttempt->started_at ?? now();
        $remainingTime = max(0, (int) $startedAt
            ->copy()
            ->addMinutes((int) $examAttempt->exam->duration_minutes)
            ->diffInSeconds(now()));

        // Waktu sudah habis dan percobaan masih berjalan → submit otomatis di sisi server.
        if ($remainingTime <= 0 && $examAttempt->status === ExamAttemptStatusEnum::IN_PROGRESS->value) {
            try {
                $attempt = $this->examService->submitAttempt($examAttempt);

                $this->auditLogService->log(
                    Auth::user(),
                    'submitted_exam',
                    ExamAttempt::class,
                    $attempt->id
                );

                return redirect()->route('exam.exams.result', $attempt)
                    ->with('success', 'Waktu pengerjaan ujian telah habis. Jawaban disubmit otomatis.');
            } catch (\Exception $e) {
                // Jangan sampai siswa terkunci — tetap tampilkan halaman bila submit otomatis gagal.
            }
        }

        $examAttempt->load([
            'exam.subject',
            'exam.examQuestions.question.options',
            'answers',
        ]);

        $questions = $examAttempt->exam->random_question
            ? $examAttempt->exam->examQuestions->shuffle()->values()
            : $examAttempt->exam->examQuestions->sortBy('order')->values();

        $answers = $examAttempt->answers->keyBy('question_id');

        return view('exam.exams.take', compact('examAttempt', 'questions', 'answers', 'remainingTime'));
    }

    public function answer(Request $request, ExamAttempt $examAttempt)
    {
        if (Auth::id() !== $examAttempt->student->user_id) {
            abort(403);
        }

        if ($examAttempt->status !== ExamAttemptStatusEnum::IN_PROGRESS->value) {
            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => 'Percobaan ujian tidak aktif.'], 422)
                : back()->withErrors(['attempt' => 'Percobaan ujian tidak aktif.']);
        }

        $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'selected_option_id' => ['nullable', 'integer', 'exists:question_options,id'],
            'essay_answer' => ['nullable', 'string'],
            'answer' => ['nullable', 'string', 'max:2048'],
        ]);

        $question = Question::findOrFail($request->question_id);

        $belongsToExam = $examAttempt->exam->examQuestions()
            ->where('question_id', $question->id)
            ->exists();

        if (! $belongsToExam) {
            abort(422, 'Soal tidak termasuk dalam ujian ini.');
        }

        // Pilihan jawaban harus milik soal yang bersangkutan (anti injeksi
        // pilihan jawaban dari soal lain).
        if ($request->filled('selected_option_id')
            && ! $question->options()->where('id', $request->selected_option_id)->exists()) {
            abort(422, 'Pilihan jawaban tidak valid untuk soal ini.');
        }

        $isComplex = $question->type === QuestionTypeEnum::MCQ_COMPLEX->value;

        $data = [
            'selected_option_id' => ! $isComplex ? $request->selected_option_id : null,
            'essay_answer' => $request->essay_answer,
            'answer' => $isComplex ? $request->answer : null,
        ];

        $existingAnswer = $examAttempt->answers()
            ->where('question_id', $question->id)
            ->first();

        if ($existingAnswer) {
            $existingAnswer->update($data);
        } else {
            $examAttempt->answers()->create(array_merge(['question_id' => $question->id], $data));
        }

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Jawaban berhasil disimpan.');
    }

    public function submit(ExamAttempt $examAttempt): RedirectResponse
    {
        if (Auth::id() !== $examAttempt->student->user_id) {
            abort(403);
        }

        if ($examAttempt->status !== 'in_progress') {
            return back()->withErrors(['attempt' => 'Percobaan ujian tidak aktif.']);
        }

        try {
            $attempt = $this->examService->submitAttempt($examAttempt);

            $this->auditLogService->log(
                Auth::user(),
                'submitted_exam',
                ExamAttempt::class,
                $attempt->id
            );

            return redirect()->route('exam.exams.result', $attempt)
                ->with('success', 'Ujian berhasil disubmit.');
        } catch (\Exception $e) {
            return back()->withErrors(['attempt' => $e->getMessage()]);
        }
    }

    public function result(ExamAttempt $examAttempt): View
    {
        if (Auth::id() !== $examAttempt->student->user_id) {
            abort(403);
        }

        $examAttempt->load([
            'exam.subject',
            'exam.examQuestions.question.options',
            'answers.question.options',
            'answers.question.correctOption',
            'answers.selectedOption',
        ]);

        return view('exam.exams.result', compact('examAttempt'));
    }
}
