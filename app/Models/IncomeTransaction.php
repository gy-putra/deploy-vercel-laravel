<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomeTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'pilgrim_id',
        'departure_schedule_id',
        'transaction_category_id',
        'payment_method_id',
        'account_id',
        'amount',
        'payment_date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the pilgrim that owns this income transaction.
     */
    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }

    /**
     * Get the departure schedule that owns this income transaction.
     */
    public function departureSchedule(): BelongsTo
    {
        return $this->belongsTo(DepartureSchedule::class);
    }

    /**
     * Get the transaction category that owns this income transaction.
     */
    public function transactionCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class);
    }

    /**
     * Get the payment method that owns this income transaction.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the account that owns this income transaction.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get the package type and name from departure schedule.
     */
    public function getPackageInfoAttribute(): string
    {
        if (!$this->departureSchedule) {
            return 'N/A';
        }

        $schedule = $this->departureSchedule;
        $packageName = $schedule->package ? $schedule->package->name : 'Unknown Package';
        $packageType = ucfirst(str_replace('_', ' ', $schedule->package_type));
        
        return "{$packageType}: {$packageName}";
    }

    /**
     * Get only the package name from departure schedule.
     */
    public function getPackageNameAttribute(): string
    {
        if (!$this->departureSchedule) {
            return 'N/A';
        }

        $schedule = $this->departureSchedule;
        return $schedule->package ? $schedule->package->name : 'Unknown Package';
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by pilgrim.
     */
    public function scopeByPilgrim($query, $pilgrimId)
    {
        return $query->where('pilgrim_id', $pilgrimId);
    }

    /**
     * Scope to filter by departure schedule.
     */
    public function scopeBySchedule($query, $scheduleId)
    {
        return $query->where('departure_schedule_id', $scheduleId);
    }
}