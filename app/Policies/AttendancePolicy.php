<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::GURU->value,
            RoleEnum::WALI_KELAS->value,
        );
    }

    /**
     * Siswa berhak melihat riwayat absensinya sendiri.
     */
    public function viewOwn(User $user): bool
    {
        return $user->hasRole(RoleEnum::SISWA->value);
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        )) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value, RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->teachingAssignments()
                ->where('class_id', $attendance->schedule->teachingAssignment->class_id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::GURU->value,
            RoleEnum::WALI_KELAS->value,
        );
    }

    public function update(User $user, Attendance $attendance): bool
    {
        if ($user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        )) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value, RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->teachingAssignments()
                ->where('class_id', $attendance->schedule->teachingAssignment->class_id)
                ->exists();
        }

        return false;
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }
}
