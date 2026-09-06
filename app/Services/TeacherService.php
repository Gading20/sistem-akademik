<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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
                'email' => $data['email'] ?? $data['nip'].'@teacher.sch.id',
                'password' => Hash::make($data['password'] ?? Str::random(8)),
                'role_id' => $this->getRoleId(RoleEnum::GURU),
                'is_active' => true,
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => $data['nip'],
                'nuptk' => $data['nuptk'] ?? null,
                'gender' => $data['gender'] ?? null,
                'nik' => $data['nik'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'place_of_birth' => $data['place_of_birth'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'employment_status' => $data['employment_status'] ?? 'active',
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

            $userData = array_filter([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
            ], fn ($value) => $value !== null);

            if (! empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            if (! empty($userData)) {
                $teacher->user()->update($userData);
            }

            unset($data['name'], $data['email'], $data['password'], $data['user_data']);

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

    public function getAll(): Collection
    {
        return Teacher::with(['user', 'subject'])->active()->get();
    }

    public function getById(int $id): ?Teacher
    {
        return Teacher::with(['user', 'subject', 'teachingAssignments'])->find($id);
    }

    private function getRoleId(RoleEnum $role): int
    {
        return Role::where('name', $role->value)->first()->id;
    }

    public function import(array $teachers): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($teachers as $index => $data) {
            try {
                $validation = $this->validateImportRow($data, $index + 2);
                if (! empty($validation)) {
                    $errors[] = $validation;
                    $failed++;

                    continue;
                }

                // Cek duplikat NIP
                if (! empty($data['nip']) && Teacher::where('nip', $data['nip'])->exists()) {
                    $errors[] = 'Baris '.($index + 2).": NIP {$data['nip']} sudah terdaftar";
                    $failed++;

                    continue;
                }

                // Cek duplikat email
                $email = $data['email'] ?? ($data['nip'].'@teacher.sch.id');
                if (User::where('email', $email)->exists()) {
                    $errors[] = 'Baris '.($index + 2).": Email {$email} sudah terdaftar";
                    $failed++;

                    continue;
                }

                // Cari subject berdasarkan nama
                $subjectId = null;
                if (! empty($data['subject_name'])) {
                    $subject = Subject::where('name', 'like', '%'.$data['subject_name'].'%')->first();
                    if ($subject) {
                        $subjectId = $subject->id;
                    }
                }

                $this->create([
                    'name' => $data['name'],
                    'email' => $email,
                    'nip' => $data['nip'] ?? null,
                    'nuptk' => $data['nuptk'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'subject_id' => $subjectId,
                    'place_of_birth' => $data['place_of_birth'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'address' => $data['address'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'join_date' => $data['join_date'] ?? now()->toDateString(),
                    'password' => 'guru123',
                ]);

                $success++;
            } catch (\Exception $e) {
                $errors[] = 'Baris '.($index + 2).': '.$e->getMessage();
                $failed++;
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    private function validateImportRow(array $data, int $row): ?string
    {
        if (empty($data['name'])) {
            return "Baris {$row}: Nama wajib diisi";
        }

        if (! empty($data['gender']) && ! in_array($data['gender'], ['male', 'female'])) {
            return "Baris {$row}: Gender harus male atau female";
        }

        if (! empty($data['date_of_birth']) && ! strtotime($data['date_of_birth'])) {
            return "Baris {$row}: Format tanggal lahir tidak valid (gunakan YYYY-MM-DD)";
        }

        if (! empty($data['join_date']) && ! strtotime($data['join_date'])) {
            return "Baris {$row}: Format tanggal masuk tidak valid (gunakan YYYY-MM-DD)";
        }

        return null;
    }
}
