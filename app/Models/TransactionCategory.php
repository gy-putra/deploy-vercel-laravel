<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'type',
        'description',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Get formatted type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'income' => 'Income',
            'expense' => 'Expense',
            default => 'Unknown'
        };
    }

    /**
     * Scope to get only income categories.
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope to get only expense categories.
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }
}