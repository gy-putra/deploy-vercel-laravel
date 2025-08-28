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
        Schema::create('pilgrim_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pilgrim_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', ['passport', 'visa', 'vaccine', 'ticket']);
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type'); // pdf, jpg, png
            $table->integer('file_size'); // in bytes
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('uploaded_at');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Ensure one document per type per pilgrim
            $table->unique(['pilgrim_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilgrim_documents');
    }
};