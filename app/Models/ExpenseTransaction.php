<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_schedule_id',
        'transaction_category_id',
        'amount',
        'payment_method_id',
        'account_id',
        'description',
        'date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Get the departure schedule that owns this expense transaction.
     * This is nullable for general operational expenses.
     */
    public function departureSchedule(): BelongsTo
    {
        return $this->belongsTo(DepartureSchedule::class);
    }

    /**
     * Get the transaction category that owns this expense transaction.
     */
    public function transactionCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class);
    }

    /**
     * Get the payment method that owns this expense transaction.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the account that owns this expense transaction.
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
            return 'General Operation';
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
            return 'General Operation';
        }

        $schedule = $this->departureSchedule;
        return $schedule->package ? $schedule->package->name : 'Unknown Package';
    }

    /**
     * Get the expense type (trip-related or general).
     */
    public function getExpenseTypeAttribute(): string
    {
        return $this->departure_schedule_id ? 'Trip Related' : 'General Operation';
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by departure schedule.
     */
    public function scopeBySchedule($query, $scheduleId)
    {
        return $query->where('departure_schedule_id', $scheduleId);
    }

    /**
     * Scope to filter by trip-related expenses.
     */
    public function scopeTripRelated($query)
    {
        return $query->whereNotNull('departure_schedule_id');
    }

    /**
     * Scope to filter by general operational expenses.
     */
    public function scopeGeneralOperation($query)
    {
        return $query->whereNull('departure_schedule_id');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('transaction_category_id', $categoryId);
    }
}