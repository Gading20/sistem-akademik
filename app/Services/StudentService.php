<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Enums\StudentStatusEnum;
use App\Models\ClassMember;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentService
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? $data['nisn'] . '@student.sch.id',
                'password' => Hash::make($data['password'] ?? Str::random(8)),
                'role_id' => $this->getRoleId(RoleEnum::SISWA),
                'is_active' => true,
            ]);

            $classId = $data['class_id'] ?? null;
            if (empty($classId) && !empty($data['class_name'])) {
                $class = ClassRoom::where('name', $data['class_name'])->first();
                $classId = $class?->id;
            }

            $student = Student::create([
                'user_id' => $user->id,
                'nisn' => $data['nisn'],
                'nis' => $data['nis'] ?? null,
                'class_id' => $classId,
                'parent_id' => $data['parent_id'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'birth_place' => $data['birth_place'] ?? null,
                'gender' => $data['gender'],
                'religion' => $data['religion'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => StudentStatusEnum::ACTIVE,
                'admission_date' => $data['admission_date'] ?? now()->toDateString(),
            ]);

            if ($classId) {
                $this->syncClassMembership($student, $classId);
            }

            $this->auditLogService->log($user, 'created', Student::class, $student->id, null, $student->toArray());

            return $student->load('user');
        });
    }

    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            $oldData = $student->toArray();

            if (isset($data['user_data'])) {
                $student->user()->update($data['user_data']);
                unset($data['user_data']);
            }

            $student->update($data);

            if (array_key_exists('class_id', $data)) {
                $this->syncClassMembership($student, $data['class_id']);
            }

            $this->auditLogService->log(
                $student->user,
                'updated',
                Student::class,
                $student->id,
                $oldData,
                $student->fresh()->toArray()
            );

            return $student->fresh()->load('user');
        });
    }

    /**
     * Sinkronkan kolom students.class_id dengan baris aktif di tabel
     * class_members (beserta tahun ajaran/semester kelas tsb), agar
     * pengecekan keanggotaan kelas konsisten di semua fitur.
     */
    protected function syncClassMembership(Student $student, ?int $classId): void
    {
        $classRoom = $classId ? ClassRoom::find($classId) : null;

        // Hapus/ Nonaktifkan keanggotaan sebelumnya di kelas lain pada periode yang sama.
        ClassMember::where('student_id', $student->id)
            ->when($classRoom?->academic_year_id, fn ($q) => $q->where('academic_year_id', $classRoom->academic_year_id))
            ->when($classRoom?->semester_id, fn ($q) => $q->where('semester_id', $classRoom->semester_id))
            ->where('class_id', '!=', $classRoom?->id)
            ->update(['is_active' => false]);

        if (!$classRoom) {
            return;
        }

        ClassMember::updateOrCreate(
            [
                'class_id' => $classRoom->id,
                'student_id' => $student->id,
                'academic_year_id' => $classRoom->academic_year_id,
                'semester_id' => $classRoom->semester_id,
            ],
            ['is_active' => true]
        );
    }

    public function import(array $students): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($students as $index => $studentData) {
            try {
                $this->validateImportRow($studentData, $index + 2);
                $this->create($studentData);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $index + 2,
                    'nisn' => $studentData['nisn'] ?? '-',
                    'name' => $studentData['name'] ?? '-',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    private function validateImportRow(array $data, int $row): void
    {
        $errors = [];

        if (empty($data['nisn'])) {
            $errors[] = 'NISN wajib diisi';
        }
        if (empty($data['nis'])) {
            $errors[] = 'NIS wajib diisi';
        }
        if (empty($data['name'])) {
            $errors[] = 'Nama wajib diisi';
        }
        if (empty($data['gender']) || !in_array($data['gender'], ['male', 'female'])) {
            $errors[] = 'Gender harus male atau female';
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }
        if (!empty($data['birth_date']) && !strtotime($data['birth_date'])) {
            $errors[] = 'Format tanggal lahir tidak valid (YYYY-MM-DD)';
        }

        if (!empty($errors)) {
            throw new \Exception('Baris ' . $row . ': ' . implode(', ', $errors));
        }
    }

    public function getStudentsByClass(int $classId): \Illuminate\Database\Eloquent\Collection
    {
        return Student::with(['user', 'classRoom'])
            ->active()
            ->byClass($classId)
            ->get();
    }

    private function getRoleId(RoleEnum $role): int
    {
        return \App\Models\Role::where('name', $role->value)->first()->id;
    }
}
