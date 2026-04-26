<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDailyAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // controller-level gate via authorize('admission.create/submit')
    }

    public function rules(): array
    {
        return [
            'admissions'                        => 'required|array',
            'admissions.*.class_id'             => 'required|integer|exists:classes,id',
            'admissions.*.morning_boys'         => 'required|integer|min:0|max:9999',
            'admissions.*.morning_girls'        => 'required|integer|min:0|max:9999',
            'admissions.*.evening_boys'         => 'nullable|integer|min:0|max:9999',
            'admissions.*.evening_girls'        => 'nullable|integer|min:0|max:9999',
            'admissions.*.morning_oosc_boys'    => 'nullable|integer|min:0|max:9999',
            'admissions.*.morning_oosc_girls'   => 'nullable|integer|min:0|max:9999',
            'admissions.*.morning_p2p_boys'     => 'nullable|integer|min:0|max:9999',
            'admissions.*.morning_p2p_girls'    => 'nullable|integer|min:0|max:9999',
            'admissions.*.evening_oosc_boys'    => 'nullable|integer|min:0|max:9999',
            'admissions.*.evening_oosc_girls'   => 'nullable|integer|min:0|max:9999',
            'admissions.*.evening_p2p_boys'     => 'nullable|integer|min:0|max:9999',
            'admissions.*.evening_p2p_girls'    => 'nullable|integer|min:0|max:9999',
            'admissions.*.matric_tech_count'     => 'nullable|integer|min:0|max:9999',
            'admissions.*.existing_enrollment'  => 'nullable|integer|min:0|max:99999',
        ];
    }

    public function messages(): array
    {
        return [
            'admissions.required'                    => 'No admission data was submitted.',
            'admissions.*.class_id.exists'           => 'One or more class IDs are invalid.',
            'admissions.*.morning_boys.min'          => 'Admission counts cannot be negative.',
            'admissions.*.*.max'                     => 'A single field cannot exceed 9,999.',
        ];
    }
}
