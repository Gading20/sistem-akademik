<?php

namespace App\Services;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function create(array $data): AcademicYear
    {
        $data['is_active'] = $data['is_active'] ?? false;

        if ($data['is_active']) {
            return DB::transaction(function () use ($data) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);

                return AcademicYear::create($data);
            });
        }

        return AcademicYear::create($data);
    }

    public function update(AcademicYear $year, array $data): AcademicYear
    {
        if (isset($data['is_active']) && $data['is_active'] && !$year->is_active) {
            return DB::transaction(function () use ($year, $data) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
                $year->update($data);

                return $year->fresh();
            });
        }

        $year->update($data);

        return $year->fresh();
    }

    public function activate(AcademicYear $year): AcademicYear
    {
        return DB::transaction(function () use ($year) {
            AcademicYear::where('is_active', true)->update(['is_active' => false]);
            $year->update(['is_active' => true]);

            return $year->fresh();
        });
    }

    public function deactivate(AcademicYear $year): AcademicYear
    {
        $year->update(['is_active' => false]);

        return $year->fresh();
    }

    public function getActive(): ?AcademicYear
    {
        return AcademicYear::with('semesters')->active()->first();
    }

    public function getCurrent(): ?AcademicYear
    {
        return AcademicYear::with('semesters')->current()->first();
    }
}
