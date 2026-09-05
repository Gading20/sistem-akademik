<?php

namespace App\Http\Requests\Academic;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreJournalRequest extends FormRequest
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
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'date' => ['required', 'date'],
            'material' => ['required', 'string'],
            'learning_objectives' => ['required', 'string'],
            'activities' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.required' => 'Guru wajib dipilih.',
            'teacher_id.exists' => 'Guru tidak ditemukan.',
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'material.required' => 'Materi wajib diisi.',
            'learning_objectives.required' => 'Tujuan pembelajaran wajib diisi.',
            'activities.required' => 'Kegiatan wajib diisi.',
        ];
    }
}
