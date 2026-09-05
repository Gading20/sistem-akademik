<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreMajorRequest;
use App\Http\Requests\Master\UpdateMajorRequest;
use App\Models\Major;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MajorController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Major::class);

        $search = $request->input('search');

        $majors = Major::withCount('competencies', 'subjects', 'classes')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15);

        return view('master.majors.index', compact('majors', 'search'));
    }

    public function create(): View
    {
        $this->authorize('create', Major::class);

        return view('master.majors.create');
    }

    public function store(StoreMajorRequest $request): RedirectResponse
    {
        $this->authorize('create', Major::class);

        $major = Major::create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            Major::class,
            $major->id,
            null,
            $major->toArray()
        );

        return redirect()->route('master.majors.index')
            ->with('success', 'Jurusan berhasil dibuat.');
    }

    public function edit(Major $major): View
    {
        $this->authorize('update', $major);

        return view('master.majors.edit', compact('major'));
    }

    public function update(UpdateMajorRequest $request, Major $major): RedirectResponse
    {
        $this->authorize('update', $major);

        $oldData = $major->toArray();
        $major->update($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            Major::class,
            $major->id,
            $oldData,
            $major->fresh()->toArray()
        );

        return redirect()->route('master.majors.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Major $major): RedirectResponse
    {
        $this->authorize('delete', $major);

        $major->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Major::class,
            $major->id
        );

        return redirect()->route('master.majors.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}
