<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class HalalTourPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'destination',
        'country',
        'price',
        'duration_days',
        'facilities',
        'description',
        'departure_date',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'facilities' => 'array',
        'departure_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the quota for this package.
     */
    public function quota(): MorphOne
    {
        return $this->morphOne(Quota::class, 'package');
    }

    /**
     * Get all departure schedules for this package.
     */
    public function departureSchedules(): MorphMany
    {
        return $this->morphMany(DepartureSchedule::class, 'package');
    }

    /**
     * Get the active departure schedules for this package.
     */
    public function activeDepartureSchedules(): MorphMany
    {
        return $this->departureSchedules()->where('is_active', true);
    }

    /**
     * Scope to get only active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by destination.
     */
    public function scopeByDestination($query, $destination)
    {
        return $query->where('destination', 'like', '%' . $destination . '%');
    }

    /**
     * Scope to filter by country.
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', 'like', '%' . $country . '%');
    }

    /**
     * Scope to filter by price range.
     */
    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Get formatted price with currency.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get full destination (destination, country).
     */
    public function getFullDestinationAttribute(): string
    {
        return $this->destination . ', ' . $this->country;
    }

    /**
     * Get remaining quota for this package.
     */
    public function getRemainingQuotaAttribute(): int
    {
        return $this->quota ? $this->quota->remaining_quota : 0;
    }

    /**
     * Check if package is almost full (based on notification threshold).
     */
    public function getIsAlmostFullAttribute(): bool
    {
        if (!$this->quota) {
            return false;
        }
        
        return $this->quota->remaining_quota <= $this->quota->notification_threshold;
    }
}