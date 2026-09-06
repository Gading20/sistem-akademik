<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
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

        $classes = ClassRoom::active()->orderBy('name')->get();
        $students = Student::with('user')->active()->orderBy('nis')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('start_date')->get();

        $reportData = collect();
        $student = $studentId ? $students->firstWhere('id', (int) $studentId) : null;

        $hasScope = (bool) ($studentId || $classId);

        if ($hasScope) {
            $academicYear = $academicYearId
                ? AcademicYear::find($academicYearId)
                : (AcademicYear::active()->first() ?? AcademicYear::latest('start_date')->first());

            $semester = $semesterId
                ? Semester::find($semesterId)
                : (Semester::active()->first() ?? Semester::latest('start_date')->first());

            $scopedStudents = $classId
                ? $students->where('class_id', (int) $classId)->values()
                : $students->where('id', (int) $studentId)->values();

            if ($academicYear && $semester && $scopedStudents->isNotEmpty()) {
                $gradeRows = Grade::whereIn('student_id', $scopedStudents->pluck('id'))
                    ->where('academic_year_id', $academicYear->id)
                    ->where('semester_id', $semester->id)
                    ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
                    ->get(['student_id', 'subject_id', 'final_score']);

                $reportData = $scopedStudents->map(function ($scopedStudent) use ($gradeRows) {
                    $scores = $gradeRows
                        ->where('student_id', $scopedStudent->id)
                        ->filter(fn ($grade) => $grade->final_score !== null)
                        ->mapWithKeys(fn ($grade) => [
                            $grade->subject_id => round((float) $grade->final_score, 2),
                        ]);

                    return [
                        'student' => $scopedStudent,
                        'grades' => $scores,
                        'average' => $scores->isNotEmpty() ? round($scores->avg(), 2) : null,
                    ];
                });
            }
        }

        return view('reports.grades', compact(
            'reportData',
            'student',
            'classes',
            'students',
            'subjects',
            'academicYears',
            'semesters',
            'studentId',
            'classId',
            'subjectId',
            'academicYearId',
            'semesterId'
        ));
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
