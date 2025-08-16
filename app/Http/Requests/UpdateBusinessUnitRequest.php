<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessUnitRequest extends FormRequest
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
        $businessUnitId = $this->route('businessUnit') ? $this->route('businessUnit')->id : 
                         ($this->route('business_unit') ? $this->route('business_unit')->id : null);

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('business_units')->ignore($businessUnitId)
            ],
            'description' => [
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
        $businessUnit = $this->route('businessUnit') ?? $this->route('business_unit');
        
        // Additional business rule: Ensure name is not just whitespace
        if ($this->name && trim($this->name) === '') {
            $validator->errors()->add('name', 'Business unit name cannot be empty or contain only whitespace.');
        }

        // Additional business rule: Normalize name to prevent near-duplicates
        if ($this->name && $businessUnit) {
            $normalizedName = strtolower(trim($this->name));
            $existingUnit = \App\Models\BusinessUnit::whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
                ->where('id', '!=', $businessUnit->id)
                ->first();
            
            if ($existingUnit) {
                $validator->errors()->add('name', 'A business unit with a similar name already exists.');
            }
        }

        // Prevent deactivating business units with active contracts
        if ($this->has('is_active') && $this->is_active === false && $businessUnit) {
            $activeContractsCount = $businessUnit->contracts()->where('is_active', true)->count();
            if ($activeContractsCount > 0) {
                $validator->errors()->add('is_active', 'Cannot deactivate business unit with active contracts. Please deactivate or reassign contracts first.');
            }
        }
    }
}
