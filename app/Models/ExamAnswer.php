<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    use HasFactory;

    protected $table = 'exam_answers';

    protected $fillable = [
        'exam_attempt_id',
        'question_id',
        'answer',
        'selected_option_id',
        'essay_answer',
        'file_path',
        'points_earned',
        'is_correct',
        'is_graded',
        'graded_by',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'points_earned' => 'decimal:2',
            'is_correct' => 'boolean',
            'is_graded' => 'boolean',
            'graded_at' => 'datetime',
        ];
    }

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function scopeByAttempt($query, int $attemptId)
    {
        return $query->where('exam_attempt_id', $attemptId);
    }

    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeGraded($query)
    {
        return $query->where('is_graded', true);
    }
}
