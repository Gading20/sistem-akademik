<?php

namespace App\Http\Requests\Exam;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionBankRequest extends FormRequest
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
            'teacher_id' => ['sometimes', 'exists:teachers,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'teacher_id.exists' => 'Guru tidak ditemukan.',
        ];
    }
}
