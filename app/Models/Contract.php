<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'contract_counter_party_id',
        'date_signed',
        'expiry_date',
        'business_unit_id',
        'is_active'
    ];

    protected $casts = [
        'date_signed' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the contract counter party that owns the contract.
     */
    public function contractCounterParty(): BelongsTo
    {
        return $this->belongsTo(ContractCounterParty::class);
    }

    /**
     * Get the business unit that owns the contract.
     */
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    /**
     * Scope a query to only include active contracts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive contracts.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to only include expired contracts.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope a query to only include contracts expiring soon (within specified days).
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('expiry_date', '>', now())
                    ->where('expiry_date', '<=', now()->addDays($days));
    }

    /**
     * Scope a query to only include contracts expiring within a date range.
     */
    public function scopeExpiringBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('expiry_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by date signed range.
     */
    public function scopeSignedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('date_signed', [$startDate, $endDate]);
    }

    /**
     * Get the formatted date signed.
     */
    public function getFormattedDateSignedAttribute()
    {
        return $this->date_signed ? $this->date_signed->format('M d, Y') : null;
    }

    /**
     * Get the formatted expiry date.
     */
    public function getFormattedExpiryDateAttribute()
    {
        return $this->expiry_date ? $this->expiry_date->format('M d, Y') : null;
    }

    /**
     * Check if the contract is expired.
     */
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if the contract is expiring soon (within 7 days).
     */
    public function getIsExpiringSoonAttribute()
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays(now()) <= 7;
    }

    /**
     * Get the number of days until expiry.
     */
    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }

        $days = now()->diffInDays($this->expiry_date, false);
        return $days >= 0 ? $days : 0;
    }

    /**
     * Get the contract status based on expiry date.
     */
    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->is_expired) {
            return 'expired';
        }

        if ($this->is_expiring_soon) {
            return 'expiring_soon';
        }

        return 'active';
    }
}
