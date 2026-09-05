<?php

namespace App\Services;

use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function record(array $data): Attendance
    {
        $existing = Attendance::where('student_id', $data['student_id'])
            ->where('schedule_id', $data['schedule_id'])
            ->whereDate('date', $data['date'])
            ->first();

        if ($existing) {
            $existing->update($data);
            return $existing->fresh();
        }

        return Attendance::create($data);
    }

    public function bulkRecord(array $attendances): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        DB::transaction(function () use ($attendances, &$results) {
            foreach ($attendances as $index => $data) {
                try {
                    $this->record($data);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'student_id' => $data['student_id'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }
        });

        return $results;
    }

    public function getStudentAttendance(int $studentId, int $academicYearId, int $semesterId): \Illuminate\Database\Eloquent\Collection
    {
        return Attendance::with(['schedule.teachingAssignment.subject'])
            ->where('student_id', $studentId)
            ->whereHas('schedule.teachingAssignment', function ($q) use ($academicYearId, $semesterId) {
                $q->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId);
            })
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getStudentAttendancePaginated(int $studentId, int $academicYearId, int $semesterId, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Attendance::with(['schedule.teachingAssignment.subject'])
            ->where('student_id', $studentId)
            ->whereHas('schedule.teachingAssignment', function ($q) use ($academicYearId, $semesterId) {
                $q->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId);
            })
            ->orderBy('date', 'desc')
            ->paginate($perPage);
    }

    public function getClassAttendance(int $classId, string $date): \Illuminate\Database\Eloquent\Collection
    {
        return Attendance::with(['student.user', 'schedule.teachingAssignment.subject'])
            ->whereHas('schedule.teachingAssignment', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })
            ->whereDate('date', $date)
            ->get();
    }

    public function getAttendanceSummary(int $studentId, int $academicYearId, int $semesterId): array
    {
        $attendances = $this->getStudentAttendance($studentId, $academicYearId, $semesterId);

        return [
            'total' => $attendances->count(),
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'alpa' => $attendances->where('status', 'alpa')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'percentage' => $attendances->isNotEmpty()
                ? round($attendances->where('status', 'hadir')->count() / $attendances->count() * 100, 2)
                : 0,
        ];
    }
}
