<?php

namespace App\Services;

use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function create(array $data): Schedule
    {
        if ($this->checkConflict(
            $data['teaching_assignment_id'],
            $data['day'],
            $data['start_time'],
            $data['end_time']
        )) {
            throw new \Exception('Jadwal bentrok dengan jadwal yang sudah ada.');
        }

        return Schedule::create($data);
    }

    public function update(Schedule $schedule, array $data): Schedule
    {
        $teachingAssignmentId = $data['teaching_assignment_id'] ?? $schedule->teaching_assignment_id;
        $day = $data['day'] ?? $schedule->day;
        $startTime = $data['start_time'] ?? $schedule->start_time;
        $endTime = $data['end_time'] ?? $schedule->end_time;

        if ($this->checkConflict($teachingAssignmentId, $day, $startTime, $endTime, $schedule->id)) {
            throw new \Exception('Jadwal bentrok dengan jadwal yang sudah ada.');
        }

        $schedule->update($data);

        return $schedule->fresh();
    }

    public function checkConflict(
        int $teachingAssignmentId,
        string $day,
        string $startTime,
        string $endTime,
        ?int $excludeId = null
    ): bool {
        $query = Schedule::where('day', $day)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q2) use ($startTime, $endTime) {
                        $q2->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $conflictWithTeacher = (clone $query)
            ->where('teaching_assignment_id', $teachingAssignmentId)
            ->exists();

        $conflictWithClass = (clone $query)
            ->whereHas('teachingAssignment', function ($q) use ($teachingAssignmentId) {
                $q->where('id', $teachingAssignmentId);
            })
            ->exists();

        return $conflictWithTeacher || $conflictWithClass;
    }

    public function getByTeacher(int $teacherId): \Illuminate\Database\Eloquent\Collection
    {
        return Schedule::with(['teachingAssignment.subject', 'teachingAssignment.classRoom', 'room'])
            ->whereHas('teachingAssignment', fn ($q) => $q->where('teacher_id', $teacherId))
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }

    public function getByClass(int $classId): \Illuminate\Database\Eloquent\Collection
    {
        return Schedule::with(['teachingAssignment.subject', 'teachingAssignment.teacher.user', 'room'])
            ->whereHas('teachingAssignment', fn ($q) => $q->where('class_id', $classId))
            ->orderBy('day')
            ->orderBy('start_time')
            ->get();
    }
}
