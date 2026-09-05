<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCard extends Model
{
    use HasFactory;

    protected $table = 'report_cards';

    protected $fillable = [
        'student_id',
        'class_id',
        'academic_year_id',
        'semester_id',
        'total_score',
        'average_score',
        'rank',
        'attendance_summary',
        'class_teacher_notes',
        'principal_notes',
        'is_finalized',
        'finalized_at',
        'finalized_by',
    ];

    protected function casts(): array
    {
        return [
            'total_score' => 'decimal:2',
            'average_score' => 'decimal:2',
            'rank' => 'integer',
            'is_finalized' => 'boolean',
            'finalized_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
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

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeFinalized($query)
    {
        return $query->where('is_finalized', true);
    }

    public function scopeUnfinalized($query)
    {
        return $query->where('is_finalized', false);
    }
}
