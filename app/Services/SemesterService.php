<?php

namespace App\Services;

use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class SemesterService
{
    public function create(array $data): Semester
    {
        $data['is_active'] = $data['is_active'] ?? false;

        if ($data['is_active']) {
            return DB::transaction(function () use ($data) {
                Semester::where('academic_year_id', $data['academic_year_id'])
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                return Semester::create($data);
            });
        }

        return Semester::create($data);
    }

    public function update(Semester $semester, array $data): Semester
    {
        if (isset($data['is_active']) && $data['is_active'] && !$semester->is_active) {
            return DB::transaction(function () use ($semester, $data) {
                Semester::where('academic_year_id', $semester->academic_year_id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                $semester->update($data);

                return $semester->fresh();
            });
        }

        $semester->update($data);

        return $semester->fresh();
    }

    public function activate(Semester $semester): Semester
    {
        return DB::transaction(function () use ($semester) {
            Semester::where('academic_year_id', $semester->academic_year_id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $semester->update(['is_active' => true]);

            return $semester->fresh();
        });
    }

    public function getActive(): ?Semester
    {
        return Semester::with('academicYear')->active()->first();
    }

    public function getByAcademicYear(int $academicYearId): \Illuminate\Database\Eloquent\Collection
    {
        return Semester::where('academic_year_id', $academicYearId)
            ->orderBy('start_date')
            ->get();
    }
}
