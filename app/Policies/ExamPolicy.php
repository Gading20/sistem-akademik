<?php

namespace App\Policies;

use App\Enums\ExamStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Exam;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExamPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value)
            || $user->hasRole(RoleEnum::KEPALA_SEKOLAH->value)
            || $user->hasRole(RoleEnum::WAKIL_KEPALA_SEKOLAH->value)
            || $user->hasRole(RoleEnum::GURU->value)
            || $user->hasRole(RoleEnum::WALI_KELAS->value)
            || $user->hasRole(RoleEnum::SISWA->value);
    }

    public function view(User $user, Exam $exam): bool
    {
        if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value)
            || $user->hasRole(RoleEnum::KEPALA_SEKOLAH->value)
            || $user->hasRole(RoleEnum::WAKIL_KEPALA_SEKOLAH->value)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value) || $user->hasRole(RoleEnum::WALI_KELAS->value)) {
            return $exam->teacher_id === $user->teacher?->id;
        }

        if ($user->hasRole(RoleEnum::SISWA->value)) {
            return $this->isAssignedToStudent($exam, $user->student);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value)
            || $user->hasRole(RoleEnum::GURU->value)
            || $user->hasRole(RoleEnum::WALI_KELAS->value);
    }

    public function update(User $user, Exam $exam): bool
    {
        if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value) || $user->hasRole(RoleEnum::WALI_KELAS->value)) {
            return $exam->teacher_id === $user->teacher?->id;
        }

        return false;
    }

    public function delete(User $user, Exam $exam): bool
    {
        if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value) || $user->hasRole(RoleEnum::WALI_KELAS->value)) {
            return $exam->teacher_id === $user->teacher?->id;
        }

        return false;
    }

    public function take(User $user, Exam $exam): bool
    {
        if (!$user->hasRole(RoleEnum::SISWA->value)) {
            return false;
        }

        if (!in_array($exam->status->value, [ExamStatusEnum::PUBLISHED->value, ExamStatusEnum::ACTIVE->value], true)) {
            return false;
        }

        return $this->isAssignedToStudent($exam, $user->student);
    }

    /**
     * Siswa dianggap menjadi target ujian bila kelasnya terdaftar
     * pada ujian, baik lewat kolom students.class_id maupun
     * baris aktif di class_members.
     */
    protected function isAssignedToStudent(Exam $exam, ?Student $student): bool
    {
        if (!$student) {
            return false;
        }

        $classIds = $student->activeClassIds();

        if (empty($classIds)) {
            return false;
        }

        return $exam->examClasses()
            ->whereIn('class_id', $classIds)
            ->exists();
    }
}
