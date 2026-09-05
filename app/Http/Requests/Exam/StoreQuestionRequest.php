<?php

namespace App\Http\Requests\Exam;

use App\Enums\QuestionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_bank_id' => ['sometimes', 'exists:question_banks,id'],
            'type' => ['required', 'string', Rule::in(array_column(QuestionTypeEnum::cases(), 'value'))],
            'difficulty' => ['required', 'string', 'in:easy,medium,hard'],
            'question' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'points' => ['required', 'numeric', 'min:0.01'],
            'topic' => ['nullable', 'string', 'max:255'],
            'options' => ['nullable', 'array', 'min:2'],
            'options.*.option_text' => ['required_with:options', 'string'],
            'options.*.is_correct' => ['required_with:options', 'boolean'],
            'options.*.order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'question_bank_id.required' => 'Bank soal wajib dipilih.',
            'question_bank_id.exists' => 'Bank soal tidak ditemukan.',
            'type.required' => 'Jenis soal wajib dipilih.',
            'type.in' => 'Jenis soal tidak valid.',
            'difficulty.required' => 'Tingkat kesulitan wajib dipilih.',
            'difficulty.in' => 'Tingkat kesulitan tidak valid.',
            'question.required' => 'Teks soal wajib diisi.',
            'points.required' => 'Bobot soal wajib diisi.',
            'points.numeric' => 'Bobot soal harus berupa angka.',
            'points.min' => 'Bobot soal minimal 0.01.',
            'options.min' => 'Minimal 2 opsi jawaban.',
            'options.*.option_text.required_with' => 'Teks opsi wajib diisi.',
            'options.*.is_correct.required_with' => 'Status benar/salah wajib diisi.',
        ];
    }
}
