<?php

namespace App\Http\Requests\Academic;

use App\Enums\AttendanceStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'schedule_id' => ['required', 'exists:schedules,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(array_column(AttendanceStatusEnum::cases(), 'value'))],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Siswa wajib dipilih.',
            'student_id.exists' => 'Siswa tidak ditemukan.',
            'schedule_id.required' => 'Jadwal wajib dipilih.',
            'schedule_id.exists' => 'Jadwal tidak ditemukan.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'status.required' => 'Status kehadiran wajib dipilih.',
            'status.in' => 'Status kehadiran tidak valid.',
        ];
    }
}
