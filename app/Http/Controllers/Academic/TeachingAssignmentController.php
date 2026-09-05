<?php

namespace App\Http\Controllers\Academic;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreTeachingAssignmentRequest;
use App\Http\Requests\Academic\UpdateTeachingAssignmentRequest;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeachingAssignmentController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', TeachingAssignment::class);

        $teacherId = $request->input('teacher_id');
        $classId = $request->input('class_id');
        $academicYearId = $request->input('academic_year_id');

        $query = TeachingAssignment::with(['teacher.user', 'subject', 'classRoom', 'academicYear', 'semester']);

        if (auth()->user()->hasRole(RoleEnum::GURU->value)) {
            $query->where('teacher_id', auth()->user()->teacher?->id);
        } else {
            $query->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId));
        }

        $query->when($classId, fn ($q) => $q->where('class_id', $classId));
        $query->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId));

        $allAssignments = $query->latest()->get();

        $grouped = $allAssignments->groupBy(fn ($item) => implode('-', [
            $item->teacher_id,
            $item->subject_id,
            $item->academic_year_id,
            $item->semester_id,
        ]))->map(function ($group) {
            $first = $group->first();
            return (object) [
                'id' => $first->id,
                'teacher' => $first->teacher,
                'subject' => $first->subject,
                'academicYear' => $first->academicYear,
                'semester' => $first->semester,
                'classes' => $group->pluck('classRoom')->filter(),
                'ids' => $group->pluck('id'),
            ];
        });

        $teachingAssignments = new \Illuminate\Pagination\LengthAwarePaginator(
            $grouped->values()->forPage($request->input('page', 1), 15),
            $grouped->count(),
            15,
            $request->input('page', 1),
            ['path' => $request->url()]
        );

        $teachers = Teacher::with('user')->active()->orderBy('nip')->get();
        $classes = ClassRoom::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('academic.teaching-assignments.index', compact('teachingAssignments', 'teachers', 'classes', 'academicYears', 'teacherId', 'classId', 'academicYearId'));
    }

    public function create(): View
    {
        $this->authorize('create', TeachingAssignment::class);

        $isGuru = auth()->user()->hasRole(RoleEnum::GURU->value);
        $teachers = Teacher::with('user')->active()->orderBy('nip')->get();
        $subjects = Subject::orderBy('name')->get();
        $classes = ClassRoom::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('academic.teaching-assignments.create', compact('teachers', 'subjects', 'classes', 'academicYears', 'semesters', 'isGuru'));
    }

    public function store(StoreTeachingAssignmentRequest $request): RedirectResponse
    {
        $this->authorize('create', TeachingAssignment::class);

        $data = $request->validated();

        if (auth()->user()->hasRole(RoleEnum::GURU->value)) {
            $data['teacher_id'] = auth()->user()->teacher?->id;
        }

        if (empty($data['teacher_id'])) {
            return back()->withErrors(['teacher_id' => 'Guru wajib dipilih.'])->withInput();
        }

        $classIds = $data['class_ids'];
        unset($data['class_ids']);

        $created = 0;
        foreach ($classIds as $classId) {
            $exists = TeachingAssignment::where(array_merge($data, ['class_id' => $classId]))->exists();
            if (!$exists) {
                TeachingAssignment::create(array_merge($data, ['class_id' => $classId]));
                $created++;
            }
        }

        if ($created === 0) {
            return back()->withErrors(['class_ids' => 'Semua penugasan mengajar yang dipilih sudah ada.'])->withInput();
        }

        return redirect()->route('academic.teaching-assignments.index')
            ->with('success', "{$created} penugasan mengajar berhasil dibuat.");
    }

    public function edit(TeachingAssignment $teachingAssignment): View
    {
        $this->authorize('update', $teachingAssignment);

        $teachers = Teacher::with('user')->active()->orderBy('nip')->get();
        $subjects = Subject::orderBy('name')->get();
        $classes = ClassRoom::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('academic.teaching-assignments.edit', compact('teachingAssignment', 'teachers', 'subjects', 'classes', 'academicYears', 'semesters'));
    }

    public function update(UpdateTeachingAssignmentRequest $request, TeachingAssignment $teachingAssignment): RedirectResponse
    {
        $this->authorize('update', $teachingAssignment);

        $data = $request->validated();
        $classIds = $data['class_ids'] ?? null;
        unset($data['class_ids']);

        $teacherId = $data['teacher_id'] ?? $teachingAssignment->teacher_id;
        $subjectId = $data['subject_id'] ?? $teachingAssignment->subject_id;
        $ayId = $data['academic_year_id'] ?? $teachingAssignment->academic_year_id;
        $semesterId = $data['semester_id'] ?? $teachingAssignment->semester_id;

        $existingRecords = TeachingAssignment::where('teacher_id', $teachingAssignment->teacher_id)
            ->where('subject_id', $teachingAssignment->subject_id)
            ->where('academic_year_id', $teachingAssignment->academic_year_id)
            ->where('semester_id', $teachingAssignment->semester_id)
            ->get();

        if ($classIds) {
            $existingClassIds = $existingRecords->pluck('class_id')->toArray();

            $toDelete = $existingRecords->whereNotIn('class_id', $classIds);
            $toKeep = $existingRecords->whereIn('class_id', $classIds);
            $toAdd = array_diff($classIds, $existingClassIds);

            foreach ($toDelete as $record) {
                if (!$record->schedules()->exists()) {
                    $record->delete();
                }
            }

            foreach ($toKeep as $record) {
                $record->update([
                    'teacher_id' => $teacherId,
                    'subject_id' => $subjectId,
                    'academic_year_id' => $ayId,
                    'semester_id' => $semesterId,
                ]);
            }

            foreach ($toAdd as $classId) {
                TeachingAssignment::create([
                    'teacher_id' => $teacherId,
                    'subject_id' => $subjectId,
                    'class_id' => $classId,
                    'academic_year_id' => $ayId,
                    'semester_id' => $semesterId,
                ]);
            }
        } else {
            $existingRecords->each(function ($record) {
                if (!$record->schedules()->exists()) {
                    $record->delete();
                }
            });
        }

        return redirect()->route('academic.teaching-assignments.index')
            ->with('success', 'Penugasan mengajar berhasil diperbarui.');
    }

    public function destroy(TeachingAssignment $teachingAssignment): RedirectResponse
    {
        $this->authorize('delete', $teachingAssignment);

        if ($teachingAssignment->schedules()->exists()) {
            return back()->withErrors(['delete' => 'Penugasan mengajar tidak dapat dihapus karena masih memiliki jadwal terkait.']);
        }

        $teachingAssignment->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            TeachingAssignment::class,
            $teachingAssignment->id
        );

        return redirect()->route('academic.teaching-assignments.index')
            ->with('success', 'Penugasan mengajar berhasil dihapus.');
    }
}
