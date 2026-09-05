<?php

namespace App\Http\Controllers\Grading;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grading\StoreGradingConfigRequest;
use App\Http\Requests\Grading\UpdateGradingConfigRequest;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\GradingConfig;
use App\Models\Semester;
use App\Models\Subject;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradingConfigController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', GradingConfig::class);

        $classId = $request->input('class_id');
        $subjectId = $request->input('subject_id');

        $configs = GradingConfig::with(['subject', 'classRoom', 'academicYear', 'semester'])
            ->when($classId, fn ($q) => $q->where('class_id', $classId))
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->latest()
            ->paginate(15);

        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        return view('grading.configs.index', compact('configs', 'classes', 'subjects', 'classId', 'subjectId'));
    }

    public function create(): View
    {
        $this->authorize('create', GradingConfig::class);

        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('start_date')->get();

        return view('grading.configs.create', compact('classes', 'subjects', 'academicYears', 'semesters'));
    }

    public function store(StoreGradingConfigRequest $request): RedirectResponse
    {
        $this->authorize('create', GradingConfig::class);

        $totalWeights = ($request->tugas_weight ?? 0)
            + ($request->quiz_weight ?? 0)
            + ($request->uts_weight ?? 0)
            + ($request->uas_weight ?? 0)
            + ($request->practical_weight ?? 0)
            + ($request->project_weight ?? 0);

        if ($totalWeights != 100) {
            return back()->withErrors(['tugas_weight' => 'Total bobot penilaian harus 100%.'])->withInput();
        }

        $config = GradingConfig::create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            GradingConfig::class,
            $config->id,
            null,
            $config->toArray()
        );

        return redirect()->route('grading.configs.index')
            ->with('success', 'Konfigurasi penilaian berhasil dibuat.');
    }

    public function edit(GradingConfig $gradingConfig): View
    {
        $this->authorize('update', $gradingConfig);

        $classes = ClassRoom::active()->orderBy('name')->get();
        $subjects = Subject::active()->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('start_date')->get();

        return view('grading.configs.edit', compact('gradingConfig', 'classes', 'subjects', 'academicYears', 'semesters'));
    }

    public function update(UpdateGradingConfigRequest $request, GradingConfig $gradingConfig): RedirectResponse
    {
        $this->authorize('update', $gradingConfig);

        $data = $request->validated();

        $totalWeights = ($data['tugas_weight'] ?? 0)
            + ($data['quiz_weight'] ?? 0)
            + ($data['uts_weight'] ?? 0)
            + ($data['uas_weight'] ?? 0)
            + ($data['practical_weight'] ?? 0)
            + ($data['project_weight'] ?? 0);

        if ($totalWeights != 100) {
            return back()->withErrors(['tugas_weight' => 'Total bobot penilaian harus 100%.'])->withInput();
        }

        $oldData = $gradingConfig->toArray();
        $gradingConfig->update($data);

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            GradingConfig::class,
            $gradingConfig->id,
            $oldData,
            $gradingConfig->fresh()->toArray()
        );

        return redirect()->route('grading.configs.index')
            ->with('success', 'Konfigurasi penilaian berhasil diperbarui.');
    }

    public function destroy(GradingConfig $gradingConfig): RedirectResponse
    {
        $this->authorize('delete', $gradingConfig);

        $gradingConfig->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            GradingConfig::class,
            $gradingConfig->id
        );

        return redirect()->route('grading.configs.index')
            ->with('success', 'Konfigurasi penilaian berhasil dihapus.');
    }
}
