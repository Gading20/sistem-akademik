<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreSubjectRequest;
use App\Http\Requests\Master\UpdateSubjectRequest;
use App\Models\Major;
use App\Models\Subject;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Subject::class);

        $search = $request->input('search');
        $majorId = $request->input('major_id');

        $subjects = Subject::with('major')
            ->when($majorId, fn ($q) => $q->where('major_id', $majorId))
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15);

        $majors = Major::orderBy('name')->get();

        return view('master.subjects.index', compact('subjects', 'majors', 'search', 'majorId'));
    }

    public function create(): View
    {
        $this->authorize('create', Subject::class);

        $majors = Major::orderBy('name')->get();

        return view('master.subjects.create', compact('majors'));
    }

    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $this->authorize('create', Subject::class);

        $subject = Subject::create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            Subject::class,
            $subject->id,
            null,
            $subject->toArray()
        );

        return redirect()->route('master.subjects.index')
            ->with('success', 'Mata pelajaran berhasil dibuat.');
    }

    public function edit(Subject $subject): View
    {
        $this->authorize('update', $subject);

        $majors = Major::orderBy('name')->get();

        return view('master.subjects.edit', compact('subject', 'majors'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $this->authorize('update', $subject);

        $oldData = $subject->toArray();
        $subject->update($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            Subject::class,
            $subject->id,
            $oldData,
            $subject->fresh()->toArray()
        );

        return redirect()->route('master.subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorize('delete', $subject);

        $subject->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Subject::class,
            $subject->id
        );

        return redirect()->route('master.subjects.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
