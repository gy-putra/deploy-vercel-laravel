<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IncomeTransaction;
use App\Models\Pilgrim;
use App\Models\DepartureSchedule;
use App\Models\TransactionCategory;
use App\Models\PaymentMethod;
use App\Models\Account;
use Carbon\Carbon;

class IncomeTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing records
        $pilgrims = Pilgrim::all();
        $schedules = DepartureSchedule::where('is_active', true)->get();
        $incomeCategories = TransactionCategory::where('type', 'income')->get();
        $paymentMethods = PaymentMethod::all();
        $accounts = Account::all();

        // Only proceed if we have the necessary data
        if ($pilgrims->isEmpty() || $schedules->isEmpty() || $incomeCategories->isEmpty() || 
            $paymentMethods->isEmpty() || $accounts->isEmpty()) {
            $this->command->info('Insufficient master data to create income transactions. Please ensure you have:');
            $this->command->info('- Pilgrims data');
            $this->command->info('- Active departure schedules');
            $this->command->info('- Income transaction categories');
            $this->command->info('- Payment methods');
            $this->command->info('- Accounts');
            return;
        }

        // Create sample income transactions
        $transactions = [
            [
                'pilgrim_id' => $pilgrims->random()->id,
                'departure_schedule_id' => $schedules->random()->id,
                'transaction_category_id' => $incomeCategories->random()->id,
                'payment_method_id' => $paymentMethods->where('method_name', 'Bank Transfer')->first()?->id ?? $paymentMethods->random()->id,
                'account_id' => $accounts->where('account_type', 'Bank')->first()?->id ?? $accounts->random()->id,
                'amount' => 5000000.00, // Down payment
                'payment_date' => Carbon::now()->subDays(30),
                'note' => 'Down payment for Umrah package registration'
            ],
            [
                'pilgrim_id' => $pilgrims->random()->id,
                'departure_schedule_id' => $schedules->random()->id,
                'transaction_category_id' => $incomeCategories->random()->id,
                'payment_method_id' => $paymentMethods->where('method_name', 'Cash')->first()?->id ?? $paymentMethods->random()->id,
                'account_id' => $accounts->where('account_type', 'Cash')->first()?->id ?? $accounts->random()->id,
                'amount' => 2500000.00, // Installment
                'payment_date' => Carbon::now()->subDays(15),
                'note' => 'First installment payment'
            ],
            [
                'pilgrim_id' => $pilgrims->random()->id,
                'departure_schedule_id' => $schedules->random()->id,
                'transaction_category_id' => $incomeCategories->random()->id,
                'payment_method_id' => $paymentMethods->where('method_name', 'Bank Transfer')->first()?->id ?? $paymentMethods->random()->id,
                'account_id' => $accounts->where('account_type', 'Bank')->first()?->id ?? $accounts->random()->id,
                'amount' => 15000000.00, // Full payment
                'payment_date' => Carbon::now()->subDays(7),
                'note' => 'Full package payment'
            ],
            [
                'pilgrim_id' => $pilgrims->random()->id,
                'departure_schedule_id' => $schedules->random()->id,
                'transaction_category_id' => $incomeCategories->random()->id,
                'payment_method_id' => $paymentMethods->where('method_name', 'Virtual Account')->first()?->id ?? $paymentMethods->random()->id,
                'account_id' => $accounts->where('account_type', 'Bank')->first()?->id ?? $accounts->random()->id,
                'amount' => 1000000.00, // Registration fee
                'payment_date' => Carbon::now()->subDays(3),
                'note' => 'Registration fee payment'
            ],
            [
                'pilgrim_id' => $pilgrims->random()->id,
                'departure_schedule_id' => $schedules->random()->id,
                'transaction_category_id' => $incomeCategories->random()->id,
                'payment_method_id' => $paymentMethods->where('method_name', 'Credit Card')->first()?->id ?? $paymentMethods->random()->id,
                'account_id' => $accounts->where('account_type', 'Bank')->first()?->id ?? $accounts->random()->id,
                'amount' => 3000000.00, // Second installment
                'payment_date' => Carbon::now()->subDays(1),
                'note' => 'Second installment via credit card'
            ],
        ];

        foreach ($transactions as $transaction) {
            IncomeTransaction::create($transaction);
        }

        $this->command->info('Created ' . count($transactions) . ' sample income transactions.');
    }
}