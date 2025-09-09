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
            // Make nik nullable and remove unique constraint temporarily
            $table->string('nik')->nullable()->change();
            
            // Make phone and address nullable
            $table->string('phone')->nullable()->change();
            $table->text('address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilgrims', function (Blueprint $table) {
            // Revert back to NOT NULL
            $table->string('nik')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
        });
    }
};
