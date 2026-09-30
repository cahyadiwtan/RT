<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isPengurus();
    }

    public function rules(): array
    {
        return [
            'block' => ['required', 'string', 'max:10'],
            'house_number' => ['required', 'string', 'max:10', Rule::unique('houses')->where(function ($query) {
                return $query->where('block', $this->block);
            })],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:ditempati,kosong,renovasi'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'house_number.unique' => 'Kombinasi Blok dan Nomor Rumah ini sudah terdaftar.',
        ];
    }
}
