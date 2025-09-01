<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'method_name',
    ];

    /**
     * Scope to search by method name.
     */
    public function scopeByMethodName($query, $methodName)
    {
        return $query->where('method_name', 'like', '%' . $methodName . '%');
    }
}