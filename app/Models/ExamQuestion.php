<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $table = 'exam_questions';

    protected $fillable = [
        'exam_id',
        'question_id',
        'order',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'points' => 'decimal:2',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
