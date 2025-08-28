<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Quota extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_type',
        'package_id',
        'total_quota',
        'registered_pilgrims',
        'notification_threshold',
    ];

    protected $casts = [
        'total_quota' => 'integer',
        'registered_pilgrims' => 'integer',
        'notification_threshold' => 'integer',
    ];

    /**
     * Get the package that owns this quota (polymorphic).
     */
    public function package(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the remaining quota.
     */
    public function getRemainingQuotaAttribute(): int
    {
        return max(0, $this->total_quota - $this->registered_pilgrims);
    }

    /**
     * Check if quota is full.
     */
    public function getIsFullAttribute(): bool
    {
        return $this->registered_pilgrims >= $this->total_quota;
    }

    /**
     * Check if quota is almost full (within notification threshold).
     */
    public function getIsAlmostFullAttribute(): bool
    {
        return $this->remaining_quota <= $this->notification_threshold;
    }

    /**
     * Get the quota utilization percentage.
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->total_quota == 0) {
            return 0;
        }
        
        return round(($this->registered_pilgrims / $this->total_quota) * 100, 2);
    }

    /**
     * Increment registered pilgrims count.
     */
    public function incrementRegistered(int $count = 1): bool
    {
        if ($this->registered_pilgrims + $count > $this->total_quota) {
            return false; // Cannot exceed total quota
        }
        
        $this->increment('registered_pilgrims', $count);
        return true;
    }

    /**
     * Decrement registered pilgrims count.
     */
    public function decrementRegistered(int $count = 1): bool
    {
        if ($this->registered_pilgrims - $count < 0) {
            return false; // Cannot go below zero
        }
        
        $this->decrement('registered_pilgrims', $count);
        return true;
    }

    /**
     * Scope to get quotas that are almost full.
     */
    public function scopeAlmostFull($query)
    {
        return $query->whereRaw('(total_quota - registered_pilgrims) <= notification_threshold');
    }

    /**
     * Scope to get full quotas.
     */
    public function scopeFull($query)
    {
        return $query->whereRaw('registered_pilgrims >= total_quota');
    }

    /**
     * Scope to get available quotas.
     */
    public function scopeAvailable($query)
    {
        return $query->whereRaw('registered_pilgrims < total_quota');
    }
}