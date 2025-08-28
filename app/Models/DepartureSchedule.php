<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class DepartureSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_type',
        'package_id',
        'departure_date',
        'return_date',
        'departure_time',
        'return_time',
        'departure_location',
        'return_location',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'departure_time' => 'datetime:H:i',
        'return_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    /**
     * Get the package that owns this schedule (polymorphic).
     */
    public function package(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the duration in days.
     */
    public function getDurationDaysAttribute(): int
    {
        if (!$this->departure_date || !$this->return_date) {
            return 0;
        }
        
        return $this->departure_date->diffInDays($this->return_date) + 1;
    }

    /**
     * Get formatted departure datetime.
     */
    public function getFormattedDepartureDatetimeAttribute(): string
    {
        $date = $this->departure_date->format('d M Y');
        $time = $this->departure_time ? $this->departure_time->format('H:i') : '';
        
        return $time ? "$date at $time" : $date;
    }

    /**
     * Get formatted return datetime.
     */
    public function getFormattedReturnDatetimeAttribute(): string
    {
        $date = $this->return_date->format('d M Y');
        $time = $this->return_time ? $this->return_time->format('H:i') : '';
        
        return $time ? "$date at $time" : $date;
    }

    /**
     * Check if departure is upcoming.
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->departure_date->isFuture();
    }

    /**
     * Check if departure is today.
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->departure_date->isToday();
    }

    /**
     * Check if departure is past.
     */
    public function getIsPastAttribute(): bool
    {
        return $this->departure_date->isPast();
    }

    /**
     * Scope to get only active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get upcoming schedules.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('departure_date', '>=', now()->toDateString());
    }

    /**
     * Scope to get past schedules.
     */
    public function scopePast($query)
    {
        return $query->where('departure_date', '<', now()->toDateString());
    }

    /**
     * Scope to filter by month and year.
     */
    public function scopeByMonthYear($query, $month, $year)
    {
        return $query->whereMonth('departure_date', $month)
                    ->whereYear('departure_date', $year);
    }

    /**
     * Scope to filter by year.
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('departure_date', $year);
    }

    /**
     * Scope to filter by month.
     */
    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('departure_date', $month);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('departure_date', [$startDate, $endDate]);
    }
}