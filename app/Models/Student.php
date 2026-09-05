<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'class_id',
        'parent_id',
        'birth_place',
        'birth_date',
        'gender',
        'religion',
        'address',
        'phone',
        'admission_date',
        'status',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'admission_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Semua id kelas tempat siswa terdaftar aktif:
     * gabungan kolom class_id (kelas saat ini) dan baris aktif
     * pada tabel class_members.
     */
    public function activeClassIds(): array
    {
        return collect([$this->class_id])
            ->merge(ClassMember::where('student_id', $this->id)
                ->where('is_active', true)
                ->pluck('class_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

    public function classMembers(): HasMany
    {
        return $this->hasMany(ClassMember::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function assignmentSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }
}
