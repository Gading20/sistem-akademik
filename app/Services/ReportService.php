<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(
        protected GradeService $gradeService,
        protected AttendanceService $attendanceService,
    ) {}

    public function generateReportCard(
        Student $student,
        ClassRoom $class,
        AcademicYear $year,
        Semester $semester,
    ): ReportCard {
        $grades = Grade::where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->where('academic_year_id', $year->id)
            ->where('semester_id', $semester->id)
            ->get();

        $averageScore = $grades->isNotEmpty()
            ? round($grades->avg('final_score'), 2)
            : 0;

        $letterGrade = $this->determineGradeLetter($averageScore);

        $ranking = $this->calculateRanking($class->id, $year->id, $semester->id, $student->id);

        $attendanceSummary = $this->attendanceService->getAttendanceSummary($student->id, $year->id, $semester->id);

        return ReportCard::updateOrCreate(
            [
                'student_id' => $student->id,
                'class_id' => $class->id,
                'academic_year_id' => $year->id,
                'semester_id' => $semester->id,
            ],
            [
                'total_score' => $averageScore,
                'average_score' => $averageScore,
                'letter_grade' => $letterGrade,
                'rank' => $ranking,
                'attendance_summary' => json_encode($attendanceSummary),
            ]
        );
    }

    public function getStudentReport(int $studentId, int $academicYearId, int $semesterId): array
    {
        $student = Student::with(['user', 'classRoom'])->findOrFail($studentId);

        $grades = $this->gradeService->getStudentGrades($studentId, $academicYearId, $semesterId);
        $attendance = $this->attendanceService->getAttendanceSummary($studentId, $academicYearId, $semesterId);

        $reportCard = ReportCard::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->first();

        return [
            'student' => $student,
            'grades' => $grades,
            'attendance' => $attendance,
            'report_card' => $reportCard,
            'average_score' => $reportCard?->average_score ?? $grades->avg('final_score'),
            'letter_grade' => $reportCard?->letter_grade ?? $this->determineGradeLetter($grades->avg('final_score') ?? 0),
            'rank' => $reportCard?->rank,
        ];
    }

    public function getClassRanking(int $classId, int $academicYearId, int $semesterId): \Illuminate\Database\Eloquent\Collection
    {
        $reportCards = ReportCard::with(['student.user'])
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->orderBy('average_score', 'desc')
            ->get();

        $rank = 1;
        foreach ($reportCards as $reportCard) {
            $reportCard->update(['rank' => $rank]);
            $rank++;
        }

        return ReportCard::with(['student.user'])
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->orderBy('rank', 'asc')
            ->get();
    }

    public function finalizeReportCard(int $studentId, int $classId, int $academicYearId, int $semesterId, int $userId): ReportCard
    {
        $reportCard = ReportCard::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->first();

        if (!$reportCard) {
            throw new \Exception('Laporan nilai belum tersedia.');
        }

        $reportCard->update([
            'is_finalized' => true,
            'finalized_by' => $userId,
            'finalized_at' => now(),
        ]);

        return $reportCard->fresh();
    }

    private function calculateRanking(int $classId, int $academicYearId, int $semesterId, int $studentId): int
    {
        $ranking = ReportCard::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('average_score', '>', function ($query) use ($studentId, $classId, $academicYearId, $semesterId) {
                $query->select('average_score')
                    ->from('report_cards')
                    ->where('student_id', $studentId)
                    ->where('class_id', $classId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId)
                    ->limit(1);
            })
            ->count() + 1;

        return $ranking;
    }

    private function determineGradeLetter(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'E',
        };
    }
}
