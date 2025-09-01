<?php

namespace App\Services;

use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use App\Models\DepartureSchedule;
use App\Models\Pilgrim;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class FinancialReportService
{
    public function getPerTripReport(?int $departureScheduleId = null, ?int $year = null): Collection
    {
        $query = DepartureSchedule::with(['package']);

        if ($departureScheduleId) {
            $query->where('id', $departureScheduleId);
        }

        if ($year) {
            $query->whereYear('departure_date', $year);
        }

        $schedules = $query->get();

        return $schedules->map(function ($schedule) {
            $income = IncomeTransaction::where('departure_schedule_id', $schedule->id)->sum('amount');
            $expenses = ExpenseTransaction::where('departure_schedule_id', $schedule->id)->sum('amount');
            $balance = $income - $expenses;

            $pilgrimCount = IncomeTransaction::where('departure_schedule_id', $schedule->id)
                ->distinct('pilgrim_id')
                ->count();

            return [
                'trip_name' => $schedule->package ? $schedule->package->name : 'Unknown Package',
                'departure_date' => $schedule->departure_date->format('Y-m-d'),
                'pilgrim_count' => $pilgrimCount,
                'total_income' => $income,
                'total_expenses' => $expenses,
                'net_balance' => $balance,
                'package_type' => ucfirst(str_replace('_', ' ', $schedule->package_type)),
            ];
        });
    }

    public function getMonthlyReport(?int $year = null): Collection
    {
        $year = $year ?? now()->year;

        // Get monthly income data
        $monthlyIncome = IncomeTransaction::selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
            ->whereYear('payment_date', $year)
            ->groupBy(DB::raw('MONTH(payment_date)'))
            ->pluck('total', 'month');

        // Get monthly expense data
        $monthlyExpenses = ExpenseTransaction::selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->whereYear('date', $year)
            ->groupBy(DB::raw('MONTH(date)'))
            ->pluck('total', 'month');

        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $data = collect();
        
        foreach ($months as $monthNum => $monthName) {
            $income = $monthlyIncome->get($monthNum, 0);
            $expenses = $monthlyExpenses->get($monthNum, 0);
            
            $data->push([
                'month' => $monthName,
                'year' => $year,
                'total_income' => $income,
                'total_expenses' => $expenses,
                'net_balance' => $income - $expenses,
            ]);
        }

        return $data;
    }

    public function getAnnualReport(?int $startYear = null, ?int $endYear = null): Collection
    {
        $startYear = $startYear ?? (now()->year - 4);
        $endYear = $endYear ?? now()->year;

        $data = collect();

        for ($year = $startYear; $year <= $endYear; $year++) {
            $income = IncomeTransaction::whereYear('payment_date', $year)->sum('amount');
            $expenses = ExpenseTransaction::whereYear('date', $year)->sum('amount');

            $data->push([
                'year' => $year,
                'total_income' => $income,
                'total_expenses' => $expenses,
                'net_balance' => $income - $expenses,
            ]);
        }

        return $data;
    }

    public function getOutstandingReport(): Collection
    {
        // Get pilgrims with their total payments and package information
        $pilgrims = Pilgrim::with(['documents'])
            ->select('pilgrims.*')
            ->get();

        $data = collect();

        foreach ($pilgrims as $pilgrim) {
            // Calculate total payments made by this pilgrim
            $totalPaid = IncomeTransaction::where('pilgrim_id', $pilgrim->id)->sum('amount');

            // For outstanding calculation, we'll use a base package price
            // This should be adjusted based on your business logic for package pricing
            $packagePrice = $this->getPackagePrice($pilgrim->package_name);
            $outstanding = $packagePrice - $totalPaid;

            // Only include pilgrims with outstanding amounts
            if ($outstanding > 0) {
                $data->push([
                    'pilgrim_name' => $pilgrim->name,
                    'nik' => $pilgrim->nik,
                    'package_name' => $pilgrim->package_name,
                    'registration_date' => $pilgrim->registration_date->format('Y-m-d'),
                    'package_price' => $packagePrice,
                    'total_paid' => $totalPaid,
                    'outstanding_amount' => $outstanding,
                    'payment_status' => $pilgrim->payment_status,
                ]);
            }
        }

        return $data->sortByDesc('outstanding_amount');
    }

    private function getPackagePrice(string $packageName): float
    {
        // This is a simplified approach - in a real application,
        // you would have package prices stored in the database
        $packagePrices = [
            'UMROH PROMO' => 25000000,
            'UMROH REGULER' => 35000000,
            'UMROH VIP' => 45000000,
            'HALAL TOUR TURKI' => 20000000,
            'HALAL TOUR MALAYSIA' => 15000000,
        ];

        return $packagePrices[$packageName] ?? 30000000; // Default price
    }

    public function getTotals(Collection $data, string $reportType): array
    {
        switch ($reportType) {
            case 'per_trip':
            case 'monthly':
            case 'annual':
                return [
                    'total_income' => $data->sum('total_income'),
                    'total_expenses' => $data->sum('total_expenses'),
                    'net_balance' => $data->sum('net_balance'),
                ];
            
            case 'outstanding':
                return [
                    'total_package_value' => $data->sum('package_price'),
                    'total_paid' => $data->sum('total_paid'),
                    'total_outstanding' => $data->sum('outstanding_amount'),
                    'pilgrim_count' => $data->count(),
                ];
            
            default:
                return [];
        }
    }

    public function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}