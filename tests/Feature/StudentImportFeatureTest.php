<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentImportFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ClassRoom $classRoom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(PreventRequestForgery::class);

        $this->seed(RolesSeeder::class);

        $role = Role::where('name', RoleEnum::SUPER_ADMIN->value)->firstOrFail();

        $this->admin = User::create([
            'name' => 'Admin Sekolah',
            'email' => 'admin@test.sch.id',
            'password' => Hash::make('password'),
            'role_id' => $role->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $major = Major::create(['name' => 'Rekayasa Perangkat Lunak', 'code' => 'RPL']);

        $academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'ganjil',
            'start_date' => '2025-07-15',
            'end_date' => '2025-12-20',
            'is_active' => true,
        ]);

        $this->classRoom = ClassRoom::create([
            'name' => 'X RPL 1',
            'major_id' => $major->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'capacity' => 36,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_import_students_from_csv(): void
    {
        $csv = $this->csvContent([
            ['nisn', 'nis', 'name', 'email', 'gender', 'class_name'],
            ['0012345678', '2024001', 'Ahmad Rizky', 'ahmad@test.sch.id', 'male', 'X RPL 1'],
            ['0012345679', '2024002', 'Siti Nurhaliza', '', 'female', 'X RPL 1'],
            ['0012345680', '2024003', 'Budi Santoso', '', 'male', 'X RPL 1'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('master.students.import.process'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
            ])
            ->assertRedirect(route('master.students.index'))
            ->assertSessionHas('import_results');

        $this->assertSame(3, Student::count());

        $siswaRoleId = Role::where('name', RoleEnum::SISWA->value)->firstOrFail()->id;
        $this->assertSame(3, User::where('role_id', $siswaRoleId)->count());

        $this->assertDatabaseHas('students', ['nisn' => '0012345678', 'class_id' => $this->classRoom->id]);
        $this->assertDatabaseHas('students', ['nisn' => '0012345680']);

        // Email kosong -> otomatis dibuat dari NISN (unik, agar dua baris tanpa email tidak bentrok).
        $this->assertDatabaseHas('users', ['email' => '0012345679@student.sch.id']);
        $this->assertDatabaseHas('users', ['email' => '0012345680@student.sch.id']);

        $sessionResults = session('import_results');
        $this->assertSame(3, $sessionResults['success']);
        $this->assertSame(0, $sessionResults['failed']);
    }

    public function test_import_uses_low_bcrypt_cost_for_default_password(): void
    {
        $csv = $this->csvContent([
            ['nisn', 'nis', 'name', 'gender', 'class_name'],
            ['0012345678', '2024001', 'Ahmad Rizky', 'male', 'X RPL 1'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('master.students.import.process'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
            ])
            ->assertRedirect(route('master.students.index'));

        $imported = Student::firstOrFail()->user;

        // Cost 8 (IMPORT_PASSWORD_ROUNDS) agar import massal tidak melampaui batas waktu.
        $this->assertStringStartsWith('$2y$08$', $imported->password);
        $this->assertTrue(Hash::check('siswa123', $imported->password));
    }

    public function test_import_reports_invalid_rows_and_keeps_valid_ones(): void
    {
        $csv = $this->csvContent([
            ['nisn', 'nis', 'name', 'gender', 'class_name'],
            ['0012345678', '2024001', 'Ahmad Rizky', 'male', 'X RPL 1'],
            ['0012345679', '2024002', 'Siti Nurhaliza', 'invalid-gender', 'X RPL 1'],
            ['', '2024003', 'Budi Santoso', 'male', 'X RPL 1'],
            ['0012345680', '2024004', 'Dewi Lestari', 'female', 'X RPL 1'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('master.students.import.process'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
            ])
            ->assertRedirect(route('master.students.index'));

        $this->assertSame(2, Student::count());

        $sessionResults = session('import_results');
        $this->assertSame(2, $sessionResults['success']);
        $this->assertSame(2, $sessionResults['failed']);
        $this->assertCount(2, $sessionResults['errors']);
    }

    public function test_import_reports_missing_class_clearly(): void
    {
        $csv = $this->csvContent([
            ['nisn', 'nis', 'name', 'gender', 'class_name'],
            ['0012345678', '2024001', 'Ahmad Rizky', 'male', 'X AKL 1'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('master.students.import.process'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
            ])
            ->assertRedirect(route('master.students.index'));

        $this->assertSame(0, Student::count());

        $sessionResults = session('import_results');
        $this->assertSame(1, $sessionResults['failed']);
        $this->assertStringContainsString("Kelas 'X AKL 1' tidak ditemukan", $sessionResults['errors'][0]['error']);
    }

    public function test_import_records_single_summary_audit_log(): void
    {
        $csv = $this->csvContent([
            ['nisn', 'nis', 'name', 'gender', 'class_name'],
            ['0012345678', '2024001', 'Ahmad Rizky', 'male', 'X RPL 1'],
            ['0012345679', '2024002', 'Siti Nurhaliza', 'female', 'X RPL 1'],
        ]);

        $this->actingAs($this->admin)
            ->post(route('master.students.import.process'), [
                'file' => UploadedFile::fake()->createWithContent('siswa.csv', $csv),
            ])
            ->assertRedirect(route('master.students.index'));

        // Satu entri audit ringkasan oleh admin (bukan per-baris seperti sebelumnya).
        $this->assertSame(1, AuditLog::where('action', 'imported')
            ->where('module', Student::class)
            ->where('user_id', $this->admin->id)
            ->count());
    }

    protected function csvContent(array $rows): string
    {
        $stream = fopen('php://temp', 'w');

        foreach ($rows as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);

        return stream_get_contents($stream);
    }
}
