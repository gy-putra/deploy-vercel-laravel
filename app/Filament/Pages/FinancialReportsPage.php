<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Action;
use App\Services\FinancialReportService;
use App\Models\DepartureSchedule;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use BackedEnum;
use UnitEnum;

class FinancialReportsPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public ?array $data = [];

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static UnitEnum|string|null $navigationGroup = 'Financial Management';

    protected static ?string $navigationLabel = 'Financial Reports';

    protected static ?int $navigationSort = 8;

    protected static ?string $title = 'Financial Reports';

    public ?string $reportType = 'per_trip';
    public ?int $departureScheduleId = null;
    public ?int $year = null;
    public ?int $startYear = null;
    public ?int $endYear = null;

    protected FinancialReportService $reportService;
    protected ?Collection $reportData = null;

    public function getReportService(): FinancialReportService
    {
        if (!isset($this->reportService)) {
            $this->reportService = new FinancialReportService();
        }
        return $this->reportService;
    }

    public function getView(): string
    {
        return 'filament.pages.financial-reports-page';
    }

    public function mount(): void
    {
        // Initialize service
        $this->getReportService();
        
        $this->year = now()->year;
        $this->startYear = now()->year - 4;
        $this->endYear = now()->year;
        
        $this->form->fill([
            'reportType' => 'per_trip',
            'year' => now()->year,
            'startYear' => now()->year - 4,
            'endYear' => now()->year,
        ]);
        
        $this->generateReport();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Report Configuration')
                    ->schema([
                        Forms\Components\Radio::make('reportType')
                            ->label('Report Type')
                            ->options([
                                'per_trip' => 'Per Trip Report',
                                'monthly' => 'Monthly Report',
                                'annual' => 'Annual Report',
                                'outstanding' => 'Outstanding Report',
                            ])
                            ->default('per_trip')
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->reportType = $state;
                                $this->generateReport();
                            })
                            ->columnSpanFull(),

                        Forms\Components\Select::make('departureScheduleId')
                            ->label('Select Trip')
                            ->options(function () {
                                return DepartureSchedule::with('package')
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($schedule) {
                                        $packageName = $schedule->package ? $schedule->package->name : 'Unknown Package';
                                        $packageType = ucfirst(str_replace('_', ' ', $schedule->package_type));
                                        $label = "{$packageType}: {$packageName} - {$schedule->departure_date->format('Y-m-d')}";
                                        return [$schedule->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->placeholder('All Trips')
                            ->visible(fn ($get) => $get('reportType') === 'per_trip')
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->departureScheduleId = $state;
                                $this->generateReport();
                            }),

                        Forms\Components\Select::make('year')
                            ->label('Year')
                            ->options(function () {
                                $years = [];
                                for ($year = now()->year; $year >= (now()->year - 10); $year--) {
                                    $years[$year] = (string) $year;
                                }
                                return $years;
                            })
                            ->default(now()->year)
                            ->visible(fn ($get) => in_array($get('reportType'), ['per_trip', 'monthly']))
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->year = $state;
                                $this->generateReport();
                            }),

                        Forms\Components\Select::make('startYear')
                            ->label('Start Year')
                            ->options(function () {
                                $years = [];
                                for ($year = now()->year; $year >= (now()->year - 10); $year--) {
                                    $years[$year] = (string) $year;
                                }
                                return $years;
                            })
                            ->default(now()->year - 4)
                            ->visible(fn ($get) => $get('reportType') === 'annual')
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->startYear = $state;
                                $this->generateReport();
                            }),

                        Forms\Components\Select::make('endYear')
                            ->label('End Year')
                            ->options(function () {
                                $years = [];
                                for ($year = now()->year; $year >= (now()->year - 10); $year--) {
                                    $years[$year] = (string) $year;
                                }
                                return $years;
                            })
                            ->default(now()->year)
                            ->visible(fn ($get) => $get('reportType') === 'annual')
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->endYear = $state;
                                $this->generateReport();
                            }),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function generateReport(): void
    {
        switch ($this->reportType) {
            case 'per_trip':
                $this->reportData = $this->getReportService()->getPerTripReport($this->departureScheduleId, $this->year);
                break;
            case 'monthly':
                $this->reportData = $this->getReportService()->getMonthlyReport($this->year);
                break;
            case 'annual':
                $this->reportData = $this->getReportService()->getAnnualReport($this->startYear, $this->endYear);
                break;
            case 'outstanding':
                $this->reportData = $this->getReportService()->getOutstandingReport();
                break;
            default:
                $this->reportData = collect();
        }
    }

    public function table(Table $table): Table
    {
        $columns = $this->getTableColumns();
        
        return $table
            ->records(fn () => $this->reportData ?? collect())
            ->columns($columns)
            ->paginated(false);
    }

    protected function getTableColumns(): array
    {
        switch ($this->reportType) {
            case 'per_trip':
                return [
                    Tables\Columns\TextColumn::make('trip_name')
                        ->label('Trip Name')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('package_type')
                        ->label('Package Type')
                        ->badge(),
                    Tables\Columns\TextColumn::make('departure_date')
                        ->label('Departure Date')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('pilgrim_count')
                        ->label('Pilgrims')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('total_income')
                        ->label('Total Income')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->sortable(),
                    Tables\Columns\TextColumn::make('total_expenses')
                        ->label('Total Expenses')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->sortable(),
                    Tables\Columns\TextColumn::make('net_balance')
                        ->label('Net Balance')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                        ->sortable(),
                ];

            case 'monthly':
                return [
                    Tables\Columns\TextColumn::make('month')
                        ->label('Month')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('year')
                        ->label('Year')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('total_income')
                        ->label('Total Income')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->sortable(),
                    Tables\Columns\TextColumn::make('total_expenses')
                        ->label('Total Expenses')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->sortable(),
                    Tables\Columns\TextColumn::make('net_balance')
                        ->label('Net Balance')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                        ->sortable(),
                ];

            case 'annual':
                return [
                    Tables\Columns\TextColumn::make('year')
                        ->label('Year')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('total_income')
                        ->label('Total Income')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->sortable(),
                    Tables\Columns\TextColumn::make('total_expenses')
                        ->label('Total Expenses')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->sortable(),
                    Tables\Columns\TextColumn::make('net_balance')
                        ->label('Net Balance')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                        ->sortable(),
                ];

            case 'outstanding':
                return [
                    Tables\Columns\TextColumn::make('pilgrim_name')
                        ->label('Pilgrim Name')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('nik')
                        ->label('NIK')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('package_name')
                        ->label('Package')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('registration_date')
                        ->label('Registration Date')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('package_price')
                        ->label('Package Price')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->sortable(),
                    Tables\Columns\TextColumn::make('total_paid')
                        ->label('Total Paid')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->sortable(),
                    Tables\Columns\TextColumn::make('outstanding_amount')
                        ->label('Outstanding')
                        ->formatStateUsing(fn ($state) => $this->getReportService()->formatCurrency($state))
                        ->color('danger')
                        ->sortable(),
                    Tables\Columns\TextColumn::make('payment_status')
                        ->label('Status')
                        ->badge()
                        ->color(fn ($state) => match($state) {
                            'paid' => 'success',
                            'partial' => 'warning',
                            'pending' => 'danger',
                            default => 'gray'
                        }),
                ];

            default:
                return [];
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action('exportToPdf'),
            
            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->action('exportToExcel'),
        ];
    }

    public function exportToPdf()
    {
        $data = $this->reportData ?? collect();
        $totals = $this->getReportService()->getTotals($data, $this->reportType);
        
        $pdf = Pdf::loadView('reports.financial-report-pdf', [
            'reportType' => $this->reportType,
            'data' => $data,
            'totals' => $totals,
            'reportService' => $this->getReportService(),
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'financial-report-' . $this->reportType . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportToExcel()
    {
        $data = $this->reportData ?? collect();
        
        return Excel::download(new \App\Exports\FinancialReportExport($data, $this->reportType), 
            'financial-report-' . $this->reportType . '-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function getTotals(): array
    {
        $data = $this->reportData ?? collect();
        return $this->getReportService()->getTotals($data, $this->reportType);
    }
}