<?php

namespace App\Http\Requests\Academic;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if ($user && $user->hasRole(RoleEnum::GURU->value)) {
            $this->merge([
                'teacher_id' => $user->teacher?->id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['sometimes', 'string', 'max:100'],
            'deadline' => ['required', 'date', 'after:now'],
            'max_score' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'teacher_id.required' => 'Guru wajib dipilih.',
            'teacher_id.exists' => 'Guru tidak ditemukan.',
            'title.required' => 'Judul tugas wajib diisi.',
            'description.required' => 'Deskripsi tugas wajib diisi.',
            'deadline.required' => 'Batas pengumpulan wajib diisi.',
            'deadline.after' => 'Batas pengumpulan harus setelah waktu sekarang.',
        ];
    }
}
