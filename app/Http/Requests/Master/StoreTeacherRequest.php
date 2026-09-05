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
            'subject_id' => ['nullable', 'exists:subjects,id'],
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
            'join_date.required' => 'Tanggal masuk wajib diisi.',
            'contract_end_date.after' => 'Tanggal akhir kontrak harus setelah tanggal masuk.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ];
    }
}
