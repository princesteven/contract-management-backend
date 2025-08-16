<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateContractRequest extends FormRequest
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
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('contracts')->where(function ($query) {
                    return $query->where('is_active', true);
                })
            ],
            'contract_counter_party_id' => [
                'required',
                'integer',
                'exists:contract_counter_parties,id'
            ],
            'date_signed' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'expiry_date' => [
                'required',
                'date',
                'after:date_signed'
            ],
            'business_unit_id' => [
                'required',
                'integer',
                'exists:business_units,id'
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
            'title.required' => 'Contract title is required.',
            'title.unique' => 'A contract with this title already exists and is active.',
            'contract_counter_party_id.required' => 'Contract counter party is required.',
            'contract_counter_party_id.exists' => 'Selected contract counter party does not exist.',
            'date_signed.required' => 'Date signed is required.',
            'date_signed.before_or_equal' => 'Date signed cannot be in the future.',
            'expiry_date.required' => 'Expiry date is required.',
            'expiry_date.after' => 'Expiry date must be after the date signed.',
            'business_unit_id.required' => 'Business unit is required.',
            'business_unit_id.exists' => 'Selected business unit does not exist.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional business rule: Check if counter party is active
            if ($this->contract_counter_party_id) {
                $counterParty = \App\Models\ContractCounterParty::find($this->contract_counter_party_id);
                if ($counterParty && !$counterParty->is_active) {
                    $validator->errors()->add('contract_counter_party_id', 'Selected contract counter party is not active.');
                }
            }

            // Additional business rule: Check if business unit is active
            if ($this->business_unit_id) {
                $businessUnit = \App\Models\BusinessUnit::find($this->business_unit_id);
                if ($businessUnit && !$businessUnit->is_active) {
                    $validator->errors()->add('business_unit_id', 'Selected business unit is not active.');
                }
            }

            // Additional business rule: Ensure minimum contract duration (e.g., at least 1 day)
            if ($this->date_signed && $this->expiry_date) {
                $dateSigned = \Carbon\Carbon::parse($this->date_signed);
                $expiryDate = \Carbon\Carbon::parse($this->expiry_date);
                
                if ($expiryDate->diffInDays($dateSigned) < 1) {
                    $validator->errors()->add('expiry_date', 'Contract must have a minimum duration of 1 day.');
                }
            }
        });
    }
}
