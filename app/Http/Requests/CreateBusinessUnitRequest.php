<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBusinessUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:business_units,name'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'is_active' => [
                'sometimes',
                'boolean'
            ]
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Business unit name is required.',
            'name.max' => 'Business unit name cannot exceed 255 characters.',
            'name.unique' => 'A business unit with this name already exists.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional business rule: Ensure name is not just whitespace
            if ($this->name && trim($this->name) === '') {
                $validator->errors()->add('name', 'Business unit name cannot be empty or contain only whitespace.');
            }

            // Additional business rule: Normalize name to prevent near-duplicates
            if ($this->name) {
                $normalizedName = strtolower(trim($this->name));
                $existingUnit = \App\Models\BusinessUnit::whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])->first();
                
                if ($existingUnit) {
                    $validator->errors()->add('name', 'A business unit with a similar name already exists.');
                }
            }
        });
    }
}
