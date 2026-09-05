<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreScheduleRequest;
use App\Http\Requests\Academic\UpdateScheduleRequest;
use App\Models\ClassRoom;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\AuditLogService;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService,
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Schedule::class);

        $day = $request->input('day') ? strtolower($request->input('day')) : null;
        $teacherId = $request->input('teacher_id');
        $classId = $request->input('class_id');

        $schedules = Schedule::with([
            'teachingAssignment.subject',
            'teachingAssignment.teacher.user',
            'teachingAssignment.classRoom',
            'room',
        ])
            ->when($day, fn ($q) => $q->where('day', $day))
            ->when($teacherId, fn ($q) => $q->whereHas('teachingAssignment', fn ($q2) => $q2->where('teacher_id', $teacherId)))
            ->when($classId, fn ($q) => $q->whereHas('teachingAssignment', fn ($q2) => $q2->where('class_id', $classId)))
            ->orderBy('day')
            ->orderBy('start_time')
            ->paginate(20);

        $teachingAssignments = TeachingAssignment::with(['teacher.user', 'classRoom', 'subject'])
            ->get();
        $rooms = Room::active()->orderBy('name')->get();
        $classes = ClassRoom::active()->orderBy('name')->get();
        $teachers = Teacher::with('user')->active()->get();

        return view('academic.schedules.index', compact('schedules', 'teachingAssignments', 'rooms', 'classes', 'teachers', 'day', 'teacherId', 'classId'));
    }

    public function create(): View
    {
        $this->authorize('create', Schedule::class);

        $teachingAssignments = TeachingAssignment::with(['teacher.user', 'classRoom', 'subject'])
            ->get();
        $teachers = $teachingAssignments->pluck('teacher')->unique('id')->values();
        $rooms = Room::active()->orderBy('name')->get();

        $taJson = $teachingAssignments->map(fn ($ta) => [
            'id' => $ta->id,
            'teacher_id' => $ta->teacher_id,
            'class_id' => $ta->class_id,
            'class_name' => $ta->classRoom->name ?? '-',
            'subject_name' => $ta->subject->name ?? '-',
        ])->toJson();

        return view('academic.schedules.create', compact('teachingAssignments', 'teachers', 'rooms', 'taJson'));
    }

    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $this->authorize('create', Schedule::class);

        try {
            $schedule = $this->scheduleService->create($request->validated());

            $this->auditLogService->log(
                Auth::user(),
                'created',
                Schedule::class,
                $schedule->id,
                null,
                $schedule->toArray()
            );

            return redirect()->route('academic.schedules.index')
                ->with('success', 'Jadwal berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withErrors(['schedule' => $e->getMessage()])->withInput();
        }
    }

    public function show(Schedule $schedule): View
    {
        $this->authorize('view', $schedule);

        $schedule->load([
            'teachingAssignment.subject',
            'teachingAssignment.teacher.user',
            'teachingAssignment.classRoom',
            'room',
        ]);

        return view('academic.schedules.show', compact('schedule'));
    }

    public function edit(Schedule $schedule): View
    {
        $this->authorize('update', $schedule);

        $teachingAssignments = TeachingAssignment::with(['teacher.user', 'classRoom', 'subject'])
            ->get();
        $teachers = $teachingAssignments->pluck('teacher')->unique('id')->values();
        $rooms = Room::active()->orderBy('name')->get();

        $taJson = $teachingAssignments->map(fn ($ta) => [
            'id' => $ta->id,
            'teacher_id' => $ta->teacher_id,
            'class_id' => $ta->class_id,
            'class_name' => $ta->classRoom->name ?? '-',
            'subject_name' => $ta->subject->name ?? '-',
        ])->toJson();

        return view('academic.schedules.edit', compact('schedule', 'teachingAssignments', 'teachers', 'rooms', 'taJson'));
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        $this->authorize('update', $schedule);

        try {
            $oldData = $schedule->toArray();
            $schedule = $this->scheduleService->update($schedule, $request->validated());

            $this->auditLogService->log(
                Auth::user(),
                'updated',
                Schedule::class,
                $schedule->id,
                $oldData,
                $schedule->toArray()
            );

            return redirect()->route('academic.schedules.index')
                ->with('success', 'Jadwal berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['schedule' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $this->authorize('delete', $schedule);

        $schedule->delete();

        $this->auditLogService->log(
            Auth::user(),
            'deleted',
            Schedule::class,
            $schedule->id
        );

        return redirect()->route('academic.schedules.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }
}
