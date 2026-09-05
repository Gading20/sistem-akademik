<?php

namespace App\Http\Requests\Academic;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $user = $this->user();

        if ($user && $user->hasRole(RoleEnum::GURU->value) && $user->teacher) {
            $this->merge([
                'teacher_id' => $user->teacher->id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['sometimes', 'exists:teachers,id'],
            'class_id' => ['sometimes', 'exists:classes,id'],
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'date' => ['sometimes', 'date'],
            'material' => ['sometimes', 'string'],
            'learning_objectives' => ['sometimes', 'string'],
            'activities' => ['sometimes', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.exists' => 'Guru tidak ditemukan.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'date.date' => 'Format tanggal tidak valid.',
        ];
    }
}
