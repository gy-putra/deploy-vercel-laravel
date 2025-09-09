<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the document_type enum to include all new document types
        DB::statement("ALTER TABLE pilgrim_documents MODIFY COLUMN document_type ENUM(
            'ktp',
            'kk', 
            'passport',
            'visa',
            'marriage_certificate',
            'birth_certificate',
            'transfer_proof',
            'vaccine',
            'ticket'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE pilgrim_documents MODIFY COLUMN document_type ENUM(
            'passport',
            'visa',
            'vaccine',
            'ticket'
        ) NOT NULL");
    }
};
