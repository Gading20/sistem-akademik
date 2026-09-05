<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teaching_assignment_id' => ['sometimes', 'exists:teaching_assignments,id'],
            'day' => ['sometimes', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', 'after:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'teaching_assignment_id.exists' => 'Penugasan mengajar tidak ditemukan.',
            'day.in' => 'Hari tidak valid.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}
