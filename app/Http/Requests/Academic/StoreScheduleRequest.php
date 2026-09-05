<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'day' => ['required', 'string', 'in:senin,selasa,rabu,kamis,jumat,sabtu'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'teaching_assignment_id.required' => 'Penugasan mengajar wajib dipilih.',
            'teaching_assignment_id.exists' => 'Penugasan mengajar tidak ditemukan.',
            'day.required' => 'Hari wajib dipilih.',
            'day.in' => 'Hari tidak valid.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'start_time.date_format' => 'Format jam mulai tidak valid (HH:mm).',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}
