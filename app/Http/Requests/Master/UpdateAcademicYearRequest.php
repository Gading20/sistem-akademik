<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $yearId = $this->route('academicYear')?->id ?? $this->route('academic_year');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('academic_years', 'name')->ignore($yearId)],
            'start_year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'end_year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama tahun ajaran sudah digunakan.',
            'start_year.integer' => 'Tahun mulai harus berupa angka.',
            'start_year.min' => 'Tahun mulai minimal 2000.',
            'start_year.max' => 'Tahun mulai maksimal 2100.',
            'end_year.integer' => 'Tahun selesai harus berupa angka.',
            'end_year.min' => 'Tahun selesai minimal 2000.',
            'end_year.max' => 'Tahun selesai maksimal 2100.',
        ];
    }
}
