<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAspirationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isPengurus();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:submitted,reviewed,in_progress,resolved,rejected'],
            'comment' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'comment.required' => 'Komentar tindak lanjut wajib diisi.',
        ];
    }
}
