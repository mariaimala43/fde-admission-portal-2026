<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdmissionEditGrantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'institution_id' => 'required|integer|exists:institutions,id',
            'date_from'      => 'required|date|date_format:Y-m-d',
            'date_to'        => 'required|date|date_format:Y-m-d|after_or_equal:date_from',
            'expires_at'     => 'required|date|after:now',
            'reason'         => 'required|string|min:10|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'institution_id.exists'   => 'The selected school does not exist.',
            'date_to.after_or_equal'  => '"Allow Editing To" must be on or after the "From" date.',
            'expires_at.after'        => 'Grant expiry must be in the future.',
            'reason.min'              => 'Reason must be at least 10 characters.',
        ];
    }
}
