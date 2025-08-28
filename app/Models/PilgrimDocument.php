<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PilgrimDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'pilgrim_id',
        'document_type',
        'file',
        'description',
        'category',
        'status',
        'notes',
        'uploaded_at',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the pilgrim that owns the document.
     */
    public function pilgrim(): BelongsTo
    {
        return $this->belongsTo(Pilgrim::class);
    }

    /**
     * Get the user who verified the document.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the formatted document type.
     */
    public function getDocumentTypeLabelAttribute(): string
    {
        return match($this->document_type) {
            'passport' => 'Passport',
            'visa' => 'Visa',
            'vaccine' => 'Vaccine Certificate',
            'ticket' => 'Flight Ticket',
            default => 'Unknown'
        };
    }

    /**
     * Get the formatted status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown'
        };
    }

    /**
     * Get the formatted file size from the actual file.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file || !Storage::exists($this->file)) {
            return 'N/A';
        }
        
        $bytes = Storage::size($this->file);
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the full URL to the document file.
     */
    public function getFileUrlAttribute(): string
    {
        return $this->file ? Storage::url($this->file) : '';
    }

    /**
     * Check if the document is an image.
     */
    public function getIsImageAttribute(): bool
    {
        if (!$this->file) return false;
        
        $extension = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png']);
    }

    /**
     * Check if the document is a PDF.
     */
    public function getIsPdfAttribute(): bool
    {
        if (!$this->file) return false;
        
        $extension = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));
        return $extension === 'pdf';
    }

    /**
     * Scope to filter by document type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Delete the physical file when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            if ($document->file && Storage::exists($document->file)) {
                Storage::delete($document->file);
            }
        });
    }
}