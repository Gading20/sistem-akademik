<?php

use App\Http\Controllers\Announcement\AnnouncementController;
use App\Http\Controllers\Academic\AssignmentController;
use App\Http\Controllers\Academic\AttendanceController;
use App\Http\Controllers\Academic\JournalController;
use App\Http\Controllers\Academic\ScheduleController;
use App\Http\Controllers\Academic\TeachingAssignmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Exam\ExamAttemptController;
use App\Http\Controllers\Exam\ExamController;
use App\Http\Controllers\Exam\QuestionBankController;
use App\Http\Controllers\Exam\QuestionController;
use App\Http\Controllers\Grading\GradeController;
use App\Http\Controllers\Grading\GradingConfigController;
use App\Http\Controllers\Master\AcademicYearController;
use App\Http\Controllers\Master\ClassController;
use App\Http\Controllers\Master\CompetencyController;
use App\Http\Controllers\Master\MajorController;
use App\Http\Controllers\Master\RoomController;
use App\Http\Controllers\Master\SemesterController;
use App\Http\Controllers\Master\StudentController;
use App\Http\Controllers\Master\SubjectController;
use App\Http\Controllers\Master\TeacherController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Report\ReportCardController;
use App\Http\Controllers\Report\ReportController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
// Batasi percobaan login per IP (10 kali/menit) selain lockout per-akun di LoginRequest.
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Master Data - accessible by super_admin, admin_sekolah
    Route::middleware(['role:super_admin,admin_sekolah'])->prefix('master')->name('master.')->group(function () {
        Route::resource('academic-years', AcademicYearController::class)->except(['show']);
        Route::post('academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
        Route::resource('semesters', SemesterController::class)->except(['show']);
        Route::post('semesters/{semester}/activate', [SemesterController::class, 'activate'])->name('semesters.activate');
        Route::resource('majors', MajorController::class)->except(['show']);
        Route::resource('competencies', CompetencyController::class)->except(['show']);
        Route::resource('rooms', RoomController::class)->except(['show']);
        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::resource('classes', ClassController::class)->except(['show']);
        Route::get('classes/{class}/members', [ClassController::class, 'manageMembers'])->name('classes.members');
        Route::post('classes/{class}/members', [ClassController::class, 'addMember'])->name('classes.members.add');
        Route::delete('classes/{class}/members/{member}', [ClassController::class, 'removeMember'])->name('classes.members.remove');
        Route::get('students/import', [StudentController::class, 'import'])->name('students.import');
        Route::post('students/import', [StudentController::class, 'processImport'])->name('students.import.process');
        Route::get('students/import/template', [StudentController::class, 'downloadTemplate'])->name('students.import.template');
        Route::resource('students', StudentController::class)->except(['show']);
        Route::resource('teachers', TeacherController::class)->except(['show']);
    });

    // Academic - accessible by super_admin, admin_sekolah, guru, wali_kelas
    Route::middleware(['role:super_admin,admin_sekolah,guru,wali_kelas'])->prefix('academic')->name('academic.')->group(function () {
        Route::resource('teaching-assignments', TeachingAssignmentController::class)->except(['show']);
        Route::resource('schedules', ScheduleController::class);
        Route::resource('journals', JournalController::class)->except(['show']);
        Route::resource('assignments', AssignmentController::class)->except(['show']);
        Route::get('assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('assignments.submissions');
        Route::post('assignments/{assignment}/submissions/{submission}/grade', [AssignmentController::class, 'grade'])->name('assignments.grade');
    });

    // Attendance - for teachers and above
    Route::middleware(['role:super_admin,admin_sekolah,guru,wali_kelas'])->prefix('academic')->name('academic.')->group(function () {
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('attendance', [AttendanceController::class, 'bulkRecord'])->name('attendance.bulk-record');
        Route::get('attendance/student/{student}', [AttendanceController::class, 'byStudent'])->name('attendance.student');
    });

    // Attendance history - for students (view own record only)
    Route::middleware(['role:siswa'])->prefix('academic')->name('academic.')->group(function () {
        Route::get('attendance/riwayat', [AttendanceController::class, 'myHistory'])->name('attendance.my');
    });

    // Exams - for teachers and above
    Route::middleware(['role:super_admin,admin_sekolah,guru'])->prefix('exam')->name('exam.')->group(function () {
        Route::resource('question-banks', QuestionBankController::class)->except(['show']);
        Route::resource('question-banks/{questionBank}/questions', QuestionController::class)->except(['show']);
        Route::resource('exams', ExamController::class)->except(['show']);
        Route::post('exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
        Route::get('exams/{exam}/results', [ExamController::class, 'results'])->name('exams.results');
    });

    // Exams - for students
    Route::middleware(['role:siswa'])->prefix('exam')->name('exam.')->group(function () {
        Route::get('available', [ExamAttemptController::class, 'available'])->name('exams.available');
        Route::post('exams/{exam}/start', [ExamAttemptController::class, 'start'])->name('exams.start');
        Route::get('exams/{examAttempt}/attempt', [ExamAttemptController::class, 'show'])->name('exams.attempt');
        Route::post('exams/{examAttempt}/attempt/answer', [ExamAttemptController::class, 'answer'])->name('exams.answer')->middleware('throttle:120,1');
        Route::post('exams/{examAttempt}/attempt/autosave', [ExamAttemptController::class, 'answer'])->name('exams.autosave')->middleware('throttle:120,1');
        Route::post('exams/{examAttempt}/attempt/submit', [ExamAttemptController::class, 'submit'])->name('exams.submit');
        Route::get('exams/{examAttempt}/result', [ExamAttemptController::class, 'result'])->name('exams.result');
    });

    // Grading - for teachers and above
    Route::middleware(['role:super_admin,admin_sekolah,guru'])->prefix('grading')->name('grading.')->group(function () {
        Route::resource('configs', GradingConfigController::class)->except(['show']);
        Route::get('grades', [GradeController::class, 'index'])->name('grades.index');
        Route::post('grades', [GradeController::class, 'store'])->name('grades.store');
        Route::get('grades/input', [GradeController::class, 'input'])->name('grades.input');
        Route::get('grades/class/{class}', [GradeController::class, 'byClass'])->name('grades.class');
    });

    // Reports - for admin and above
    Route::middleware(['role:super_admin,admin_sekolah,kepala_sekolah,wakil_kepala_sekolah'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('grades', [ReportController::class, 'grades'])->name('grades');
        Route::get('ranking', [ReportController::class, 'ranking'])->name('ranking');
        Route::resource('report-cards', ReportCardController::class)->only(['index', 'show']);
        Route::post('report-cards/generate', [ReportCardController::class, 'generate'])->name('report-cards.generate');
        Route::post('report-cards/{reportCard}/finalize', [ReportCardController::class, 'finalize'])->name('report-cards.finalize');
    });

    // Announcements
    Route::resource('announcements', AnnouncementController::class)->except(['show']);
});
