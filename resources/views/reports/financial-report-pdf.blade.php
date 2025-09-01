<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report - {{ ucwords(str_replace('_', ' ', $reportType)) }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            color: #666;
            margin: 5px 0;
        }
        
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .summary h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .summary-grid {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .summary-item {
            flex: 1;
            min-width: 200px;
            margin-right: 20px;
        }
        
        .summary-item:last-child {
            margin-right: 0;
        }
        
        .summary-label {
            font-weight: bold;
            color: #666;
            font-size: 11px;
        }
        
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 2px;
        }
        
        .summary-value.positive {
            color: #28a745;
        }
        
        .summary-value.negative {
            color: #dc3545;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        th {
            background-color: #366092;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ ucwords(str_replace('_', ' ', $reportType)) }} Report</h1>
        <p>Generated on {{ now()->format('d F Y H:i:s') }}</p>
    </div>

    @if(!empty($totals))
    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-grid">
            @if($reportType === 'outstanding')
                <div class="summary-item">
                    <div class="summary-label">Total Pilgrims</div>
                    <div class="summary-value">{{ $totals['pilgrim_count'] ?? 0 }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Paid</div>
                    <div class="summary-value positive">{{ $reportService->formatCurrency($totals['total_paid'] ?? 0) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Outstanding</div>
                    <div class="summary-value negative">{{ $reportService->formatCurrency($totals['total_outstanding'] ?? 0) }}</div>
                </div>
            @else
                <div class="summary-item">
                    <div class="summary-label">Total Income</div>
                    <div class="summary-value positive">{{ $reportService->formatCurrency($totals['total_income'] ?? 0) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Expenses</div>
                    <div class="summary-value negative">{{ $reportService->formatCurrency($totals['total_expenses'] ?? 0) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Net Balance</div>
                    <div class="summary-value {{ ($totals['net_balance'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ $reportService->formatCurrency($totals['net_balance'] ?? 0) }}
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif

    @if($data->isNotEmpty())
    <table>
        <thead>
            <tr>
                @if($reportType === 'per_trip')
                    <th>Trip Name</th>
                    <th>Package Type</th>
                    <th>Departure Date</th>
                    <th>Pilgrims</th>
                    <th class="text-right">Income</th>
                    <th class="text-right">Expenses</th>
                    <th class="text-right">Balance</th>
                @elseif($reportType === 'monthly')
                    <th>Month</th>
                    <th>Year</th>
                    <th class="text-right">Income</th>
                    <th class="text-right">Expenses</th>
                    <th class="text-right">Balance</th>
                @elseif($reportType === 'annual')
                    <th>Year</th>
                    <th class="text-right">Income</th>
                    <th class="text-right">Expenses</th>
                    <th class="text-right">Balance</th>
                @elseif($reportType === 'outstanding')
                    <th>Pilgrim Name</th>
                    <th>NIK</th>
                    <th>Package</th>
                    <th>Registration</th>
                    <th class="text-right">Package Price</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Outstanding</th>
                    <th class="text-center">Status</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                @if($reportType === 'per_trip')
                    <td>{{ $row['trip_name'] ?? '' }}</td>
                    <td><span class="badge badge-success">{{ $row['package_type'] ?? '' }}</span></td>
                    <td class="text-center">{{ $row['departure_date'] ?? '' }}</td>
                    <td class="text-center">{{ $row['pilgrim_count'] ?? 0 }}</td>
                    <td class="text-right">{{ $reportService->formatCurrency($row['total_income'] ?? 0) }}</td>
                    <td class="text-right">{{ $reportService->formatCurrency($row['total_expenses'] ?? 0) }}</td>
                    <td class="text-right {{ ($row['net_balance'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ $reportService->formatCurrency($row['net_balance'] ?? 0) }}
                    </td>
                @elseif($reportType === 'monthly')
                    <td>{{ $row['month'] ?? '' }}</td>
                    <td class="text-center">{{ $row['year'] ?? '' }}</td>
                    <td class="text-right">{{ $reportService->formatCurrency($row['total_income'] ?? 0) }}</td>
                    <td class="text-right">{{ $reportService->formatCurrency($row['total_expenses'] ?? 0) }}</td>
                    <td class="text-right {{ ($row['net_balance'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ $reportService->formatCurrency($row['net_balance'] ?? 0) }}
                    </td>
                @elseif($reportType === 'annual')
                    <td class="text-center">{{ $row['year'] ?? '' }}</td>
                    <td class="text-right">{{ $reportService->formatCurrency($row['total_income'] ?? 0) }}</td>
                    <td class="text-right">{{ $reportService->formatCurrency($row['total_expenses'] ?? 0) }}</td>
                    <td class="text-right {{ ($row['net_balance'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ $reportService->formatCurrency($row['net_balance'] ?? 0) }}
                    </td>
                @elseif($reportType === 'outstanding')
                    <td>{{ $row['pilgrim_name'] ?? '' }}</td>
                    <td>{{ $row['nik'] ?? '' }}</td>
                    <td>{{ $row['package_name'] ?? '' }}</td>
                    <td class="text-center">{{ $row['registration_date'] ?? '' }}</td>
                    <td class="text-right">{{ $reportService->formatCurrency($row['package_price'] ?? 0) }}</td>
                    <td class="text-right">{{ $reportService->formatCurrency($row['total_paid'] ?? 0) }}</td>
                    <td class="text-right negative">{{ $reportService->formatCurrency($row['outstanding_amount'] ?? 0) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $row['payment_status'] === 'paid' ? 'success' : ($row['payment_status'] === 'partial' ? 'warning' : 'danger') }}">
                            {{ ucfirst($row['payment_status'] ?? '') }}
                        </span>
                    </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 40px; color: #666;">
        <h3>No Data Available</h3>
        <p>No records found for the selected criteria.</p>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the Financial Management System</p>
        <p>© {{ now()->year }} Pilgrimage Management Dashboard</p>
    </div>
</body>
</html>