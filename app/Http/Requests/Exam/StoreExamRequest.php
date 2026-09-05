<?php

namespace App\Http\Requests\Exam;

use App\Enums\ExamTypeEnum;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
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
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', Rule::in(array_column(ExamTypeEnum::cases(), 'value'))],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'duration_minutes' => ['required', 'integer', 'min:5'],
            'attempt_limit' => ['sometimes', 'integer', 'min:1'],
            'random_question' => ['sometimes', 'boolean'],
            'random_option' => ['sometimes', 'boolean'],
            'shuffle_options' => ['sometimes', 'boolean'],
            'show_result' => ['sometimes', 'boolean'],
            'show_answer_after_submit' => ['sometimes', 'boolean'],
            'passing_score' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'status' => ['sometimes', 'string', 'in:draft,published,active,completed,archived'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['exists:classes,id'],
            'questions' => ['nullable', 'array'],
            'questions.*' => ['integer', 'distinct', 'exists:questions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'teacher_id.required' => 'Guru wajib dipilih.',
            'teacher_id.exists' => 'Guru tidak ditemukan.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun ajaran tidak ditemukan.',
            'semester_id.required' => 'Semester wajib dipilih.',
            'semester_id.exists' => 'Semester tidak ditemukan.',
            'title.required' => 'Judul ujian wajib diisi.',
            'type.required' => 'Jenis ujian wajib dipilih.',
            'type.in' => 'Jenis ujian tidak valid.',
            'start_at.required' => 'Waktu mulai wajib diisi.',
            'end_at.required' => 'Waktu selesai wajib diisi.',
            'end_at.after' => 'Waktu selesai harus setelah waktu mulai.',
            'duration_minutes.required' => 'Durasi ujian wajib diisi.',
            'duration_minutes.integer' => 'Durasi ujian harus berupa angka.',
            'duration_minutes.min' => 'Durasi ujian minimal 5 menit.',
            'class_ids.required' => 'Kelas tujuan wajib dipilih.',
            'class_ids.min' => 'Minimal satu kelas harus dipilih.',
            'class_ids.*.exists' => 'Kelas tidak ditemukan.',
            'questions.*.integer' => 'Soal yang dipilih tidak valid.',
            'questions.*.distinct' => 'Soal tidak boleh dipilih lebih dari satu kali.',
            'questions.*.exists' => 'Soal tidak ditemukan.',
        ];
    }
}
