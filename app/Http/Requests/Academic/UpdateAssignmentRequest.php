<?php

namespace App\Http\Requests\Academic;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssignmentRequest extends FormRequest
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
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'class_id' => ['sometimes', 'exists:classes,id'],
            'teacher_id' => ['sometimes', 'exists:teachers,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['sometimes', 'string', 'max:100'],
            'deadline' => ['sometimes', 'date', 'after:now'],
            'max_score' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'teacher_id.exists' => 'Guru tidak ditemukan.',
        ];
    }
}
