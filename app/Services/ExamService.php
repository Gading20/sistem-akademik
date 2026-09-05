<?php

namespace App\Services;

use App\Enums\ExamAttemptStatusEnum;
use App\Enums\ExamStatusEnum;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamService
{
    public function __construct(
        protected ExamScoringService $scoringService,
    ) {}

    public function create(array $data): Exam
    {
        return DB::transaction(function () use ($data) {
            $questionIds = $this->normalizeQuestionIds($data['questions'] ?? []);
            $classIds = array_values(array_filter((array) ($data['class_ids'] ?? [])));

            unset($data['questions'], $data['class_ids']);

            $data['status'] = ExamStatusEnum::DRAFT;
            $data['token'] = empty($data['token']) ? Str::random(6) : $data['token'];

            $exam = Exam::create($data);

            $this->syncQuestions($exam, $questionIds);
            $this->syncClasses($exam, $classIds);

            return $exam->load(['examQuestions.question.options', 'classes']);
        });
    }

    public function update(array $data, Exam $exam): Exam
    {
        return DB::transaction(function () use ($data, $exam) {
            if (array_key_exists('questions', $data)) {
                $questionIds = $this->normalizeQuestionIds($data['questions']);
                unset($data['questions']);
                $this->syncQuestions($exam, $questionIds);
            }

            if (array_key_exists('class_ids', $data)) {
                $classIds = array_values(array_filter((array) $data['class_ids']));
                unset($data['class_ids']);
                $this->syncClasses($exam, $classIds);
            }

            $exam->update($data);

            return $exam->fresh(['examQuestions.question.options', 'classes']);
        });
    }

    protected function normalizeQuestionIds(mixed $questions): array
    {
        $ids = array_values(array_filter((array) $questions, fn ($id) => $id !== null && $id !== ''));

        return array_map('intval', array_unique($ids));
    }

    protected function syncQuestions(Exam $exam, array $questionIds): void
    {
        $exam->examQuestions()->delete();

        if (empty($questionIds)) {
            return;
        }

        $questionPoints = Question::whereIn('id', $questionIds)
            ->pluck('points', 'id');

        foreach (array_values($questionIds) as $index => $questionId) {
            $exam->examQuestions()->create([
                'question_id' => $questionId,
                'order' => $index + 1,
                'points' => $questionPoints[$questionId] ?? 1,
            ]);
        }
    }

    protected function syncClasses(Exam $exam, array $classIds): void
    {
        $exam->examClasses()->delete();

        foreach ($classIds as $classId) {
            $exam->examClasses()->create(['class_id' => $classId]);
        }
    }

    public function publish(Exam $exam): Exam
    {
        if ($exam->status !== ExamStatusEnum::DRAFT) {
            throw new \Exception('Hanya ujian dengan status draft yang bisa dipublikasikan.');
        }

        if ($exam->examQuestions()->count() === 0) {
            throw new \Exception('Belum ada soal yang dipilih. Centang minimal satu soal pada bagian "Pilih Soal" untuk bisa menerbitkan ujian ini.');
        }

        $exam->update(['status' => ExamStatusEnum::PUBLISHED]);

        return $exam->fresh();
    }

    public function startAttempt(Exam $exam, Student $student): ExamAttempt
    {
        if ($exam->status !== ExamStatusEnum::PUBLISHED && $exam->status !== ExamStatusEnum::ACTIVE) {
            throw new \Exception('Ujian belum tersedia untuk dikerjakan.');
        }

        // Percobaan yang masih berjalan tetap bisa dilanjutkan (timer internal yang mengatur),
        // meskipun batas jadwal end_at sudah lewat.
        $inProgress = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', ExamAttemptStatusEnum::IN_PROGRESS)
            ->first();

        if ($inProgress) {
            return $inProgress;
        }

        // Cek jadwal berlangsung di sisi server (tidak bergantung pada JS).
        $now = now();

        if ($exam->start_at && $now->lt($exam->start_at)) {
            throw new \Exception('Ujian belum dimulai. Silakan tunggu jadwal mulai ujian.');
        }

        if ($exam->end_at && $now->gt($exam->end_at)) {
            throw new \Exception('Waktu pengerjaan ujian telah berakhir.');
        }

        $attemptCount = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->whereIn('status', [ExamAttemptStatusEnum::SUBMITTED, ExamAttemptStatusEnum::GRADED])
            ->count();

        if ($attemptCount >= $exam->attempt_limit) {
            throw new \Exception('Anda telah mencapai batas maksimal pengerjaan ujian.');
        }

        if ($exam->status === ExamStatusEnum::PUBLISHED) {
            $exam->update(['status' => ExamStatusEnum::ACTIVE]);
        }

        $attemptNumber = $attemptCount + 1;

        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'attempt_number' => $attemptNumber,
            'status' => ExamAttemptStatusEnum::IN_PROGRESS,
            'started_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function submitAttempt(ExamAttempt $attempt): ExamAttempt
    {
        if ($attempt->status !== ExamAttemptStatusEnum::IN_PROGRESS->value) {
            throw new \Exception('Percobaan ujian ini tidak bisa disubmit.');
        }

        return DB::transaction(function () use ($attempt) {
            // Jawaban sudah tersimpan lewat autosave pada tabel exam_answers,
            // cukup tandai selesai lalu hitung nilai.
            $attempt->update([
                'status' => ExamAttemptStatusEnum::SUBMITTED,
                'submitted_at' => now(),
            ]);

            $this->scoringService->scoreAttempt($attempt);

            return $attempt->fresh();
        });
    }

    public function getExamResult(Exam $exam): array
    {
        $attempts = ExamAttempt::with(['student.user', 'answers'])
            ->where('exam_id', $exam->id)
            ->submitted()
            ->get();

        $scores = $attempts->pluck('score')->filter()->values();

        return [
            'exam' => $exam,
            'total_attempts' => $attempts->count(),
            'average_score' => $scores->isNotEmpty() ? $scores->avg() : 0,
            'highest_score' => $scores->isNotEmpty() ? $scores->max() : 0,
            'lowest_score' => $scores->isNotEmpty() ? $scores->min() : 0,
            'pass_count' => $attempts->where('percentage', '>=', 50)->count(),
            'fail_count' => $attempts->where('percentage', '<', 50)->count(),
            'pass_rate' => $attempts->isNotEmpty()
                ? round($attempts->where('percentage', '>=', 50)->count() / $attempts->count() * 100, 2)
                : 0,
            'attempts' => $attempts,
        ];
    }
}
