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
        Schema::create('income_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pilgrim_id')->constrained('pilgrims')->onDelete('cascade');
            $table->foreignId('departure_schedule_id')->constrained('departure_schedules')->onDelete('cascade');
            $table->foreignId('transaction_category_id')->constrained('transaction_categories')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->text('note')->nullable();
            $table->timestamps();
            
            // Index for filtering and performance
            $table->index(['payment_date', 'pilgrim_id']);
            $table->index(['departure_schedule_id', 'payment_date']);
            $table->index(['transaction_category_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income_transactions');
    }
};