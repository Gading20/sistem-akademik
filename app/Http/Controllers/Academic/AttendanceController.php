<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\StoreAttendanceRequest;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\Student;
use App\Services\AttendanceService;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);

        $classId = $request->input('class_id');
        $scheduleId = $request->input('schedule_id');
        $date = $request->input('date', now()->toDateString());

        $attendances = collect();
        $students = collect();
        $existingAttendances = collect();
        $isAlreadyRecorded = false;

        if ($classId) {
            $attendances = $this->attendanceService->getClassAttendance($classId, $date);
            $students = Student::with('user')
                ->active()
                ->byClass($classId)
                ->get();
        }

        $classes = ClassRoom::active()->orderBy('name')->get();
        $dayName = Carbon::parse($date)->locale('en')->dayName;

        $schedules = Schedule::with(['teachingAssignment.teacher.user', 'teachingAssignment.classRoom', 'teachingAssignment.subject'])
            ->when($classId, fn ($q) => $q->whereHas('teachingAssignment', fn ($q2) => $q2->where('class_id', $classId)))
            ->when($date, fn ($q) => $q->where('day', strtolower($dayName)))
            ->orderBy('start_time')
            ->get();

        // Cek apakah absensi untuk schedule dan tanggal ini sudah dicatat
        if ($scheduleId && $date) {
            $existingAttendances = Attendance::where('schedule_id', $scheduleId)
                ->where('date', $date)
                ->with('student.user')
                ->get()
                ->keyBy('student_id');

            $isAlreadyRecorded = $existingAttendances->isNotEmpty();
        }

        return view('academic.attendance.index', compact(
            'attendances',
            'students',
            'classes',
            'schedules',
            'classId',
            'scheduleId',
            'date',
            'existingAttendances',
            'isAlreadyRecorded'
        ));
    }

    public function record(StoreAttendanceRequest $request): RedirectResponse
    {
        $this->authorize('create', Attendance::class);

        $data = $request->validated();
        $data['recorded_by'] = Auth::id();

        $attendance = $this->attendanceService->record($data);

        $this->auditLogService->log(
            Auth::user(),
            'recorded',
            Attendance::class,
            $attendance->id,
            null,
            $attendance->toArray()
        );

        return back()->with('success', 'Absensi berhasil dicatat.');
    }

    public function bulkRecord(Request $request): RedirectResponse
    {
        $this->authorize('create', Attendance::class);

        $globalScheduleId = $request->input('schedule_id');

        if ($globalScheduleId && is_array($request->attendances)) {
            $mergedAttendances = collect($request->attendances)->map(function ($item) use ($globalScheduleId) {
                if (empty($item['schedule_id'])) {
                    $item['schedule_id'] = $globalScheduleId;
                }

                return $item;
            })->toArray();

            $request->merge(['attendances' => $mergedAttendances]);
        }

        $request->validate([
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'exists:students,id'],
            'attendances.*.schedule_id' => ['required', 'exists:schedules,id'],
            'attendances.*.status' => ['required', 'string', 'in:hadir,sakit,izin,alpa,terlambat'],
            'attendances.*.date' => ['required', 'date'],
            'attendances.*.note' => ['nullable', 'string'],
        ]);

        $attendancesData = collect($request->attendances)->map(fn ($item) => array_merge($item, ['recorded_by' => Auth::id()]))->toArray();

        $results = $this->attendanceService->bulkRecord($attendancesData);

        if ($results['failed'] > 0) {
            return back()->with(['import_results' => $results]);
        }

        return back()->with('success', "Berhasil mencatat {$results['success']} absensi.");
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $this->authorize('create', Attendance::class);

        $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'date' => ['required', 'date'],
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.attendance_id' => ['required', 'exists:attendances,id'],
            'attendances.*.status' => ['required', 'string', 'in:hadir,sakit,izin,alpa,terlambat'],
            'attendances.*.note' => ['nullable', 'string'],
        ]);

        $updated = 0;
        foreach ($request->attendances as $data) {
            $attendance = Attendance::find($data['attendance_id']);
            if ($attendance) {
                $attendance->update([
                    'status' => $data['status'],
                    'note' => $data['note'] ?? null,
                    'recorded_by' => Auth::id(),
                ]);
                $updated++;

                $this->auditLogService->log(
                    Auth::user(),
                    'updated',
                    Attendance::class,
                    $attendance->id,
                    ['status' => $attendance->getOriginal('status')],
                    $attendance->toArray()
                );
            }
        }

        return back()->with('success', "Berhasil mengupdate {$updated} absensi.");
    }

    public function show(Attendance $attendance): View
    {
        $this->authorize('view', $attendance);

        $attendance->load(['student.user', 'schedule.teachingAssignment.subject', 'recorder']);

        $attendances = collect([$attendance]);
        $summary = null;

        return view('academic.attendance.student', [
            'student' => $attendance->student,
            'attendances' => $attendances,
            'summary' => null,
        ]);
    }

    public function byStudent(Request $request, Student $student): View
    {
        $this->authorize('viewAny', Attendance::class);

        $academicYearId = $request->input('academic_year_id');
        $semesterId = $request->input('semester_id');

        $attendances = collect();
        $summary = null;

        if ($academicYearId && $semesterId) {
            $attendances = $this->attendanceService->getStudentAttendancePaginated($student->id, $academicYearId, $semesterId);
            $summary = $this->attendanceService->getAttendanceSummary($student->id, $academicYearId, $semesterId);
        }

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('start_date')->get();

        return view('academic.attendance.student', compact('student', 'attendances', 'summary', 'academicYears', 'semesters', 'academicYearId', 'semesterId'));
    }

    /**
     * Riwayat absensi untuk siswa yang sedang login (dirinya sendiri).
     */
    public function myHistory(Request $request): View
    {
        $this->authorize('viewOwn', Attendance::class);

        $student = Auth::user()->student;

        if (! $student) {
            abort(403, 'Data siswa tidak ditemukan untuk akun ini.');
        }

        $activeYear = AcademicYear::active()->first();
        $activeSemester = Semester::active()->first();

        $academicYearId = $request->input('academic_year_id', $activeYear?->id);
        $semesterId = $request->input('semester_id', $activeSemester?->id);

        $attendances = collect();
        $summary = null;

        if ($academicYearId && $semesterId) {
            $attendances = $this->attendanceService->getStudentAttendancePaginated($student->id, $academicYearId, $semesterId);
            $summary = $this->attendanceService->getAttendanceSummary($student->id, $academicYearId, $semesterId);
        }

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $semesters = Semester::orderByDesc('start_date')->get();

        return view('academic.attendance.student', compact('student', 'attendances', 'summary', 'academicYears', 'semesters', 'academicYearId', 'semesterId'));
    }
}
