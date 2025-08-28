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
        Schema::table('pilgrims', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['package_id']);
            // Then drop the package_id column
            $table->dropColumn('package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilgrims', function (Blueprint $table) {
            // Re-add the package_id column and foreign key constraint
            $table->foreignId('package_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
