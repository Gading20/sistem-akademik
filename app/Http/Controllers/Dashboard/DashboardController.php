<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\ClassMember;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $roleName = $user->role?->name;

        $data = match ($roleName) {
            RoleEnum::SUPER_ADMIN->value, RoleEnum::ADMIN_SEKOLAH->value => $this->adminData(),
            RoleEnum::KEPALA_SEKOLAH->value, RoleEnum::WAKIL_KEPALA_SEKOLAH->value => $this->leadershipData(),
            RoleEnum::GURU->value, RoleEnum::WALI_KELAS->value => $this->teacherData($user),
            RoleEnum::SISWA->value => $this->studentData($user),
            RoleEnum::ORANG_TUA->value => $this->parentData($user),
            default => [],
        };

        $announcements = Announcement::published()
            ->notExpired()
            ->byTargetRole($user->role?->name)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', array_merge($data, [
            'user' => $user,
            'announcements' => $announcements,
        ]));
    }

    private function adminData(): array
    {
        $activeYear = AcademicYear::active()->first();

        return [
            'total_students' => Student::active()->count(),
            'total_teachers' => Teacher::active()->count(),
            'active_year' => $activeYear,
            'total_classes' => $activeYear ? $activeYear->classes()->active()->count() : 0,
            'total_exams' => Exam::count(),
            'teaching_classes' => collect(),
            'today_schedules' => collect(),
        ];
    }

    private function leadershipData(): array
    {
        $activeYear = AcademicYear::active()->first();

        return [
            'total_students' => Student::active()->count(),
            'total_teachers' => Teacher::active()->count(),
            'active_year' => $activeYear,
            'total_classes' => $activeYear ? $activeYear->classes()->active()->count() : 0,
            'teaching_classes' => collect(),
            'today_schedules' => collect(),
        ];
    }

    private function teacherData($user): array
    {
        $teacher = $user->teacher;

        return [
            'teacher' => $teacher,
            'teaching_classes' => $teacher ? $teacher->teachingAssignments()->get() : collect(),
            'today_schedules' => $teacher
                ? $teacher->teachingAssignments()->with('schedules')->get()->pluck('schedules')->flatten()->where('day', strtolower(now()->translatedFormat('l')))->values()
                : collect(),
        ];
    }

    private function studentData($user): array
    {
        $student = $user->student;
        $activeYear = AcademicYear::active()->first();

        return [
            'student' => $student,
            'active_year' => $activeYear,
            'attendance_summary' => $student && $activeYear
                ? $this->getAttendanceSummary($student->id, $activeYear->id)
                : null,
            'recent_grades' => $student && $activeYear
                ? Grade::where('student_id', $student->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->with('subject')
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect(),
            'teaching_classes' => collect(),
            'today_schedules' => collect(),
            'total_students' => 0,
            'total_teachers' => 0,
            'total_classes' => 0,
            'total_exams' => 0,
        ];
    }

    private function parentData($user): array
    {
        $children = $user->parentProfile?->students()->with(['user', 'classRoom'])->get() ?? collect();

        return [
            'children' => $children,
            'teaching_classes' => collect(),
            'today_schedules' => collect(),
            'total_students' => 0,
            'total_teachers' => 0,
            'total_classes' => 0,
            'total_exams' => 0,
        ];
    }

    private function getAttendanceSummary(int $studentId, int $academicYearId): array
    {
        $attendances = Attendance::where('student_id', $studentId)
            ->whereHas('schedule.teachingAssignment', function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            })
            ->get();

        return [
            'total' => $attendances->count(),
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'alpa' => $attendances->where('status', 'alpa')->count(),
        ];
    }
}
