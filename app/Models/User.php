<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'avatar',
        'phone',
        'address',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function getRoleEnumAttribute(): RoleEnum
    {
        return RoleEnum::from($this->role->name);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function parentProfile(): HasOne
    {
        return $this->hasOne(ParentProfile::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function attendanceRecordings(): HasMany
    {
        return $this->hasMany(Attendance::class, 'recorded_by');
    }

    public function gradedSubmissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'graded_by');
    }

    public function gradedExamAnswers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'graded_by');
    }

    public function finalizedReportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class, 'finalized_by');
    }

    public function authoredAnnouncements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'author_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $roleName)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', $roleName));
    }

    public function hasRole(string|array ...$roles): bool
    {
        $roles = is_array($roles[0] ?? null) ? $roles[0] : $roles;
        return in_array($this->role?->name, $roles);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RoleEnum::SUPER_ADMIN->value, RoleEnum::ADMIN_SEKOLAH->value);
    }
}
