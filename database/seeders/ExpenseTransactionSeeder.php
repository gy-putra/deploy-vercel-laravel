<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseTransaction;
use App\Models\TransactionCategory;
use App\Models\PaymentMethod;
use App\Models\Account;

class ExpenseTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $categories = TransactionCategory::where('type', 'expense')->pluck('id')->toArray();
        $paymentMethods = PaymentMethod::pluck('id')->toArray();
        $accounts = Account::pluck('id')->toArray();

        if (!empty($categories) && !empty($paymentMethods) && !empty($accounts)) {
            for ($i = 1; $i <= 12; $i++) {
                ExpenseTransaction::create([
                    'transaction_category_id' => $categories[array_rand($categories)],
                    'amount' => rand(500000, 2000000),
                    'payment_method_id' => $paymentMethods[array_rand($paymentMethods)],
                    'account_id' => $accounts[array_rand($accounts)],
                    'description' => 'Sample expense for month ' . $i,
                    'date' => now()->month($i)->day(15),
                ]);
            }
        }
    }
}