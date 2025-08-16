<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateContractCounterPartyRequest extends FormRequest
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
                'max:255'
            ],
            'contact_person' => [
                'nullable',
                'string',
                'max:255'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255'
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^255\d{9}$/'
            ],
            'address' => [
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
            'name.required' => 'Counter party name is required.',
            'name.max' => 'Counter party name cannot exceed 255 characters.',
            'contact_person.max' => 'Contact person name cannot exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email address cannot exceed 255 characters.',
            'phone.regex' => 'Phone number must begin with 255 followed by 9 digits (e.g., 255123456789).',
            'address.max' => 'Address cannot exceed 1000 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional business rule: If email is provided, ensure it's not already in use by another active counter party
            if ($this->email) {
                $existingCounterParty = \App\Models\ContractCounterParty::where('email', $this->email)
                    ->where('is_active', true)
                    ->first();
                
                if ($existingCounterParty) {
                    $validator->errors()->add('email', 'This email address is already in use by another active counter party.');
                }
            }

            // Additional business rule: If phone is provided, ensure it's not already in use by another active counter party
            if ($this->phone) {
                $existingCounterParty = \App\Models\ContractCounterParty::where('phone', $this->phone)
                    ->where('is_active', true)
                    ->first();
                
                if ($existingCounterParty) {
                    $validator->errors()->add('phone', 'This phone number is already in use by another active counter party.');
                }
            }

            // Ensure at least one contact method is provided
            if (!$this->email && !$this->phone) {
                $validator->errors()->add('email', 'At least one contact method (email or phone) must be provided.');
                $validator->errors()->add('phone', 'At least one contact method (email or phone) must be provided.');
            }
        });
    }
}
