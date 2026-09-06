<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->seed();
    }

    private function makeTeacher(): User
    {
        $role = Role::where('name', 'guru')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $major = Major::firstOrCreate(['code' => 'TM'], ['name' => 'Test Major', 'is_active' => true]);
        $subject = Subject::firstOrCreate(
            ['code' => 'TS'],
            ['major_id' => $major->id, 'name' => 'Test Subject', 'is_active' => true]
        );

        Teacher::create([
            'user_id' => $user->id,
            'nip' => '123456789'.$user->id,
            'subject_id' => $subject->id,
            'gender' => 'male',
            'join_date' => '2020-01-01',
        ]);

        return $user->load('teacher');
    }

    public function test_teacher_can_access_dashboard(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->get('/');

        $response->assertStatus(200);
    }

    public function test_teacher_can_view_teaching_assignments(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->get(route('academic.teaching-assignments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('academic.teaching-assignments.index');
    }

    public function test_teacher_can_view_schedules(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->get(route('academic.schedules.index'));

        $response->assertStatus(200);
        $response->assertViewIs('academic.schedules.index');
    }

    public function test_teacher_can_view_journals(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->get(route('academic.journals.index'));

        $response->assertStatus(200);
        $response->assertViewIs('academic.journals.index');
    }

    public function test_teacher_can_view_attendance(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->get(route('academic.attendance.index'));

        $response->assertStatus(200);
        $response->assertViewIs('academic.attendance.index');
    }

    public function test_teacher_can_view_assignments(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->get(route('academic.assignments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('academic.assignments.index');
    }

    public function test_teacher_can_view_question_banks(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->get(route('exam.question-banks.index'));

        $response->assertStatus(200);
        $response->assertViewIs('exam.question-banks.index');
    }

    public function test_teacher_can_create_question_bank(): void
    {
        $teacher = $this->makeTeacher();

        $data = [
            'name' => 'Bank Soal Test',
            'description' => 'Deskripsi bank soal test',
            'subject_id' => $teacher->teacher->subject_id,
        ];

        $response = $this->actingAs($teacher)->post(route('exam.question-banks.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('question_banks', ['name' => 'Bank Soal Test']);
    }

    public function test_teacher_can_view_exams(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->get(route('exam.exams.index'));

        $response->assertStatus(200);
        $response->assertViewIs('exam.exams.index');
    }

    public function test_teacher_can_create_exam(): void
    {
        $teacher = $this->makeTeacher();

        $academicYear = AcademicYear::first();
        $semester = Semester::first();
        $subject = $teacher->teacher->subject;
        $major = Major::first();
        $classRoom = ClassRoom::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'major_id' => $major->id,
            'name' => 'Test Class',
            'level' => 10,
        ]);

        $data = [
            'title' => 'Ujian Test',
            'description' => 'Deskripsi ujian test',
            'subject_id' => $subject->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'type' => 'quiz',
            'duration_minutes' => 60,
            'attempt_limit' => 1,
            'start_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'passing_score' => 70,
            'show_result' => true,
            'random_question' => false,
            'random_option' => false,
            'class_ids' => [$classRoom->id],
        ];

        $response = $this->actingAs($teacher)->post(route('exam.exams.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('exams', ['title' => 'Ujian Test']);
    }

    public function test_teacher_can_create_assignment(): void
    {
        $teacher = $this->makeTeacher();

        $academicYear = AcademicYear::first();
        $semester = Semester::first();
        $major = Major::first();
        $classRoom = ClassRoom::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'major_id' => $major->id,
            'name' => 'Test Class',
            'level' => 10,
        ]);

        $data = [
            'title' => 'Tugas Test',
            'description' => 'Deskripsi tugas test',
            'subject_id' => $teacher->teacher->subject_id,
            'class_id' => $classRoom->id,
            'deadline' => now()->addWeek()->format('Y-m-d H:i:s'),
            'max_score' => 100,
            'is_published' => true,
        ];

        $response = $this->actingAs($teacher)->post(route('academic.assignments.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('assignments', ['title' => 'Tugas Test']);
    }

    public function test_teacher_can_create_journal(): void
    {
        $teacher = $this->makeTeacher();

        $academicYear = AcademicYear::first();
        $semester = Semester::first();
        $major = Major::first();
        $classRoom = ClassRoom::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'major_id' => $major->id,
            'name' => 'Test Class',
            'level' => 10,
        ]);

        $teachingAssignment = TeachingAssignment::create([
            'teacher_id' => $teacher->teacher->id,
            'subject_id' => $teacher->teacher->subject_id,
            'class_id' => $classRoom->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
        ]);

        $schedule = Schedule::create([
            'teaching_assignment_id' => $teachingAssignment->id,
            'day' => 'monday',
            'start_time' => '08:00:00',
            'end_time' => '09:30:00',
        ]);

        $data = [
            'schedule_id' => $schedule->id,
            'class_id' => $classRoom->id,
            'subject_id' => $teacher->teacher->subject_id,
            'date' => now()->format('Y-m-d'),
            'material' => 'Materi Pembelajaran Test',
            'learning_objectives' => 'Tujuan pembelajaran test',
            'activities' => 'Kegiatan pembelajaran test',
            'notes' => 'Catatan pembelajaran',
        ];

        $response = $this->actingAs($teacher)->post(route('academic.journals.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('journals', ['material' => 'Materi Pembelajaran Test']);
    }

    public function test_non_teacher_cannot_access_teacher_features(): void
    {
        $role = Role::where('name', 'siswa')->first();
        $student = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($student)->get(route('academic.teaching-assignments.index'));

        $response->assertStatus(403);
    }
}
