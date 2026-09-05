<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreRoomRequest;
use App\Http\Requests\Master\UpdateRoomRequest;
use App\Models\Room;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Room::class);

        $search = $request->input('search');

        $rooms = Room::when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('building', 'like', "%{$search}%");
        })->latest()->paginate(15);

        return view('master.rooms.index', compact('rooms', 'search'));
    }

    public function create(): View
    {
        $this->authorize('create', Room::class);

        return view('master.rooms.create');
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $this->authorize('create', Room::class);

        $room = Room::create($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'created',
            Room::class,
            $room->id,
            null,
            $room->toArray()
        );

        return redirect()->route('master.rooms.index')
            ->with('success', 'Ruangan berhasil dibuat.');
    }

    public function edit(Room $room): View
    {
        $this->authorize('update', $room);

        return view('master.rooms.edit', compact('room'));
    }

    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $this->authorize('update', $room);

        $oldData = $room->toArray();
        $room->update($request->validated());

        $this->auditLogService->log(
            Auth::user(),
            'updated',
            Room::class,
            $room->id,
            $oldData,
            $room->fresh()->toArray()
        );

        return redirect()->route('master.rooms.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        $room->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Room::class,
            $room->id
        );

        return redirect()->route('master.rooms.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }
}
