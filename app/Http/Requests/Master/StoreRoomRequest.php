<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:rooms,name'],
            'code' => ['required', 'string', 'max:50', 'unique:rooms,code'],
            'capacity' => ['required', 'integer', 'min:1'],
            'building' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama ruangan wajib diisi.',
            'name.unique' => 'Nama ruangan sudah digunakan.',
            'code.required' => 'Kode ruangan wajib diisi.',
            'code.unique' => 'Kode ruangan sudah digunakan.',
            'capacity.required' => 'Kapasitas ruangan wajib diisi.',
            'capacity.integer' => 'Kapasitas harus berupa angka.',
            'capacity.min' => 'Kapasitas minimal 1.',
        ];
    }
}
