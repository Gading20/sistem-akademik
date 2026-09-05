<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherService
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function create(array $data): Teacher
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? $data['nip'] . '@teacher.sch.id',
                'password' => Hash::make($data['password'] ?? Str::random(8)),
                'role_id' => $this->getRoleId(RoleEnum::GURU),
                'is_active' => true,
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => $data['nip'],
                'nuptk' => $data['nuptk'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'join_date' => $data['join_date'] ?? now()->toDateString(),
                'contract_end_date' => $data['contract_end_date'] ?? null,
                'is_active' => true,
            ]);

            $this->auditLogService->log($user, 'created', Teacher::class, $teacher->id, null, $teacher->toArray());

            return $teacher->load('user');
        });
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        return DB::transaction(function () use ($teacher, $data) {
            $oldData = $teacher->toArray();

            if (isset($data['user_data'])) {
                $teacher->user()->update($data['user_data']);
                unset($data['user_data']);
            }

            $teacher->update($data);

            $this->auditLogService->log(
                $teacher->user,
                'updated',
                Teacher::class,
                $teacher->id,
                $oldData,
                $teacher->fresh()->toArray()
            );

            return $teacher->fresh()->load('user');
        });
    }

    public function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return Teacher::with(['user', 'subject'])->active()->get();
    }

    public function getById(int $id): ?Teacher
    {
        return Teacher::with(['user', 'subject', 'teachingAssignments'])->find($id);
    }

    private function getRoleId(RoleEnum $role): int
    {
        return \App\Models\Role::where('name', $role->value)->first()->id;
    }
}
