<?php

namespace App\Http\Requests\Announcement;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
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
            'title.required' => 'Judul pengumuman wajib diisi.',
            'content.required' => 'Isi pengumuman wajib diisi.',
            'target_roles.*.in' => 'Target role tidak valid.',
            'target_class_ids.*.exists' => 'Kelas tidak ditemukan.',
            'expires_at.after' => 'Tanggal kadaluarsa harus setelah tanggal terbit.',
        ];
    }
}
