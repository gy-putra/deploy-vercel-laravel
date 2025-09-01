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
        Schema::create('expense_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departure_schedule_id')->nullable()->constrained('departure_schedules')->onDelete('cascade');
            $table->foreignId('transaction_category_id')->constrained('transaction_categories')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->text('description');
            $table->date('date');
            $table->timestamps();
            
            // Indexes for filtering and performance
            $table->index(['date', 'transaction_category_id']);
            $table->index(['departure_schedule_id', 'date']);
            $table->index(['transaction_category_id', 'date']);
            $table->index(['payment_method_id', 'date']);
            $table->index(['account_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_transactions');
    }
};