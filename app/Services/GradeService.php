<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\Grade;
use App\Models\GradingConfig;
use App\Models\Student;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Semester;

class GradeService
{
    public function calculateFinalGrade(
        Student $student,
        Subject $subject,
        ClassRoom $class,
        AcademicYear $year,
        Semester $semester,
    ): ?Grade {
        $config = GradingConfig::where('subject_id', $subject->id)
            ->where('class_id', $class->id)
            ->where('academic_year_id', $year->id)
            ->where('semester_id', $semester->id)
            ->first();

        if (!$config) {
            return null;
        }

        $grade = Grade::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('class_id', $class->id)
            ->where('academic_year_id', $year->id)
            ->where('semester_id', $semester->id)
            ->first();

        if (!$grade) {
            return null;
        }

        $totalWeight = $config->tugas_weight + $config->quiz_weight + $config->uts_weight + $config->uas_weight
            + $config->practical_weight + $config->project_weight;

        if ($totalWeight <= 0) {
            return null;
        }

        $totalScore = ($grade->tugas_score * $config->tugas_weight / $totalWeight)
            + ($grade->quiz_score * $config->quiz_weight / $totalWeight)
            + ($grade->uts_score * $config->uts_weight / $totalWeight)
            + ($grade->uas_score * $config->uas_weight / $totalWeight)
            + ($grade->practical_score * $config->practical_weight / $totalWeight)
            + ($grade->project_score * $config->project_weight / $totalWeight);

        $letterGrade = $this->determineGradeLetter($totalScore);

        $grade->update([
            'final_score' => round($totalScore, 2),
            'final_percentage' => round($totalScore, 2),
            'letter_grade' => $letterGrade,
        ]);

        return $grade->fresh();
    }

    public function getStudentGrades(int $studentId, int $academicYearId, int $semesterId): \Illuminate\Database\Eloquent\Collection
    {
        return Grade::with(['subject', 'classRoom'])
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->get();
    }

    public function getClassGrades(int $classId, int $subjectId, int $academicYearId, int $semesterId): \Illuminate\Database\Eloquent\Collection
    {
        return Grade::with(['student.user'])
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->orderBy('final_score', 'desc')
            ->get();
    }

    public function updateGradeScore(Grade $grade, array $data): Grade
    {
        $grade->update($data);

        return $grade->fresh();
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

    public function getComponents(): \Illuminate\Support\Collection
    {
        return collect([
            (object) ['id' => 'tugas', 'name' => 'Tugas', 'weight' => 30],
            (object) ['id' => 'quiz', 'name' => 'Quiz', 'weight' => 15],
            (object) ['id' => 'uts', 'name' => 'UTS', 'weight' => 20],
            (object) ['id' => 'uas', 'name' => 'UAS', 'weight' => 25],
            (object) ['id' => 'practical', 'name' => 'Praktik', 'weight' => 5],
            (object) ['id' => 'project', 'name' => 'Proyek', 'weight' => 5],
        ]);
    }
}
