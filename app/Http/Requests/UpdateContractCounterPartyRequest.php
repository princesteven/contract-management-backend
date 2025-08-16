<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractCounterPartyRequest extends FormRequest
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
                'sometimes',
                'string',
                'max:255'
            ],
            'contact_person' => [
                'sometimes',
                'nullable',
                'string',
                'max:255'
            ],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255'
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^255\d{9}$/'
            ],
            'address' => [
                'sometimes',
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
        $counterParty = $this->route('contractCounterParty') ?? $this->route('contract_counter_party');
        
        // Additional business rule: If email is provided, ensure it's not already in use by another active counter party
        if ($this->email) {
            $existingCounterParty = \App\Models\ContractCounterParty::where('email', $this->email)
                ->where('is_active', true)
                ->where('id', '!=', $counterParty ? $counterParty->id : null)
                ->first();
            
            if ($existingCounterParty) {
                $validator->errors()->add('email', 'This email address is already in use by another active counter party.');
            }
        }

        // Additional business rule: If phone is provided, ensure it's not already in use by another active counter party
        if ($this->phone) {
            $existingCounterParty = \App\Models\ContractCounterParty::where('phone', $this->phone)
                ->where('is_active', true)
                ->where('id', '!=', $counterParty ? $counterParty->id : null)
                ->first();
            
            if ($existingCounterParty) {
                $validator->errors()->add('phone', 'This phone number is already in use by another active counter party.');
            }
        }

        // Ensure at least one contact method remains after update
        if ($counterParty) {
            $finalEmail = $this->has('email') ? $this->email : $counterParty->email;
            $finalPhone = $this->has('phone') ? $this->phone : $counterParty->phone;
            
            if (!$finalEmail && !$finalPhone) {
                $validator->errors()->add('email', 'At least one contact method (email or phone) must be maintained.');
                $validator->errors()->add('phone', 'At least one contact method (email or phone) must be maintained.');
            }
        }

        // Prevent deactivating counter parties with active contracts
        if ($this->has('is_active') && $this->is_active === false && $counterParty) {
            $activeContractsCount = $counterParty->contracts()->where('is_active', true)->count();
            if ($activeContractsCount > 0) {
                $validator->errors()->add('is_active', 'Cannot deactivate counter party with active contracts. Please deactivate or reassign contracts first.');
            }
        }
    }
}
