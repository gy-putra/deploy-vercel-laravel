<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_name',
        'account_number',
        'account_type',
        'description',
    ];

    protected $casts = [
        'account_type' => 'string',
    ];

    /**
     * Get formatted account type label.
     */
    public function getAccountTypeLabelAttribute(): string
    {
        return match($this->account_type) {
            'Bank' => 'Bank Account',
            'Cash' => 'Cash Account',
            default => 'Unknown'
        };
    }

    /**
     * Get full account display name.
     */
    public function getFullAccountNameAttribute(): string
    {
        if ($this->account_number) {
            return $this->account_name . ' (' . $this->account_number . ')';
        }
        return $this->account_name;
    }

    /**
     * Scope to get only bank accounts.
     */
    public function scopeBank($query)
    {
        return $query->where('account_type', 'Bank');
    }

    /**
     * Scope to get only cash accounts.
     */
    public function scopeCash($query)
    {
        return $query->where('account_type', 'Cash');
    }
}