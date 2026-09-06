<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Competency;
use App\Models\Major;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->seed();
    }

    private function makeAdmin(): User
    {
        $role = Role::where('name', 'admin_sekolah')->first();

        return User::factory()->create(['role_id' => $role->id]);
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_academic_years_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('master.academic-years.index'));

        $response->assertStatus(200);
        $response->assertViewIs('master.academic-years.index');
    }

    public function test_admin_can_create_academic_year(): void
    {
        $admin = $this->makeAdmin();

        $data = [
            'name' => '2026/2027',
            'start_year' => 2026,
            'end_year' => 2027,
            'is_active' => false,
        ];

        $response = $this->actingAs($admin)->post(route('master.academic-years.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('academic_years', ['name' => '2026/2027']);
    }

    public function test_admin_can_update_academic_year(): void
    {
        $admin = $this->makeAdmin();
        $academicYear = AcademicYear::create([
            'name' => 'Old Name',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => false,
        ]);

        $data = [
            'name' => 'Updated Name',
            'start_year' => 2025,
            'end_year' => 2026,
            'is_active' => $academicYear->is_active,
        ];

        $response = $this->actingAs($admin)->put(route('master.academic-years.update', $academicYear), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('academic_years', ['name' => 'Updated Name']);
    }

    public function test_admin_can_delete_academic_year(): void
    {
        $admin = $this->makeAdmin();
        $academicYear = AcademicYear::create([
            'name' => 'To Delete',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->delete(route('master.academic-years.destroy', $academicYear));

        $response->assertRedirect();
        $this->assertDatabaseMissing('academic_years', ['id' => $academicYear->id]);
    }

    public function test_admin_can_view_semesters_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('master.semesters.index'));

        $response->assertStatus(200);
        $response->assertViewIs('master.semesters.index');
    }

    public function test_admin_can_create_semester(): void
    {
        $admin = $this->makeAdmin();
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);

        $data = [
            'academic_year_id' => $academicYear->id,
            'name' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => false,
        ];

        $response = $this->actingAs($admin)->post(route('master.semesters.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('semesters', ['academic_year_id' => $academicYear->id, 'name' => 'ganjil']);
    }

    public function test_admin_can_view_majors_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('master.majors.index'));

        $response->assertStatus(200);
        $response->assertViewIs('master.majors.index');
    }

    public function test_admin_can_create_major(): void
    {
        $admin = $this->makeAdmin();

        $data = [
            'name' => 'Teknik Informatika',
            'code' => 'TI',
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post(route('master.majors.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('majors', ['name' => 'Teknik Informatika', 'code' => 'TI']);
    }

    public function test_admin_can_view_competencies_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('master.competencies.index'));

        $response->assertStatus(200);
        $response->assertViewIs('master.competencies.index');
    }

    public function test_admin_can_create_competency(): void
    {
        $admin = $this->makeAdmin();
        $major = Major::create([
            'name' => 'Test Major',
            'code' => 'TM',
            'is_active' => true,
        ]);

        $data = [
            'major_id' => $major->id,
            'name' => 'Rekayasa Perangkat Lunak',
            'code' => 'RPL',
        ];

        $response = $this->actingAs($admin)->post(route('master.competencies.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('competencies', ['name' => 'Rekayasa Perangkat Lunak']);
    }

    public function test_admin_can_view_rooms_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('master.rooms.index'));

        $response->assertStatus(200);
        $response->assertViewIs('master.rooms.index');
    }

    public function test_admin_can_create_room(): void
    {
        $admin = $this->makeAdmin();

        $data = [
            'name' => 'Lab Komputer 1',
            'code' => 'LAB-1',
            'capacity' => 40,
            'type' => 'laboratorium',
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post(route('master.rooms.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('rooms', ['name' => 'Lab Komputer 1', 'code' => 'LAB-1']);
    }

    public function test_admin_can_view_subjects_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('master.subjects.index'));

        $response->assertStatus(200);
        $response->assertViewIs('master.subjects.index');
    }

    public function test_admin_can_create_subject(): void
    {
        $admin = $this->makeAdmin();
        $major = Major::create([
            'name' => 'Test Major',
            'code' => 'TM',
            'is_active' => true,
        ]);

        $data = [
            'major_id' => $major->id,
            'name' => 'Pemrograman Web',
            'code' => 'PWE',
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)->post(route('master.subjects.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('subjects', ['name' => 'Pemrograman Web', 'code' => 'PWE']);
    }

    public function test_admin_can_view_classes_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('master.classes.index'));

        $response->assertStatus(200);
        $response->assertViewIs('master.classes.index');
    }

    public function test_admin_can_create_class(): void
    {
        $admin = $this->makeAdmin();
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);
        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => false,
        ]);
        $major = Major::create([
            'name' => 'Test Major',
            'code' => 'TM',
            'is_active' => true,
        ]);
        $competency = Competency::create([
            'major_id' => $major->id,
            'name' => 'Test Competency',
            'code' => 'TC',
        ]);

        $data = [
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'major_id' => $major->id,
            'competency_id' => $competency->id,
            'name' => 'XII RPL 1',
        ];

        $response = $this->actingAs($admin)->post(route('master.classes.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', ['name' => 'XII RPL 1']);
    }

    public function test_admin_can_view_students_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('master.students.index'));

        $response->assertStatus(200);
        $response->assertViewIs('master.students.index');
    }

    public function test_admin_can_create_student(): void
    {
        $admin = $this->makeAdmin();
        $role = Role::where('name', 'siswa')->first();
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);
        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'name' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => false,
        ]);
        $major = Major::create([
            'name' => 'Test Major',
            'code' => 'TM',
            'is_active' => true,
        ]);
        $competency = Competency::create([
            'major_id' => $major->id,
            'name' => 'Test Competency',
            'code' => 'TC',
        ]);
        $classRoom = ClassRoom::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'major_id' => $major->id,
            'competency_id' => $competency->id,
            'name' => 'X TKJ 1',
            'level' => 10,
        ]);

        $data = [
            'nisn' => '1234567890',
            'nis' => '12345',
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'gender' => 'male',
            'birth_date' => '2008-01-01',
            'birth_place' => 'Jakarta',
            'address' => 'Jl. Test No. 1',
            'phone' => '081234567890',
            'class_id' => $classRoom->id,
            'admission_date' => '2024-07-15',
        ];

        $response = $this->actingAs($admin)->post(route('master.students.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('students', ['nisn' => '1234567890', 'nis' => '12345']);
        $this->assertDatabaseHas('users', ['email' => 'student@test.com']);
    }

    public function test_admin_can_view_teachers_list(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('master.teachers.index'));

        $response->assertStatus(200);
        $response->assertViewIs('master.teachers.index');
    }

    public function test_admin_can_create_teacher(): void
    {
        $admin = $this->makeAdmin();
        $role = Role::where('name', 'guru')->first();
        $major = Major::create([
            'name' => 'Test Major',
            'code' => 'TM',
            'is_active' => true,
        ]);
        $subject = Subject::create([
            'major_id' => $major->id,
            'name' => 'Test Subject',
            'code' => 'TS',
            'is_active' => true,
        ]);

        $data = [
            'nip' => '987654321',
            'name' => 'Test Teacher',
            'email' => 'teacher@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'subject_id' => $subject->id,
            'gender' => 'male',
            'date_of_birth' => '1985-01-01',
            'place_of_birth' => 'Jakarta',
            'address' => 'Jl. Guru No. 1',
            'phone' => '081234567891',
            'join_date' => '2020-07-01',
        ];

        $response = $this->actingAs($admin)->post(route('master.teachers.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('teachers', ['nip' => '987654321']);
        $this->assertDatabaseHas('users', ['email' => 'teacher@test.com']);
    }

    public function test_non_admin_cannot_access_master_data(): void
    {
        $role = Role::where('name', 'siswa')->first();
        $student = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($student)->get(route('master.academic-years.index'));

        $response->assertStatus(403);
    }
}
