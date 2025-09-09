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
        // Check if the table exists first
        if (!Schema::hasTable('pilgrim_documents')) {
            throw new \Exception('Table pilgrim_documents does not exist. Please run the base migrations first.');
        }
        
        // Get existing columns
        $existingColumns = Schema::getColumnListing('pilgrim_documents');
        
        // Drop foreign key constraint if it exists
        if (in_array('verified_by', $existingColumns)) {
            try {
                Schema::table('pilgrim_documents', function (Blueprint $table) {
                    $table->dropForeign(['verified_by']);
                });
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
        }
        
        // Drop unique constraint if it exists
        try {
            Schema::table('pilgrim_documents', function (Blueprint $table) {
                $table->dropUnique(['pilgrim_id', 'document_type']);
            });
        } catch (\Exception $e) {
            // Unique constraint doesn't exist, continue
        }
        
        // Drop existing columns that exist (except document_type)
        $columnsToDrop = [];
        $possibleColumns = ['file', 'description', 'category', 'status', 'notes', 'uploaded_at', 'verified_at', 'verified_by'];
        
        foreach ($possibleColumns as $column) {
            if (in_array($column, $existingColumns)) {
                $columnsToDrop[] = $column;
            }
        }
        
        if (!empty($columnsToDrop)) {
            Schema::table('pilgrim_documents', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
        
        // Handle document_type column more carefully
        if (in_array('document_type', $existingColumns)) {
            // First, add the new columns
            Schema::table('pilgrim_documents', function (Blueprint $table) {
                // Add files column as JSON to store multiple files
                $table->json('files')->nullable()->after('document_type');
                
                // Add is_optional boolean column
                $table->boolean('is_optional')->default(false)->after('files');
            });
            
            // Then update existing document_type values to new format if needed
            // This is safer than dropping and recreating the column
            try {
                // Update existing values to match new enum if they exist
                DB::statement("UPDATE pilgrim_documents SET document_type = 'passport' WHERE document_type = 'passport'");
                DB::statement("UPDATE pilgrim_documents SET document_type = 'visa' WHERE document_type = 'visa'");
                // Add default values for new document types if needed
            } catch (\Exception $e) {
                // Continue if update fails
            }
        } else {
            // If document_type doesn't exist, create everything from scratch
            Schema::table('pilgrim_documents', function (Blueprint $table) {
                // Add new document_type enum with Indonesian document types
                $table->enum('document_type', [
                    'ktp',                  // ID Card
                    'kk',                   // Family Card  
                    'passport',             // Passport
                    'visa',                 // Visa
                    'marriage_certificate', // Marriage Certificate
                    'birth_certificate',    // Birth Certificate
                    'transfer_proof'        // Proof of Transfer
                ])->after('pilgrim_id');
                
                // Add files column as JSON to store multiple files
                $table->json('files')->nullable()->after('document_type');
                
                // Add is_optional boolean column
                $table->boolean('is_optional')->default(false)->after('files');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilgrim_documents', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn(['files', 'is_optional']);
            
            // Restore old document_type enum
            $table->dropColumn('document_type');
        });
        
        Schema::table('pilgrim_documents', function (Blueprint $table) {
            $table->enum('document_type', ['passport', 'visa', 'vaccine', 'ticket'])->after('pilgrim_id');
            
            // Restore old columns
            $table->string('file')->nullable()->after('document_type');
            $table->text('description')->nullable()->after('file');
            $table->string('category')->nullable()->after('description');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('category');
            $table->text('notes')->nullable()->after('status');
            $table->timestamp('uploaded_at')->after('notes');
            $table->timestamp('verified_at')->nullable()->after('uploaded_at');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null')->after('verified_at');
            
            // Restore unique constraint
            $table->unique(['pilgrim_id', 'document_type']);
        });
    }
};
