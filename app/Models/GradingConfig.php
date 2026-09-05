<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingConfig extends Model
{
    use HasFactory;

    protected $table = 'grading_configs';

    protected $fillable = [
        'subject_id',
        'class_id',
        'academic_year_id',
        'semester_id',
        'method',
        'tugas_weight',
        'quiz_weight',
        'uts_weight',
        'uas_weight',
        'practical_weight',
        'project_weight',
    ];

    protected function casts(): array
    {
        return [
            'tugas_weight' => 'decimal:2',
            'quiz_weight' => 'decimal:2',
            'uts_weight' => 'decimal:2',
            'uas_weight' => 'decimal:2',
            'practical_weight' => 'decimal:2',
            'project_weight' => 'decimal:2',
        ];
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

    public function scopeBySubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }
}
