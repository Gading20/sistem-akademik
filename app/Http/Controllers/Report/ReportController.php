<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassMember;
use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\Student;
use App\Services\AttendanceService;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected GradeService $gradeService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', ReportCard::class);

        return view('reports.index');
    }

    public function attendance(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);

        $classId = $request->input('class_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');
        $studentId = $request->input('student_id');

        $summary = null;
        $attendances = collect();

        if ($studentId && $academicYearId && $semesterId) {
            $summary = $this->attendanceService->getAttendanceSummary($studentId, $academicYearId, $semesterId);
            $attendances = $this->attendanceService->getStudentAttendance($studentId, $academicYearId, $semesterId);
        } elseif ($classId && $request->input('date')) {
            $attendances = $this->attendanceService->getClassAttendance($classId, $request->input('date'));
        }

        $classes = ClassRoom::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $students = Student::with('user')->active()->get();

        return view('reports.attendance', compact('summary', 'attendances', 'classes', 'academicYears', 'students', 'classId', 'academicYearId', 'semesterId', 'studentId'));
    }

    public function grades(Request $request): View
    {
        $this->authorize('viewAny', Grade::class);

        $studentId = $request->input('student_id');
        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $grades = collect();
        $student = null;

        if ($studentId && $academicYearId && $semesterId) {
            $grades = $this->gradeService->getStudentGrades($studentId, $academicYearId, $semesterId);
            $student = Student::with('user')->find($studentId);
        }

        $classes = ClassRoom::active()->orderBy('name')->get();
        $students = Student::with('user')->active()->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('reports.grades', compact('grades', 'student', 'classes', 'students', 'academicYears', 'studentId', 'classId', 'subjectId', 'academicYearId', 'semesterId'));
    }

    public function ranking(Request $request): View
    {
        $this->authorize('viewAny', ReportCard::class);

        $classId = $request->input('class_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $rankings = collect();

        if ($classId && $academicYearId && $semesterId) {
            $rankings = ReportCard::with(['student.user'])
                ->where('class_id', $classId)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->orderBy('rank')
                ->get();
        }

        $classes = ClassRoom::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('reports.ranking', compact('rankings', 'classes', 'academicYears', 'classId', 'academicYearId', 'semesterId'));
    }

}
