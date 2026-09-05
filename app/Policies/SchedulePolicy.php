<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::GURU->value,
            RoleEnum::WALI_KELAS->value,
            RoleEnum::SISWA->value,
        );
    }

    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        )) {
            return true;
        }

        if ($user->hasRole(RoleEnum::GURU->value, RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->teachingAssignments()
                ->where('class_id', $schedule->teachingAssignment->class_id)
                ->exists();
        }

        if ($user->hasRole(RoleEnum::SISWA->value)) {
            return $user->student?->classMembers()
                ->where('class_id', $schedule->teachingAssignment->class_id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }
}
