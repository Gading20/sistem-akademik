<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\Student;
use App\Services\AuditLogService;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportCardController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ReportCard::class);

        $classId = $request->input('class_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $reportCards = ReportCard::with(['student.user', 'classRoom', 'academicYear', 'semester', 'finalizer'])
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
            ->latest()
            ->paginate(20);

        $classes = ClassRoom::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('id')->get();

        return view('reports.report-cards.index', compact('reportCards', 'classes', 'academicYears', 'semesters', 'classId', 'academicYearId', 'semesterId'));
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->authorize('create', ReportCard::class);

        $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
        ]);

        $class = ClassRoom::findOrFail($request->class_id);
        $year = AcademicYear::findOrFail($request->academic_year_id);
        $semester = Semester::findOrFail($request->semester_id);

        $members = $class->classMembers()->where('is_active', true)->get();

        $generated = 0;
        foreach ($members as $member) {
            $student = Student::find($member->student_id);
            if ($student) {
                $this->reportService->generateReportCard($student, $class, $year, $semester);
                $generated++;
            }
        }

        $this->auditLogService->log(
            Auth::user(),
            'generated_report_cards',
            ReportCard::class,
            null,
            null,
            ['class_id' => $class->id, 'count' => $generated]
        );

        return redirect()->route('reports.report-cards.index')
            ->with('success', "Berhasil generate rapor untuk {$generated} siswa.");
    }

    public function show(ReportCard $reportCard): View
    {
        $this->authorize('view', $reportCard);

        $report = $this->reportService->getStudentReport(
            $reportCard->student_id,
            $reportCard->academic_year_id,
            $reportCard->semester_id
        );

        $semester = $reportCard->semester;
        $academicYear = $reportCard->academicYear;
        $homeroomTeacher = $reportCard->classRoom->teachingAssignments()->with('teacher.user')->first()?->teacher;
        $attendanceSummary = $report['attendance'];
        $extracurricular = null;
        $notes = $reportCard->class_teacher_notes ?? null;
        $principal = \App\Models\User::where('email', 'principal@smknuru.sch.id')->first();

        $configComponents = collect(['Tugas', 'Quiz', 'UTS', 'UAS', 'Praktik', 'Proyek']);

        $grades = $report['grades']->mapWithKeys(function ($grade) use ($configComponents) {
            $subjectName = $grade->subject->name ?? '-';
            $components = [
                'Tugas' => $grade->tugas_score,
                'Quiz' => $grade->quiz_score,
                'UTS' => $grade->uts_score,
                'UAS' => $grade->uas_score,
                'Praktik' => $grade->practical_score,
                'Proyek' => $grade->project_score,
            ];
            return [$subjectName => [
                'components' => $components,
                'final' => $grade->final_score,
            ]];
        });

        return view('reports.report-cards.show', [
            'student' => $report['student'],
            'reportCard' => $reportCard,
            'semester' => $semester,
            'academicYear' => $academicYear,
            'homeroomTeacher' => $homeroomTeacher,
            'configComponents' => $configComponents,
            'grades' => $grades,
            'attendanceSummary' => $attendanceSummary,
            'extracurricular' => $extracurricular,
            'notes' => $notes,
            'principal' => $principal,
        ]);
    }

    public function finalize(ReportCard $reportCard): RedirectResponse
    {
        $this->authorize('update', $reportCard);

        try {
            $reportCard = $this->reportService->finalizeReportCard(
                $reportCard->student_id,
                $reportCard->class_id,
                $reportCard->academic_year_id,
                $reportCard->semester_id,
                Auth::id()
            );

            $this->auditLogService->log(
                Auth::user(),
                'finalized_report_card',
                ReportCard::class,
                $reportCard->id
            );

            return back()->with('success', 'Rapor berhasil difinalisasi.');
        } catch (\Exception $e) {
            return back()->withErrors(['report' => $e->getMessage()]);
        }
    }

}
