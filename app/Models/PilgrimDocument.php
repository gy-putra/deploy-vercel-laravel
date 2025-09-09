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
        'files',
        'is_optional',
    ];

    protected $casts = [
        'files' => 'array',
        'is_optional' => 'boolean',
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
            'ktp' => 'KTP (ID Card)',
            'kk' => 'KK (Family Card)',
            'passport' => 'Passport',
            'visa' => 'Visa',
            'marriage_certificate' => 'Marriage Certificate',
            'birth_certificate' => 'Birth Certificate',
            'transfer_proof' => 'Transfer Proof',
            'vaccine' => 'Vaccine Certificate',
            'ticket' => 'Flight Ticket',
            default => 'Unknown'
        };
    }

    /**
     * Get the first file from the files array.
     */
    public function getFirstFileAttribute(): ?string
    {
        return $this->files && is_array($this->files) && count($this->files) > 0 ? $this->files[0] : null;
    }

    /**
     * Get the formatted file size from the first file.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $firstFile = $this->first_file;
        if (!$firstFile || !Storage::exists($firstFile)) {
            return 'N/A';
        }
        
        try {
            $bytes = Storage::size($firstFile);
            $units = ['B', 'KB', 'MB', 'GB'];
            
            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                $bytes /= 1024;
            }
            
            return round($bytes, 2) . ' ' . $units[$i];
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get the full URL to the first document file.
     */
    public function getFileUrlAttribute(): string
    {
        $firstFile = $this->first_file;
        return $firstFile ? Storage::url($firstFile) : '';
    }

    /**
     * Check if the first document is an image.
     */
    public function getIsImageAttribute(): bool
    {
        $firstFile = $this->first_file;
        if (!$firstFile) return false;
        
        $extension = strtolower(pathinfo($firstFile, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png']);
    }

    /**
     * Check if the first document is a PDF.
     */
    public function getIsPdfAttribute(): bool
    {
        $firstFile = $this->first_file;
        if (!$firstFile) return false;
        
        $extension = strtolower(pathinfo($firstFile, PATHINFO_EXTENSION));
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
     * Delete the physical files when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            if ($document->files && is_array($document->files)) {
                foreach ($document->files as $file) {
                    if (Storage::exists($file)) {
                        Storage::delete($file);
                    }
                }
            }
        });
    }
}