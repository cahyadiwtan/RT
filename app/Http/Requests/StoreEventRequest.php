<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isPengurus();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after_or_equal:start_at'],
            'registration_deadline' => ['nullable', 'date', 'before_or_equal:start_at'],
            'payment_deadline' => ['nullable', 'date', 'before_or_equal:end_at'],
            'funding_type' => ['required', 'in:free,resident_fee,voluntary,rt_fund,mixed'],
            'required_payment' => ['required_if:funding_type,resident_fee,mixed', 'numeric', 'min:0'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,published'],
            'visibility' => ['required', 'in:private,summary,names_only'],
        ];
    }
}
