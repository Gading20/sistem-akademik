<?php

namespace Tests\Feature;

use App\Enums\ExamStatusEnum;
use App\Enums\ExamTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\StudentStatusEnum;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\Major;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StabilityRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacherUser;

    protected Teacher $teacher;

    protected Student $student;

    protected ClassRoom $classRoom;

    protected Subject $subject;

    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        $this->admin = $this->makeUser(RoleEnum::SUPER_ADMIN, 'admin@test.sch.id');

        $this->teacherUser = $this->makeUser(RoleEnum::GURU, 'guru@test.sch.id');

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

        $major = Major::create(['name' => 'Rekayasa Perangkat Lunak', 'code' => 'RPL']);

        $this->classRoom = ClassRoom::create([
            'name' => 'X RPL 1',
            'major_id' => $major->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'capacity' => 36,
            'is_active' => true,
        ]);

        $this->subject = Subject::create([
            'name' => 'Pemrograman Web',
            'code' => 'PW',
            'is_active' => true,
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'join_date' => '2020-07-01',
            'is_active' => true,
        ]);

        $studentUser = $this->makeUser(RoleEnum::SISWA, 'siswa@test.sch.id');

        $this->student = Student::create([
            'user_id' => $studentUser->id,
            'nis' => '2025001',
            'nisn' => '0012345678',
            'class_id' => $this->classRoom->id,
            'gender' => 'male',
            'religion' => 'islam',
            'admission_date' => '2025-07-15',
            'status' => StudentStatusEnum::ACTIVE,
        ]);

        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->classRoom->id,
            'academic_year_id' => $this->classRoom->academic_year_id,
            'semester_id' => $this->classRoom->semester_id,
            'final_score' => 87.5,
            'final_percentage' => 87.5,
            'letter_grade' => 'B',
        ]);

        $this->exam = Exam::create([
            'title' => 'Ujian Regresi',
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'type' => ExamTypeEnum::QUIZ,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 30,
            'attempt_limit' => 1,
            'passing_score' => 60,
            'status' => ExamStatusEnum::PUBLISHED,
        ]);

        ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(10),
            'submitted_at' => now(),
            'status' => 'submitted',
            'score' => 80,
            'percentage' => 80,
        ]);
    }

    public function test_student_edit_page_renders_for_admin(): void
    {
        $this->actingAs($this->admin)
            ->get(route('master.students.edit', $this->student))
            ->assertOk();
    }

    public function test_exam_results_page_renders_for_the_owning_teacher(): void
    {
        $this->actingAs($this->teacherUser)
            ->get(route('exam.exams.results', $this->exam))
            ->assertOk();
    }

    public function test_attendance_student_history_renders_without_filters(): void
    {
        $this->actingAs($this->admin)
            ->get(route('academic.attendance.student', $this->student))
            ->assertOk();
    }

    public function test_grades_report_page_renders_without_and_with_class_filter(): void
    {
        $this->actingAs($this->admin)
            ->get(route('reports.grades'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('reports.grades', ['class_id' => $this->classRoom->id]))
            ->assertOk()
            ->assertSee('87.5');
    }

    protected function makeUser(RoleEnum $role, string $email): User
    {
        $roleModel = Role::where('name', $role->value)->firstOrFail();

        return User::create([
            'name' => fake()->name(),
            'email' => $email,
            'password' => Hash::make('password'),
            'role_id' => $roleModel->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
