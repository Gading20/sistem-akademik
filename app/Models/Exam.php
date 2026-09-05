<?php

namespace App\Models;

use App\Enums\ExamStatusEnum;
use App\Enums\ExamTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'subject_id',
        'teacher_id',
        'type',
        'academic_year_id',
        'semester_id',
        'start_at',
        'end_at',
        'duration_minutes',
        'attempt_limit',
        'random_question',
        'random_option',
        'shuffle_options',
        'show_result',
        'show_answer_after_submit',
        'passing_score',
        'token',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => ExamTypeEnum::class,
            'status' => ExamStatusEnum::class,
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'duration_minutes' => 'integer',
            'attempt_limit' => 'integer',
            'random_question' => 'boolean',
            'random_option' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_result' => 'boolean',
            'show_answer_after_submit' => 'boolean',
            'passing_score' => 'decimal:2',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function examQuestions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Question::class,
            ExamQuestion::class,
            'exam_id',   // FK di exam_questions
            'id',        // FK di questions
            'id',        // local key di exams
            'question_id' // local key di exam_questions
        );
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function examClasses(): HasMany
    {
        return $this->hasMany(ExamClass::class);
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'exam_classes', 'exam_id', 'class_id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBySubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
