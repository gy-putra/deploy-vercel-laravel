<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinancialReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected Collection $data;
    protected string $reportType;

    public function __construct(Collection $data, string $reportType)
    {
        $this->data = $data;
        $this->reportType = $reportType;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        switch ($this->reportType) {
            case 'per_trip':
                return [
                    'Trip Name',
                    'Package Type',
                    'Departure Date',
                    'Pilgrim Count',
                    'Total Income (IDR)',
                    'Total Expenses (IDR)',
                    'Net Balance (IDR)',
                ];

            case 'monthly':
                return [
                    'Month',
                    'Year',
                    'Total Income (IDR)',
                    'Total Expenses (IDR)',
                    'Net Balance (IDR)',
                ];

            case 'annual':
                return [
                    'Year',
                    'Total Income (IDR)',
                    'Total Expenses (IDR)',
                    'Net Balance (IDR)',
                ];

            case 'outstanding':
                return [
                    'Pilgrim Name',
                    'NIK',
                    'Package Name',
                    'Registration Date',
                    'Package Price (IDR)',
                    'Total Paid (IDR)',
                    'Outstanding Amount (IDR)',
                    'Payment Status',
                ];

            default:
                return [];
        }
    }

    public function map($row): array
    {
        $row = (array) $row;
        
        switch ($this->reportType) {
            case 'per_trip':
                return [
                    $row['trip_name'] ?? '',
                    $row['package_type'] ?? '',
                    $row['departure_date'] ?? '',
                    $row['pilgrim_count'] ?? 0,
                    number_format($row['total_income'] ?? 0, 0, ',', '.'),
                    number_format($row['total_expenses'] ?? 0, 0, ',', '.'),
                    number_format($row['net_balance'] ?? 0, 0, ',', '.'),
                ];

            case 'monthly':
                return [
                    $row['month'] ?? '',
                    $row['year'] ?? '',
                    number_format($row['total_income'] ?? 0, 0, ',', '.'),
                    number_format($row['total_expenses'] ?? 0, 0, ',', '.'),
                    number_format($row['net_balance'] ?? 0, 0, ',', '.'),
                ];

            case 'annual':
                return [
                    $row['year'] ?? '',
                    number_format($row['total_income'] ?? 0, 0, ',', '.'),
                    number_format($row['total_expenses'] ?? 0, 0, ',', '.'),
                    number_format($row['net_balance'] ?? 0, 0, ',', '.'),
                ];

            case 'outstanding':
                return [
                    $row['pilgrim_name'] ?? '',
                    $row['nik'] ?? '',
                    $row['package_name'] ?? '',
                    $row['registration_date'] ?? '',
                    number_format($row['package_price'] ?? 0, 0, ',', '.'),
                    number_format($row['total_paid'] ?? 0, 0, ',', '.'),
                    number_format($row['outstanding_amount'] ?? 0, 0, ',', '.'),
                    $row['payment_status'] ?? '',
                ];

            default:
                return [];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '366092'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        $titles = [
            'per_trip' => 'Per Trip Financial Report',
            'monthly' => 'Monthly Financial Report',
            'annual' => 'Annual Financial Report',
            'outstanding' => 'Outstanding Payments Report',
        ];

        return $titles[$this->reportType] ?? 'Financial Report';
    }
}