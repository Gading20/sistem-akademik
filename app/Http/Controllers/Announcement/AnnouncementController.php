<?php

namespace App\Http\Controllers\Announcement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Announcement\StoreAnnouncementRequest;
use App\Http\Requests\Announcement\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Announcement::class);

        $search = $request->input('search');

        $announcements = Announcement::with('author')
            ->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15);

        return view('announcements.index', compact('announcements', 'search'));
    }

    public function create(): View
    {
        $this->authorize('create', Announcement::class);

        return view('announcements.create');
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $this->authorize('create', Announcement::class);

        $data = $request->validated();
        $data['author_id'] = Auth::id();

        if (!isset($data['published_at']) && ($data['is_published'] ?? false)) {
            $data['published_at'] = now();
        }

        $announcement = Announcement::create($data);

        $this->auditLogService->log(
            Auth::user(),
            'created',
            Announcement::class,
            $announcement->id,
            null,
            $announcement->toArray()
        );

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(Announcement $announcement): View
    {
        $this->authorize('update', $announcement);

        return view('announcements.edit', compact('announcement'));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $this->authorize('update', $announcement);

        $oldData = $announcement->toArray();
        $announcement->update($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            Announcement::class,
            $announcement->id,
            $oldData,
            $announcement->fresh()->toArray()
        );

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $announcement->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Announcement::class,
            $announcement->id
        );

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
