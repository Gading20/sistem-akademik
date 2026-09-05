<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreSemesterRequest;
use App\Http\Requests\Master\UpdateSemesterRequest;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\AuditLogService;
use App\Services\SemesterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SemesterController extends Controller
{
    public function __construct(
        protected SemesterService $semesterService,
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Semester::class);

        $academicYearId = $request->input('academic_year_id');

        $semesters = Semester::with('academicYear')
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->latest('start_date')
            ->paginate(15);

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('master.semesters.index', compact('semesters', 'academicYears', 'academicYearId'));
    }

    public function create(): View
    {
        $this->authorize('create', Semester::class);

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('master.semesters.create', compact('academicYears'));
    }

    public function store(StoreSemesterRequest $request): RedirectResponse
    {
        $this->authorize('create', Semester::class);

        $semester = $this->semesterService->create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            Semester::class,
            $semester->id,
            null,
            $semester->toArray()
        );

        return redirect()->route('master.semesters.index')
            ->with('success', 'Semester berhasil dibuat.');
    }

    public function edit(Semester $semester): View
    {
        $this->authorize('update', $semester);

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('master.semesters.edit', compact('semester', 'academicYears'));
    }

    public function update(UpdateSemesterRequest $request, Semester $semester): RedirectResponse
    {
        $this->authorize('update', $semester);

        $oldData = $semester->toArray();
        $semester = $this->semesterService->update($semester, $request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            Semester::class,
            $semester->id,
            $oldData,
            $semester->toArray()
        );

        return redirect()->route('master.semesters.index')
            ->with('success', 'Semester berhasil diperbarui.');
    }

    public function destroy(Semester $semester): RedirectResponse
    {
        $this->authorize('delete', $semester);

        $semester->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Semester::class,
            $semester->id
        );

        return redirect()->route('master.semesters.index')
            ->with('success', 'Semester berhasil dihapus.');
    }

    public function activate(Semester $semester): RedirectResponse
    {
        $this->authorize('update', $semester);

        $semester = $this->semesterService->activate($semester);

        $this->auditLogService->log(
            Auth::user(),
            'activated',
            Semester::class,
            $semester->id
        );

        return redirect()->route('master.semesters.index')
            ->with('success', 'Semester berhasil diaktifkan.');
    }
}
