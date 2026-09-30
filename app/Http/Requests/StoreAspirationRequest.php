<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAspirationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan user terotentikasi dan memiliki data warga (resident)
        return $this->user() && $this->user()->resident_id !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'in:keamanan,kebersihan,fasilitas,lingkungan,sosial,lainnya'],
            'attachment' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul aspirasi wajib diisi.',
            'title.max' => 'Judul aspirasi maksimal 255 karakter.',
            'description.required' => 'Deskripsi aspirasi wajib diisi.',
            'category.required' => 'Kategori aspirasi wajib dipilih.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
            'attachment.mimes' => 'Lampiran harus berupa file gambar (jpeg, png, jpg) atau PDF.',
            'attachment.max' => 'Ukuran lampiran maksimal adalah 2MB.',
        ];
    }
}
