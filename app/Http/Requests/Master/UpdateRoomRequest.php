<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roomId = $this->route('room')?->id ?? $this->route('room');

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('rooms', 'name')->ignore($roomId)],
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('rooms', 'code')->ignore($roomId)],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'building' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama ruangan sudah digunakan.',
            'code.unique' => 'Kode ruangan sudah digunakan.',
        ];
    }
}
