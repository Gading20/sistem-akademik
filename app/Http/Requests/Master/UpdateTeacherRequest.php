<?php

namespace App\Http\Requests\Master;

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
        $teacherId = $this->route('teacher')?->id ?? $this->route('teacher');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($this->user()?->id)],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('teachers', 'nip')->ignore($teacherId)],
            'nuptk' => ['nullable', 'string', 'max:30', Rule::unique('teachers', 'nuptk')->ignore($teacherId)],
            'subject_id' => ['nullable', 'exists:subjects,id'],
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
            'contract_end_date.after' => 'Tanggal akhir kontrak harus setelah tanggal masuk.',
        ];
    }
}
