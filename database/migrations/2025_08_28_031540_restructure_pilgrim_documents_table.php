<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pilgrim_documents', function (Blueprint $table) {
            // Remove old file metadata columns
            $table->dropColumn(['file_name', 'file_path', 'file_type', 'file_size']);
            
            // Add new file column to store the uploaded file path
            $table->string('file')->nullable()->after('document_type');
            
            // Add optional columns for better categorization
            $table->text('description')->nullable()->after('file');
            $table->string('category')->nullable()->after('description'); // e.g., Passport, Visa, Vaccine, Ticket
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilgrim_documents', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn(['file', 'description', 'category']);
            
            // Restore old file metadata columns
            $table->string('file_name')->nullable()->after('document_type');
            $table->string('file_path')->nullable()->after('file_name');
            $table->string('file_type')->nullable()->after('file_path');
            $table->integer('file_size')->nullable()->after('file_type');
        });
    }
};
