<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'nip' => ['nullable', 'string', 'max:30', 'unique:teachers,nip'],
            'nuptk' => ['nullable', 'string', 'max:30', 'unique:teachers,nuptk'],
            'gender' => ['required', 'string', 'in:male,female'],
            'nik' => ['nullable', 'string', 'max:30'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'qualification' => ['nullable', 'string', 'max:100'],
            'specialization' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'employment_status' => ['nullable', 'string', 'in:active,inactive,retired'],
            'join_date' => ['required', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after:join_date'],
            'is_active' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama guru wajib diisi.',
            'nip.unique' => 'NIP sudah digunakan.',
            'nuptk.unique' => 'NUPTK sudah digunakan.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin harus laki-laki atau perempuan.',
            'date_of_birth.before' => 'Tanggal lahir harus sebelum hari ini.',
            'employment_status.in' => 'Status kepegawaian tidak valid.',
            'join_date.required' => 'Tanggal masuk wajib diisi.',
            'contract_end_date.after' => 'Tanggal akhir kontrak harus setelah tanggal masuk.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ];
    }
}
