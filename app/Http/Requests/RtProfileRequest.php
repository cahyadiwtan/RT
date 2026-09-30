<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RtProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'pengurus';
    }

    public function rules(): array
    {
        return [
            'rt' => ['required', 'string', 'max:10'],
            'rw' => ['required', 'string', 'max:10'],
            'kelurahan' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'provinsi' => ['required', 'string', 'max:100'],
            'ketua_rt' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'rt.required' => 'Nomor RT wajib diisi.',
            'rw.required' => 'Nomor RW wajib diisi.',
            'kelurahan.required' => 'Kelurahan wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'kota.required' => 'Kota wajib diisi.',
            'provinsi.required' => 'Provinsi wajib diisi.',
        ];
    }
}
