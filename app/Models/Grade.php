<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'class_id',
        'academic_year_id',
        'semester_id',
        'tugas_score',
        'quiz_score',
        'uts_score',
        'uas_score',
        'practical_score',
        'project_score',
        'final_score',
        'final_percentage',
        'letter_grade',
        'is_remedial',
    ];

    protected function casts(): array
    {
        return [
            'tugas_score' => 'decimal:2',
            'quiz_score' => 'decimal:2',
            'uts_score' => 'decimal:2',
            'uas_score' => 'decimal:2',
            'practical_score' => 'decimal:2',
            'project_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'final_percentage' => 'decimal:2',
            'is_remedial' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeBySubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeRemedial($query)
    {
        return $query->where('is_remedial', true);
    }

    public function scopeNonRemedial($query)
    {
        return $query->where('is_remedial', false);
    }
}
