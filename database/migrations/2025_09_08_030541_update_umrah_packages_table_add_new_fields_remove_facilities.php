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
        Schema::table('umrah_packages', function (Blueprint $table) {
            // Add new optional columns
            $table->date('arrival_date')->nullable()->after('departure_date');
            $table->string('airlines')->nullable()->after('arrival_date');
            $table->string('flight_number')->nullable()->after('airlines');
            $table->string('hotel_madinah')->nullable()->after('flight_number');
            $table->string('hotel_makkah')->nullable()->after('hotel_madinah');
            
            // Remove facilities column
            $table->dropColumn('facilities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('umrah_packages', function (Blueprint $table) {
            // Remove the new columns
            $table->dropColumn([
                'arrival_date',
                'airlines', 
                'flight_number',
                'hotel_madinah',
                'hotel_makkah'
            ]);
            
            // Re-add facilities column
            $table->json('facilities')->nullable()->after('duration_days');
        });
    }
};
