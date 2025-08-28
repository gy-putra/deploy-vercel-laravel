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
        Schema::create('pilgrims', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nik')->unique(); // National ID Number
            $table->string('passport_number')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->date('registration_date');
            $table->enum('payment_status', ['pending', 'partial', 'paid', 'refunded'])->default('pending');
            $table->enum('status', ['pending', 'processing', 'ready_to_depart', 'completed'])->default('pending');
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female']);
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilgrims');
    }
};