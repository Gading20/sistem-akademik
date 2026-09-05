<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreAcademicYearRequest;
use App\Http\Requests\Master\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function __construct(
        protected AcademicYearService $academicYearService,
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AcademicYear::class);

        $academicYears = AcademicYear::withCount('semesters')
            ->latest('start_date')
            ->paginate(15);

        return view('master.academic-years.index', compact('academicYears'));
    }

    public function create(): View
    {
        $this->authorize('create', AcademicYear::class);

        return view('master.academic-years.create');
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $this->authorize('create', AcademicYear::class);

        $data = $request->validated();
        $data['start_date'] = $data['start_year'] . '-07-15';
        $data['end_date'] = $data['end_year'] . '-06-30';
        unset($data['start_year'], $data['end_year']);

        $year = $this->academicYearService->create($data);

        $this->auditLogService->log(
            Auth::user(),
            'created',
            AcademicYear::class,
            $year->id,
            null,
            $year->toArray()
        );

        return redirect()->route('master.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil dibuat.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        $this->authorize('update', $academicYear);

        return view('master.academic-years.edit', compact('academicYear'));
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('update', $academicYear);

        $oldData = $academicYear->toArray();
        $data = $request->validated();

        if (isset($data['start_year'])) {
            $data['start_date'] = $data['start_year'] . '-07-15';
            unset($data['start_year']);
        }
        if (isset($data['end_year'])) {
            $data['end_date'] = $data['end_year'] . '-06-30';
            unset($data['end_year']);
        }

        $year = $this->academicYearService->update($academicYear, $data);

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            AcademicYear::class,
            $year->id,
            $oldData,
            $year->toArray()
        );

        return redirect()->route('master.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('delete', $academicYear);

        $academicYear->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            AcademicYear::class,
            $academicYear->id
        );

        return redirect()->route('master.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('update', $academicYear);

        $year = $this->academicYearService->activate($academicYear);

        $this->auditLogService->log(
            Auth::user(),
            'activated',
            AcademicYear::class,
            $year->id
        );

        return redirect()->route('master.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil diaktifkan.');
    }
}
