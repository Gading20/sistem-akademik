<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreCompetencyRequest;
use App\Http\Requests\Master\UpdateCompetencyRequest;
use App\Models\Competency;
use App\Models\Major;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompetencyController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Competency::class);

        $search = $request->input('search');
        $majorId = $request->input('major_id');

        $competencies = Competency::with('major')
            ->when($majorId, fn ($q) => $q->where('major_id', $majorId))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15);

        $majors = Major::orderBy('name')->get();

        return view('master.competencies.index', compact('competencies', 'majors', 'search', 'majorId'));
    }

    public function create(): View
    {
        $this->authorize('create', Competency::class);

        $majors = Major::orderBy('name')->get();

        return view('master.competencies.create', compact('majors'));
    }

    public function store(StoreCompetencyRequest $request): RedirectResponse
    {
        $this->authorize('create', Competency::class);

        $competency = Competency::create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            Competency::class,
            $competency->id,
            null,
            $competency->toArray()
        );

        return redirect()->route('master.competencies.index')
            ->with('success', 'Kompetensi keahlian berhasil dibuat.');
    }

    public function edit(Competency $competency): View
    {
        $this->authorize('update', $competency);

        $majors = Major::orderBy('name')->get();

        return view('master.competencies.edit', compact('competency', 'majors'));
    }

    public function update(UpdateCompetencyRequest $request, Competency $competency): RedirectResponse
    {
        $this->authorize('update', $competency);

        $oldData = $competency->toArray();
        $competency->update($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            Competency::class,
            $competency->id,
            $oldData,
            $competency->fresh()->toArray()
        );

        return redirect()->route('master.competencies.index')
            ->with('success', 'Kompetensi keahlian berhasil diperbarui.');
    }

    public function destroy(Competency $competency): RedirectResponse
    {
        $this->authorize('delete', $competency);

        $competency->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Competency::class,
            $competency->id
        );

        return redirect()->route('master.competencies.index')
            ->with('success', 'Kompetensi keahlian berhasil dihapus.');
    }
}
