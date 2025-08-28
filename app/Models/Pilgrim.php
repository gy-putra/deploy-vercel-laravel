<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Pilgrim extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nik',
        'passport_number',
        'phone',
        'email',
        'address',
        'package_name',
        'registration_date',
        'payment_status',
        'status',
        'birth_date',
        'gender',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'birth_date' => 'date',
    ];

    /**
     * Get the package that the pilgrim belongs to.
     * Note: Currently storing package_name directly instead of using relationship
     */
    // public function package(): BelongsTo
    // {
    //     return $this->belongsTo(Package::class);
    // }

    /**
     * Get all documents for this pilgrim.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PilgrimDocument::class);
    }

    /**
     * Get the formatted payment status.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'Pending',
            'partial' => 'Partial Payment',
            'paid' => 'Fully Paid',
            'refunded' => 'Refunded',
            default => 'Unknown'
        };
    }

    /**
     * Get the formatted status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'ready_to_depart' => 'Ready to Depart',
            'completed' => 'Completed',
            default => 'Unknown'
        };
    }

    /**
     * Check if all required documents are uploaded and approved.
     */
    public function getDocumentCompletenessAttribute(): string
    {
        $requiredDocuments = ['passport', 'visa', 'vaccine', 'ticket'];
        $approvedDocuments = $this->documents()->where('status', 'approved')->pluck('document_type')->toArray();
        
        $missingDocuments = array_diff($requiredDocuments, $approvedDocuments);
        
        return empty($missingDocuments) ? 'Complete' : 'Incomplete';
    }

    /**
     * Scope to filter by package name.
     */
    public function scopeByPackage($query, $packageName)
    {
        return $query->where('package_name', $packageName);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by payment status.
     */
    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }
}