<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id ?? $this->route('student');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($this->user()?->id)],
            'nisn' => ['sometimes', 'string', 'max:20', Rule::unique('students', 'nisn')->ignore($studentId)],
            'nis' => ['sometimes', 'string', 'max:20', Rule::unique('students', 'nis')->ignore($studentId)],
            'class_id' => ['sometimes', 'exists:classes,id'],
            'parent_id' => ['nullable', 'exists:parents,id'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['sometimes', 'string', 'in:male,female'],
            'religion' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['sometimes', 'string', 'in:active,inactive,graduated,transferred,dropped'],
            'admission_date' => ['sometimes', 'date'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nisn.unique' => 'NISN sudah digunakan.',
            'nis.unique' => 'NIS sudah digunakan.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'gender.in' => 'Jenis kelamin harus male atau female.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
