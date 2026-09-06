<?php

namespace App\Http\Requests\Master;

use App\Models\Teacher;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');
        $teacherId = $teacher instanceof Teacher ? $teacher->id : $teacher;
        $teacherUserId = $teacher instanceof Teacher ? $teacher->user_id : null;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($teacherUserId)],
            'password' => ['nullable', 'string', 'min:8'],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('teachers', 'nip')->ignore($teacherId)],
            'nuptk' => ['nullable', 'string', 'max:30', Rule::unique('teachers', 'nuptk')->ignore($teacherId)],
            'gender' => ['sometimes', 'string', 'in:male,female'],
            'nik' => ['nullable', 'string', 'max:30'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'qualification' => ['nullable', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'employment_status' => ['nullable', 'string', 'in:active,inactive,retired'],
            'join_date' => ['sometimes', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after:join_date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nip.unique' => 'NIP sudah digunakan.',
            'nuptk.unique' => 'NUPTK sudah digunakan.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'gender.in' => 'Jenis kelamin harus laki-laki atau perempuan.',
            'date_of_birth.before' => 'Tanggal lahir harus sebelum hari ini.',
            'employment_status.in' => 'Status kepegawaian tidak valid.',
            'contract_end_date.after' => 'Tanggal akhir kontrak harus setelah tanggal masuk.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
