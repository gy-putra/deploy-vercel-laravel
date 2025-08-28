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
            // Modify uploaded_at column to be nullable with default current timestamp
            $table->timestamp('uploaded_at')->nullable()->default(now())->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilgrim_documents', function (Blueprint $table) {
            // Revert uploaded_at column to not nullable without default
            $table->timestamp('uploaded_at')->nullable(false)->change();
        });
    }
};
