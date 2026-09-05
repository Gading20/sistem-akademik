<?php

namespace App\Services;

use App\Enums\ExamAttemptStatusEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\QuestionOption;

class ExamScoringService
{
    public function scoreAttempt(ExamAttempt $attempt): ExamAttempt
    {
        $exam = $attempt->exam;
        $totalScore = 0;

        $answers = $attempt->answers()->with(['question.options', 'selectedOption'])->get();

        foreach ($answers as $answer) {
            $question = $answer->question;
            $earnedScore = 0;

            switch ($question->type) {
                case QuestionTypeEnum::MCQ:
                    $isCorrect = $answer->selected_option_id !== null
                        && $question->options()
                            ->where('id', $answer->selected_option_id)
                            ->where('is_correct', true)
                            ->exists();

                    $earnedScore = $isCorrect ? $question->points : 0;
                    $answer->update(['is_correct' => $isCorrect, 'points_earned' => $earnedScore]);
                    break;

                case QuestionTypeEnum::MCQ_COMPLEX:
                    $correctOptionIds = $question->options()
                        ->where('is_correct', true)
                        ->pluck('id')
                        ->sort()
                        ->values()
                        ->toArray();

                    $decoded = json_decode((string) ($answer->answer ?: $answer->selected_option_id), true);
                    $selectedIds = is_array($decoded)
                        ? collect($decoded)->map('intval')->sort()->values()->toArray()
                        : [];

                    $isCorrect = $correctOptionIds === $selectedIds;
                    $earnedScore = $isCorrect ? $question->points : 0;
                    $answer->update(['is_correct' => $isCorrect, 'points_earned' => $earnedScore]);
                    break;

                case QuestionTypeEnum::TRUE_FALSE:
                    $correctOptionId = $question->options()
                        ->where('is_correct', true)
                        ->value('id');

                    $selectedOptionId = $answer->selected_option_id;
                    $isCorrect = $correctOptionId !== null && $selectedOptionId == $correctOptionId;

                    $earnedScore = $isCorrect ? $question->points : 0;
                    $answer->update(['is_correct' => $isCorrect, 'points_earned' => $earnedScore]);
                    break;

                case QuestionTypeEnum::SHORT_ANSWER:
                    $isCorrect = null;
                    $earnedScore = 0;
                    $answer->update(['is_correct' => $isCorrect, 'points_earned' => $earnedScore]);
                    break;

                default:
                    $answer->update(['is_correct' => null, 'points_earned' => 0]);
                    break;
            }

            $totalScore += $earnedScore;
        }

        $totalPoints = $exam->questions()->sum('questions.points');

        $passingScore = $exam->passing_score ?? ($totalPoints * 0.5);

        $attempt->update([
            'score' => $totalScore,
            'percentage' => $totalPoints > 0 ? round(($totalScore / $totalPoints) * 100, 2) : 0,
            'status' => ExamAttemptStatusEnum::GRADED,
        ]);

        return $attempt->fresh();
    }

    public function calculatePercentage(float $score, float $totalPoints): float
    {
        if ($totalPoints <= 0) {
            return 0;
        }

        return round(($score / $totalPoints) * 100, 2);
    }

    public function getExamStatistics(Exam $exam): array
    {
        $attempts = ExamAttempt::where('exam_id', $exam->id)
            ->graded()
            ->get();

        $scores = $attempts->pluck('score')->filter()->values();

        if ($scores->isEmpty()) {
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'median_score' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'std_deviation' => 0,
                'pass_count' => 0,
                'fail_count' => 0,
                'pass_rate' => 0,
            ];
        }

        $sortedScores = $scores->sort()->values();
        $count = $sortedScores->count();
        $mid = (int) floor($count / 2);
        $median = $count % 2 === 0
            ? ($sortedScores[$mid - 1] + $sortedScores[$mid]) / 2
            : $sortedScores[$mid];

        $mean = $scores->avg();
        $variance = $scores->reduce(fn ($carry, $item) => $carry + pow($item - $mean, 2), 0) / $count;
        $stdDeviation = round(sqrt($variance), 2);

        return [
            'total_attempts' => $count,
            'average_score' => round($mean, 2),
            'median_score' => round($median, 2),
            'highest_score' => $scores->max(),
            'lowest_score' => $scores->min(),
            'std_deviation' => $stdDeviation,
            'pass_count' => $attempts->where('percentage', '>=', 50)->count(),
            'fail_count' => $attempts->where('percentage', '<', 50)->count(),
            'pass_rate' => round($attempts->where('percentage', '>=', 50)->count() / $count * 100, 2),
        ];
    }
}
