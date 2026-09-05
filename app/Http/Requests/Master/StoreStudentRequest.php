<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'nisn' => ['required', 'string', 'max:20', 'unique:students,nisn'],
            'nis' => ['required', 'string', 'max:20', 'unique:students,nis'],
            'class_id' => ['required', 'exists:classes,id'],
            'parent_id' => ['nullable', 'exists:parents,id'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'string', 'in:male,female'],
            'religion' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'admission_date' => ['required', 'date'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama siswa wajib diisi.',
            'nisn.required' => 'NISN wajib diisi.',
            'nisn.unique' => 'NISN sudah digunakan.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan.',
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Jenis kelamin harus male atau female.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'admission_date.required' => 'Tanggal masuk wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ];
    }
}
