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
        Schema::create('departure_schedules', function (Blueprint $table) {
            $table->id();
            $table->morphs('package'); // polymorphic relationship for both umrah and halal tour packages
            $table->date('departure_date');
            $table->date('return_date');
            $table->time('departure_time')->nullable();
            $table->time('return_time')->nullable();
            $table->string('departure_location')->nullable();
            $table->string('return_location')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Index for filtering by month/year
            $table->index(['departure_date', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departure_schedules');
    }
};