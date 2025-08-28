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
        Schema::create('quotas', function (Blueprint $table) {
            $table->id();
            $table->morphs('package'); // polymorphic relationship for both umrah and halal tour packages
            $table->integer('total_quota');
            $table->integer('registered_pilgrims')->default(0);
            // Note: remaining_quota and is_full are computed attributes in the model
            $table->integer('notification_threshold')->default(10); // Notify when remaining quota <= this value
            $table->timestamps();
            
            // Ensure one quota record per package
            $table->unique(['package_type', 'package_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotas');
    }
};