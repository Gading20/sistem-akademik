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

class ExamAvailableStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentUser;

    protected Student $student;

    protected Teacher $teacher;

    protected Subject $subject;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);

        $teacherUser = $this->makeUser(RoleEnum::GURU, 'guru@test.sch.id');

        $this->academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-15',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->semester = Semester::create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'ganjil',
            'start_date' => '2025-07-15',
            'end_date' => '2025-12-20',
            'is_active' => true,
        ]);

        $major = Major::create(['name' => 'Rekayasa Perangkat Lunak', 'code' => 'RPL']);

        $classRoom = ClassRoom::create([
            'name' => 'X RPL 1',
            'major_id' => $major->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'capacity' => 36,
            'is_active' => true,
        ]);

        $this->subject = Subject::create([
            'name' => 'Pemrograman Web',
            'code' => 'PW',
            'is_active' => true,
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'join_date' => '2020-07-01',
            'is_active' => true,
        ]);

        $this->studentUser = $this->makeUser(RoleEnum::SISWA, 'siswa@test.sch.id');

        $this->student = Student::create([
            'user_id' => $this->studentUser->id,
            'nis' => '2025001',
            'nisn' => '0012345678',
            'class_id' => $classRoom->id,
            'gender' => 'male',
            'religion' => 'islam',
            'admission_date' => '2025-07-15',
            'status' => StudentStatusEnum::ACTIVE,
        ]);
    }

    public function test_exam_not_started_yet_is_shown_as_unavailable(): void
    {
        $exam = $this->makeExam(now()->addHour(), now()->addHours(2));

        $this->actingAs($this->studentUser)
            ->get(route('exam.exams.available'))
            ->assertOk()
            ->assertSee('bg-gray-100 text-gray-500')
            ->assertSee($exam->title)
            ->assertDontSee('bg-indigo-100')
            ->assertDontSee(route('exam.exams.start', $exam));
    }

    public function test_ended_exam_without_attempt_is_shown_as_unavailable(): void
    {
        $exam = $this->makeExam(now()->subDays(2), now()->subDay());

        $this->actingAs($this->studentUser)
            ->get(route('exam.exams.available'))
            ->assertOk()
            ->assertSee('bg-gray-100 text-gray-500')
            ->assertSee($exam->title)
            ->assertDontSee(route('exam.exams.start', $exam));
    }

    public function test_open_exam_without_attempt_is_shown_as_available(): void
    {
        $exam = $this->makeExam(now()->subHour(), now()->addHour());

        $this->actingAs($this->studentUser)
            ->get(route('exam.exams.available'))
            ->assertOk()
            ->assertSee('bg-indigo-100 text-indigo-700')
            ->assertSee(route('exam.exams.start', $exam))
            ->assertDontSee('bg-gray-100 text-gray-500');
    }

    public function test_exam_with_in_progress_attempt_is_shown_as_berlangsung(): void
    {
        $exam = $this->makeExam(now()->subHour(), now()->addHour());

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(10),
            'status' => 'in_progress',
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('exam.exams.available'))
            ->assertOk()
            ->assertSee('bg-green-100 text-green-700')
            ->assertSee(route('exam.exams.attempt', $attempt));
    }

    public function test_exam_with_completed_attempt_is_shown_as_selesai(): void
    {
        $exam = $this->makeExam(now()->subHour(), now()->addHour());

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $this->student->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(30),
            'submitted_at' => now()->subMinutes(5),
            'status' => 'submitted',
            'score' => 80,
            'percentage' => 80,
        ]);

        $this->actingAs($this->studentUser)
            ->get(route('exam.exams.available'))
            ->assertOk()
            ->assertSee('bg-blue-100 text-blue-700')
            ->assertSee(route('exam.exams.result', $attempt))
            ->assertDontSee(route('exam.exams.start', $exam));
    }

    public function test_status_filter_filters_the_exam_list(): void
    {
        $available = $this->makeExam(now()->subHour(), now()->addHour());
        $missed = $this->makeExam(now()->subDays(2), now()->subDay());

        $this->actingAs($this->studentUser)
            ->get(route('exam.exams.available', ['status' => 'available']))
            ->assertOk()
            ->assertSee($available->title)
            ->assertDontSee($missed->title);

        $this->actingAs($this->studentUser)
            ->get(route('exam.exams.available', ['status' => 'unavailable']))
            ->assertOk()
            ->assertSee($missed->title)
            ->assertDontSee($available->title);
    }

    protected function makeExam($startAt, $endAt): Exam
    {
        return Exam::create([
            'title' => 'Ujian Status '.fake()->unique()->numberBetween(1, 9999),
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'type' => ExamTypeEnum::QUIZ,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration_minutes' => 30,
            'attempt_limit' => 1,
            'passing_score' => 60,
            'status' => ExamStatusEnum::PUBLISHED,
        ]);
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
