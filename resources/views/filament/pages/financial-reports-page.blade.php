<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Report Configuration Form -->
        <div class="bg-white rounded-lg shadow p-6">
            {{ $this->form }}
        </div>

        <!-- Summary Totals -->
        @if($this->reportData && $this->reportData->isNotEmpty())
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Summary</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @php $totals = $this->getTotals(); @endphp
                    
                    @if($this->reportType === 'outstanding')
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-blue-600">Total Pilgrims</div>
                            <div class="text-2xl font-bold text-blue-900">{{ $totals['pilgrim_count'] ?? 0 }}</div>
                        </div>
                        
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-green-600">Total Paid</div>
                            <div class="text-2xl font-bold text-green-900">{{ $this->getReportService()->formatCurrency($totals['total_paid'] ?? 0) }}</div>
                        </div>
                        
                        <div class="bg-red-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-red-600">Total Outstanding</div>
                            <div class="text-2xl font-bold text-red-900">{{ $this->getReportService()->formatCurrency($totals['total_outstanding'] ?? 0) }}</div>
                        </div>
                    @else
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-green-600">Total Income</div>
                            <div class="text-2xl font-bold text-green-900">{{ $this->getReportService()->formatCurrency($totals['total_income'] ?? 0) }}</div>
                        </div>
                        
                        <div class="bg-red-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-red-600">Total Expenses</div>
                            <div class="text-2xl font-bold text-red-900">{{ $this->getReportService()->formatCurrency($totals['total_expenses'] ?? 0) }}</div>
                        </div>
                        
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-blue-600">Net Balance</div>
                            <div class="text-2xl font-bold {{ ($totals['net_balance'] ?? 0) >= 0 ? 'text-green-900' : 'text-red-900' }}">
                                {{ $this->getReportService()->formatCurrency($totals['net_balance'] ?? 0) }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Report Data Table -->
        <div class="bg-white rounded-lg shadow">
            @if($this->reportData && $this->reportData->isNotEmpty())
                {{ $this->table }}
            @else
                <div class="p-6 text-center text-gray-500">
                    <div class="text-lg">No data available</div>
                    <div class="text-sm mt-2">Try adjusting your filter criteria to see results.</div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>