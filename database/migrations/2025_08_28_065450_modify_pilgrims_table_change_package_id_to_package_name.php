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
            // Add the new package_name column
            $table->string('package_name')->nullable()->after('address');
        });
        
        // Note: You may need to manually populate package_name values
        // from existing package_id relationships before dropping package_id
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilgrims', function (Blueprint $table) {
            // Drop the package_name column
            $table->dropColumn('package_name');
        });
    }
};
