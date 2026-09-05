<?php

namespace App\Http\Requests\Exam;

use App\Enums\QuestionTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_bank_id' => ['sometimes', 'exists:question_banks,id'],
            'type' => ['sometimes', 'string', Rule::in(array_column(QuestionTypeEnum::cases(), 'value'))],
            'difficulty' => ['sometimes', 'string', 'in:easy,medium,hard'],
            'question' => ['sometimes', 'string'],
            'explanation' => ['nullable', 'string'],
            'points' => ['sometimes', 'numeric', 'min:0.01'],
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
            'question_bank_id.exists' => 'Bank soal tidak ditemukan.',
            'type.in' => 'Jenis soal tidak valid.',
            'difficulty.in' => 'Tingkat kesulitan tidak valid.',
            'points.numeric' => 'Bobot soal harus berupa angka.',
            'points.min' => 'Bobot soal minimal 0.01.',
        ];
    }
}
