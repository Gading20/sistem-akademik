<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\ReportCard;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportCardPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
            RoleEnum::WAKIL_KEPALA_SEKOLAH->value,
            RoleEnum::GURU->value,
            RoleEnum::WALI_KELAS->value,
        );
    }

    public function view(User $user, ReportCard $reportCard): bool
    {
        if ($user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
            RoleEnum::WAKIL_KEPALA_SEKOLAH->value,
        )) {
            return true;
        }

        if ($user->hasRole(RoleEnum::WALI_KELAS->value)) {
            return $user->teacher?->teachingAssignments()
                ->where('class_id', $reportCard->class_id)
                ->exists()
                || $reportCard->classRoom?->wali_kelas_id === $user->teacher?->id;
        }

        if ($user->hasRole(RoleEnum::GURU->value)) {
            return $user->teacher?->teachingAssignments()
                ->where('class_id', $reportCard->class_id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::WALI_KELAS->value,
        );
    }

    public function update(User $user, ReportCard $reportCard): bool
    {
        if ($user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        )) {
            return true;
        }

        if ($user->hasRole(RoleEnum::WALI_KELAS->value)) {
            return $reportCard->classRoom?->wali_kelas_id === $user->teacher?->id;
        }

        return false;
    }

    public function finalize(User $user, ReportCard $reportCard): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
            RoleEnum::KEPALA_SEKOLAH->value,
        );
    }

    public function delete(User $user, ReportCard $reportCard): bool
    {
        return $user->hasRole(
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN_SEKOLAH->value,
        );
    }
}
