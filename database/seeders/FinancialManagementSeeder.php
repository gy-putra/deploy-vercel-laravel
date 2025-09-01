<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TransactionCategory;
use App\Models\PaymentMethod;
use App\Models\Account;

class FinancialManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Transaction Categories
        $categories = [
            ['category_name' => 'Package Sales', 'type' => 'income', 'description' => 'Revenue from selling Umrah and Halal tour packages'],
            ['category_name' => 'Registration Fees', 'type' => 'income', 'description' => 'Registration fees collected from pilgrims'],
            ['category_name' => 'Office Rent', 'type' => 'expense', 'description' => 'Monthly office rental expenses'],
            ['category_name' => 'Staff Salary', 'type' => 'expense', 'description' => 'Employee salary and benefits'],
            ['category_name' => 'Marketing', 'type' => 'expense', 'description' => 'Marketing and promotional expenses'],
        ];

        foreach ($categories as $category) {
            TransactionCategory::create($category);
        }

        // Create Payment Methods
        $paymentMethods = [
            ['method_name' => 'Cash'],
            ['method_name' => 'Bank Transfer'],
            ['method_name' => 'Credit Card'],
            ['method_name' => 'Virtual Account'],
            ['method_name' => 'E-Wallet'],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }

        // Create Accounts
        $accounts = [
            [
                'account_name' => 'BCA Main Account',
                'account_number' => '1234567890',
                'account_type' => 'Bank',
                'description' => 'Primary business account at Bank Central Asia'
            ],
            [
                'account_name' => 'Mandiri Operational',
                'account_number' => '9876543210',
                'account_type' => 'Bank',
                'description' => 'Operational account at Bank Mandiri'
            ],
            [
                'account_name' => 'Cash Register',
                'account_number' => null,
                'account_type' => 'Cash',
                'description' => 'Main cash register for office transactions'
            ],
            [
                'account_name' => 'Petty Cash',
                'account_number' => null,
                'account_type' => 'Cash',
                'description' => 'Small cash fund for minor expenses'
            ],
        ];

        foreach ($accounts as $account) {
            Account::create($account);
        }
    }
}