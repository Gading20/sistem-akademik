<?php

namespace App\Http\Requests\Exam;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionBankRequest extends FormRequest
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
            'teacher_id' => ['required', 'exists:teachers,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'teacher_id.required' => 'Guru wajib dipilih.',
            'teacher_id.exists' => 'Guru tidak ditemukan.',
            'name.required' => 'Nama bank soal wajib diisi.',
        ];
    }
}
