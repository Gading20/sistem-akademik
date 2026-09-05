<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GradePolicy
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
            || $user->hasRole(RoleEnum::SISWA->value)
            || $user->hasRole(RoleEnum::ORANG_TUA->value);
    }

    public function view(User $user, Grade $grade): bool
    {
        if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value)
            || $user->hasRole(RoleEnum::KEPALA_SEKOLAH->value)
            || $user->hasRole(RoleEnum::WAKIL_KEPALA_SEKOLAH->value)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value) || $user->hasRole(RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->teachingAssignments()
                ->where('subject_id', $grade->subject_id)
                ->where('class_id', $grade->class_id)
                ->exists();
        }

        if ($user->hasRole(RoleEnum::SISWA->value)) {
            return $user->student?->id === $grade->student_id;
        }

        if ($user->hasRole(RoleEnum::ORANG_TUA->value)) {
            return $user->parentProfile
                ?->students()
                ->where('students.id', $grade->student_id)
                ->exists();
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

    public function update(User $user, Grade $grade): bool
    {
        if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value)) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value) || $user->hasRole(RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->teachingAssignments()
                ->where('subject_id', $grade->subject_id)
                ->where('class_id', $grade->class_id)
                ->exists();
        }

        return false;
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN->value)
            || $user->hasRole(RoleEnum::ADMIN_SEKOLAH->value);
    }
}
