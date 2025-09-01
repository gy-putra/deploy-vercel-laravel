<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use Illuminate\Support\Facades\DB;

class CashFlowChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    public ?string $selectedYear = null;

    public function getHeading(): ?string
    {
        return 'Monthly Cash Flow';
    }

    protected function getData(): array
    {
        $year = $this->selectedYear ?? now()->year;

        // Get monthly income data
        $monthlyIncome = IncomeTransaction::selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
            ->whereYear('payment_date', $year)
            ->groupBy(DB::raw('MONTH(payment_date)'))
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Get monthly expense data
        $monthlyExpenses = ExpenseTransaction::selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->whereYear('date', $year)
            ->groupBy(DB::raw('MONTH(date)'))
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Prepare data arrays for all 12 months
        $incomeData = [];
        $expenseData = [];
        $netFlowData = [];

        for ($i = 1; $i <= 12; $i++) {
            $income = $monthlyIncome[$i] ?? 0;
            $expense = $monthlyExpenses[$i] ?? 0;
            
            $incomeData[] = $income;
            $expenseData[] = $expense;
            $netFlowData[] = $income - $expense;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $incomeData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $expenseData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Net Flow',
                    'data' => $netFlowData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'label' => "function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            return label;
                        }"
                    ],
                ],
            ],
            'responsive' => true,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Month',
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Amount (IDR)',
                    ],
                    'ticks' => [
                        'callback' => "function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }"
                    ],
                ],
            ],
            'elements' => [
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 6,
                ],
            ],
        ];
    }

    protected function getListeners(): array
    {
        return [
            'refreshFinancialData' => '$refresh',
        ];
    }
}