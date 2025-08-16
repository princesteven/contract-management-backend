<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractCounterParty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all contracts for this counter party.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Scope a query to only include active counter parties.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive counter parties.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the number of active contracts for this counter party.
     */
    public function getActiveContractsCountAttribute()
    {
        return $this->contracts()->where('is_active', true)->count();
    }

    /**
     * Get the formatted phone number.
     */
    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) {
            return null;
        }
        
        // Format phone number: +255 XXX XXX XXX
        if (strlen($this->phone) === 12 && str_starts_with($this->phone, '255')) {
            return '+' . substr($this->phone, 0, 3) . ' ' . 
                   substr($this->phone, 3, 3) . ' ' . 
                   substr($this->phone, 6, 3) . ' ' . 
                   substr($this->phone, 9, 3);
        }
        
        return $this->phone;
    }

    /**
     * Mutator for phone number - ensure it starts with 255.
     */
    public function setPhoneAttribute($value)
    {
        if ($value && !str_starts_with($value, '255')) {
            // Remove any non-numeric characters except +
            $cleaned = preg_replace('/[^0-9+]/', '', $value);
            
            // If it starts with +255, remove the +
            if (str_starts_with($cleaned, '+255')) {
                $cleaned = substr($cleaned, 1);
            }
            
            // If it doesn't start with 255, prepend it (assuming it's a local number)
            if (!str_starts_with($cleaned, '255') && strlen($cleaned) === 9) {
                $cleaned = '255' . $cleaned;
            }
            
            $this->attributes['phone'] = $cleaned;
        } else {
            $this->attributes['phone'] = $value;
        }
    }
}
