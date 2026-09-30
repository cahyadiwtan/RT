<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isPengurus();
    }

    public function rules(): array
    {
        return [
            'house_id' => ['required', 'exists:houses,id'],
            'nik' => ['required', 'string', 'digits:16', 'unique:residents,nik'],
            'nomor_ktp' => ['nullable', 'string', 'digits:16'],
            'nomor_kk' => ['nullable', 'string', 'digits:16'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'tanggal_tinggal' => ['nullable', 'date'],
            'tanggal_kematian' => ['nullable', 'date'],
            'tanggal_pindah' => ['nullable', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'nomor_telepon' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'status_warga' => ['required', 'in:aktif,pindah,meninggal,tidak_aktif'],
            'kewarganegaraan' => ['nullable', 'in:WNI,WNA'],
            'domisili_asal' => ['nullable', 'in:DD,LD'],
            'hubungan_dalam_keluarga' => ['required', 'in:kepala_keluarga,istri,anak,famili_lain,lainnya'],
            'is_verified' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
