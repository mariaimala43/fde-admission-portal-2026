<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdmissionCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id'              => 'required|integer|exists:classes,id',
            'admission_date'        => 'required|date|date_format:Y-m-d',
            'reason'                => 'required|string|min:10|max:1000',
            'new_morning_boys'      => 'required|integer|min:0|max:9999',
            'new_morning_girls'     => 'required|integer|min:0|max:9999',
            'new_evening_boys'      => 'nullable|integer|min:0|max:9999',
            'new_evening_girls'     => 'nullable|integer|min:0|max:9999',
            'new_morning_oosc_boys' => 'nullable|integer|min:0|max:9999',
            'new_morning_oosc_girls'=> 'nullable|integer|min:0|max:9999',
            'new_morning_p2p_boys'  => 'nullable|integer|min:0|max:9999',
            'new_morning_p2p_girls' => 'nullable|integer|min:0|max:9999',
            'new_evening_oosc_boys' => 'nullable|integer|min:0|max:9999',
            'new_evening_oosc_girls'=> 'nullable|integer|min:0|max:9999',
            'new_evening_p2p_boys'  => 'nullable|integer|min:0|max:9999',
            'new_evening_p2p_girls' => 'nullable|integer|min:0|max:9999',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.exists'         => 'The selected class is not valid.',
            'admission_date.required' => 'Please select the date to correct.',
            'reason.min'              => 'Please provide at least 10 characters explaining the reason.',
            '*.min'                   => 'Counts cannot be negative.',
            '*.max'                   => 'A single field cannot exceed 9,999.',
        ];
    }
}
