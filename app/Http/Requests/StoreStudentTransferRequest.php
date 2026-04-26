<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_institution_id'          => 'required|integer|exists:institutions,id',
            'students'                   => 'required|array|min:1',
            'students.*.class_id'        => 'required|integer|exists:classes,id',
            'students.*.student_name'    => 'nullable|string|max:100',
            'students.*.father_name'     => 'nullable|string|max:100',
            'students.*.notes'           => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'to_institution_id.required'  => 'Please select a destination school.',
            'to_institution_id.exists'    => 'The selected destination school does not exist.',
            'students.required'           => 'At least one student row is required.',
            'students.min'                => 'At least one student row is required.',
            'students.*.class_id.required'=> 'Each row must have a class selected.',
            'students.*.class_id.exists'  => 'One or more selected classes are invalid.',
        ];
    }
}
