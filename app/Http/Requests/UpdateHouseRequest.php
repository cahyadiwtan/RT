<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isPengurus();
    }

    public function rules(): array
    {
        $houseId = $this->route('house') ? $this->route('house')->id : null;

        return [
            'block' => ['required', 'string', 'max:10'],
            'house_number' => ['required', 'string', 'max:10', Rule::unique('houses')->where(function ($query) {
                return $query->where('block', $this->block);
            })->ignore($houseId)],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:ditempati,kosong,renovasi'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
