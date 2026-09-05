<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'target_roles' => ['nullable', 'array'],
            'target_roles.*' => ['string', 'in:super_admin,admin_sekolah,kepala_sekolah,wakil_kepala_sekolah,guru,wali_kelas,siswa,orang_tua'],
            'target_class_ids' => ['nullable', 'array'],
            'target_class_ids.*' => ['exists:classes,id'],
            'is_published' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:published_at'],
            'attachment_path' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_roles.*.in' => 'Target role tidak valid.',
            'target_class_ids.*.exists' => 'Kelas tidak ditemukan.',
            'expires_at.after' => 'Tanggal kadaluarsa harus setelah tanggal terbit.',
        ];
    }
}
