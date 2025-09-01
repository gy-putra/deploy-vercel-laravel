<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use App\Filament\Widgets\FinancialSummaryWidget;
use App\Filament\Widgets\CashFlowChartWidget;
use BackedEnum;
use UnitEnum;

class BalanceAndCashFlowPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = null;

    protected static UnitEnum|string|null $navigationGroup = 'Financial Management';

    protected static ?string $navigationLabel = 'Balance & Cash Flow';

    protected static ?int $navigationSort = 7;

    protected static ?string $title = 'Balance & Cash Flow';

    public ?string $selectedYear = null;

    public function mount(): void
    {
        $this->selectedYear = (string) now()->year;
    }

    public function getView(): string
    {
        return 'filament.pages.balance-and-cash-flow-page';
    }

    protected function getYearOptions(): array
    {
        $currentYear = now()->year;
        $startYear = $currentYear - 5; // Show last 5 years
        $endYear = $currentYear + 1; // Include next year

        $years = [];
        for ($year = $endYear; $year >= $startYear; $year--) {
            $years[$year] = (string) $year;
        }

        return $years;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FinancialSummaryWidget::class,
        ];
    }

    protected function getWidgets(): array
    {
        return [
            CashFlowChartWidget::class,
        ];
    }

    public function getSelectedYear(): string
    {
        return $this->selectedYear ?? (string) now()->year;
    }

    protected function getViewData(): array
    {
        return [
            'selectedYear' => $this->getSelectedYear(),
        ];
    }
}