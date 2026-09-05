<?php

namespace App\Http\Controllers\Grading;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grading\StoreGradeRequest;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\GradingConfig;
use App\Models\Subject;
use App\Services\AuditLogService;
use App\Services\GradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function __construct(
        protected GradeService $gradeService,
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Grade::class);

        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $grades = Grade::with(['student.user', 'subject', 'classRoom'])
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
            ->latest()
            ->paginate(20);

        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $class = $classId ? ClassRoom::find($classId) : null;
        $subject = $subjectId ? Subject::find($subjectId) : null;
        $components = $this->gradeService->getComponents();

        return view('grading.grades.index', compact('grades', 'classes', 'subjects', 'academicYears', 'classId', 'subjectId', 'academicYearId', 'semesterId', 'class', 'subject', 'components'));
    }

    public function store(StoreGradeRequest $request): RedirectResponse
    {
        $this->authorize('create', Grade::class);

        $grade = Grade::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'subject_id' => $request->subject_id,
                'class_id' => $request->class_id,
                'academic_year_id' => $request->academic_year_id,
                'semester_id' => $request->semester_id,
            ],
            [
                'tugas_score' => $request->tugas_score,
                'quiz_score' => $request->quiz_score,
                'uts_score' => $request->uts_score,
                'uas_score' => $request->uas_score,
                'practical_score' => $request->practical_score,
                'project_score' => $request->project_score,
                'final_score' => $request->final_score,
            ]
        );

        $this->auditLogService->log(
            Auth::user(),
            'created',
            Grade::class,
            $grade->id,
            null,
            $grade->toArray()
        );

        return redirect()->route('grading.grades.index')
            ->with('success', 'Nilai berhasil disimpan.');
    }

    public function byClass(Request $request): View
    {
        $this->authorize('viewAny', Grade::class);

        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $grades = collect();
        $class = null;
        $subject = null;

        if ($classId && $subjectId && $academicYearId && $semesterId) {
            $grades = $this->gradeService->getClassGrades($classId, $subjectId, $academicYearId, $semesterId);
            $class = ClassRoom::find($classId);
            $subject = Subject::find($subjectId);
        }

        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $components = $this->gradeService->getComponents();

        return view('grading.grades.index', compact('grades', 'class', 'subject', 'classes', 'subjects', 'academicYears', 'classId', 'subjectId', 'academicYearId', 'semesterId', 'components'));
    }

    public function input(Request $request): View
    {
        $this->authorize('viewAny', Grade::class);

        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');
        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $class = null;
        $subject = null;
        $config = null;
        $students = collect();
        $existingGrades = [];

        if ($classId && $subjectId && $academicYearId && $semesterId) {
            $class = ClassRoom::find($classId);
            $subject = Subject::find($subjectId);
            $config = GradingConfig::where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->first();

            $students = \App\Models\Student::with('user')
                ->where('class_id', $classId)
                ->where('status', 'active')
                ->orderBy('nis')
                ->get();

            $existingGradeRecords = Grade::where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->get()
                ->keyBy('student_id');

            foreach ($existingGradeRecords as $studentId => $grade) {
                $existingGrades[$studentId] = [
                    'tugas' => $grade->tugas_score,
                    'quiz' => $grade->quiz_score,
                    'uts' => $grade->uts_score,
                    'uas' => $grade->uas_score,
                    'practical' => $grade->practical_score,
                    'project' => $grade->project_score,
                ];
            }
        }

        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = \App\Models\Semester::orderByDesc('start_date')->get();

        $components = collect();
        if ($config) {
            $weightMap = [
                'tugas' => ['name' => 'Tugas', 'field' => 'tugas_weight'],
                'quiz' => ['name' => 'Quiz', 'field' => 'quiz_weight'],
                'uts' => ['name' => 'UTS', 'field' => 'uts_weight'],
                'uas' => ['name' => 'UAS', 'field' => 'uas_weight'],
                'practical' => ['name' => 'Praktik', 'field' => 'practical_weight'],
                'project' => ['name' => 'Proyek', 'field' => 'project_weight'],
            ];

            foreach ($weightMap as $id => $meta) {
                $weight = (float) $config->{$meta['field']};
                if ($weight > 0) {
                    $components->push((object) [
                        'id' => $id,
                        'name' => $meta['name'],
                        'weight' => $weight,
                    ]);
                }
            }
        }

        return view('grading.grades.input', compact(
            'class', 'subject', 'config', 'students', 'existingGrades',
            'classes', 'subjects', 'academicYears', 'semesters',
            'classId', 'subjectId', 'academicYearId', 'semesterId', 'components'
        ));
    }
}
