<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use Illuminate\Support\Facades\DB;

class FinancialSummaryWidget extends BaseWidget
{
    public ?string $selectedYear = null;

    protected function getStats(): array
    {
        $year = $this->selectedYear ?? now()->year;
        
        // Calculate total income for the selected year
        $totalIncome = IncomeTransaction::whereYear('payment_date', $year)
            ->sum('amount');

        // Calculate total expenses for the selected year
        $totalExpenses = ExpenseTransaction::whereYear('date', $year)
            ->sum('amount');

        // Calculate balance
        $balance = $totalIncome - $totalExpenses;

        // Get previous year data for comparison
        $previousYear = $year - 1;
        $previousIncome = IncomeTransaction::whereYear('payment_date', $previousYear)
            ->sum('amount');
        $previousExpenses = ExpenseTransaction::whereYear('date', $previousYear)
            ->sum('amount');
        $previousBalance = $previousIncome - $previousExpenses;

        // Calculate trends
        $incomeChange = $previousIncome > 0 ? (($totalIncome - $previousIncome) / $previousIncome) * 100 : 0;
        $expenseChange = $previousExpenses > 0 ? (($totalExpenses - $previousExpenses) / $previousExpenses) * 100 : 0;
        $balanceChange = $previousBalance != 0 ? (($balance - $previousBalance) / abs($previousBalance)) * 100 : 0;

        return [
            Stat::make('Total Income', 'Rp ' . number_format($totalIncome, 0, ',', '.'))
                ->description($this->formatTrend($incomeChange, 'vs previous year'))
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($incomeChange >= 0 ? 'success' : 'danger')
                ->chart($this->getMonthlyIncomeChart($year)),

            Stat::make('Total Expenses', 'Rp ' . number_format($totalExpenses, 0, ',', '.'))
                ->description($this->formatTrend($expenseChange, 'vs previous year'))
                ->descriptionIcon($expenseChange <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($expenseChange <= 0 ? 'success' : 'warning')
                ->chart($this->getMonthlyExpenseChart($year)),

            Stat::make('Net Balance', 'Rp ' . number_format($balance, 0, ',', '.'))
                ->description($this->formatTrend($balanceChange, 'vs previous year'))
                ->descriptionIcon($balanceChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($balance >= 0 ? 'success' : 'danger')
                ->chart($this->getMonthlyBalanceChart($year)),
        ];
    }

    protected function formatTrend(float $percentage, string $suffix): string
    {
        $formatted = number_format(abs($percentage), 1);
        $direction = $percentage >= 0 ? 'increase' : 'decrease';
        
        return "{$formatted}% {$direction} {$suffix}";
    }

    protected function getMonthlyIncomeChart(int $year): array
    {
        $monthlyData = IncomeTransaction::selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
            ->whereYear('payment_date', $year)
            ->groupBy(DB::raw('MONTH(payment_date)'))
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $chart = [];
        for ($i = 1; $i <= 12; $i++) {
            $chart[] = $monthlyData[$i] ?? 0;
        }

        return $chart;
    }

    protected function getMonthlyExpenseChart(int $year): array
    {
        $monthlyData = ExpenseTransaction::selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->whereYear('date', $year)
            ->groupBy(DB::raw('MONTH(date)'))
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $chart = [];
        for ($i = 1; $i <= 12; $i++) {
            $chart[] = $monthlyData[$i] ?? 0;
        }

        return $chart;
    }

    protected function getMonthlyBalanceChart(int $year): array
    {
        $incomeChart = $this->getMonthlyIncomeChart($year);
        $expenseChart = $this->getMonthlyExpenseChart($year);

        $balanceChart = [];
        for ($i = 0; $i < 12; $i++) {
            $balanceChart[] = $incomeChart[$i] - $expenseChart[$i];
        }

        return $balanceChart;
    }

    protected function getListeners(): array
    {
        return [
            'refreshFinancialData' => '$refresh',
        ];
    }
}