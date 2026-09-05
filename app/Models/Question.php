<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_bank_id',
        'type',
        'question',
        'explanation',
        'difficulty',
        'points',
        'topic',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function correctOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class)->where('is_correct', true);
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function examAnswers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }
}
