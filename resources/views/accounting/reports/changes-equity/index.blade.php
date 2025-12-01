@extends('layouts.main')

@section('title', 'Changes in Equity Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Changes in Equity Report', 'url' => '#', 'icon' => 'bx bx-trending-up']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-trending-up me-2"></i>Changes in Equity Report</h5>
                                <small class="text-muted">Track changes in shareholders' equity over time</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" onclick="generateReport()">
                                    <i class="bx bx-refresh me-1"></i> Generate Report
                                </button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')">
                                            <i class="bx bx-file-pdf me-2"></i> Export PDF
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">
                                            <i class="bx bx-file me-2"></i> Export Excel
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section -->
                        <form id="changesEquityForm" method="GET" action="{{ route('accounting.reports.changes-equity') }}">
                            <div class="row">
                                <!-- From Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="from_date" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="from_date" name="from_date" 
                                           value="{{ $fromDate }}" required>
                                </div>

                                <!-- To Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="to_date" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="to_date" name="to_date" 
                                           value="{{ $toDate }}" required>
                                </div>

                                <!-- Branch (Admin Only) -->
                                @if($user->hasRole('admin'))
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All Branches</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- Equity Category -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="equity_category_id" class="form-label">Equity Category</label>
                                    <select class="form-select" id="equity_category_id" name="equity_category_id">
                                        <option value="">All Categories</option>
                                        @foreach($equityCategories as $category)
                                            <option value="{{ $category->id }}" {{ $equityCategoryId == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h4 class="mb-1">{{ number_format($changesEquityData['opening_balance'], 2) }}</h4>
                                                <small>Opening Balance</small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-dollar-circle fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h4 class="mb-1">{{ number_format($changesEquityData['overall_total'], 2) }}</h4>
                                                <small>Net Change</small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-trending-up fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h4 class="mb-1">{{ number_format($changesEquityData['closing_balance'], 2) }}</h4>
                                                <small>Closing Balance</small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-calculator fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h4 class="mb-1">{{ count($changesEquityData['grouped_data']) }}</h4>
                                                <small>Categories</small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-category fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Report Content -->
                        @if(count($changesEquityData['grouped_data']) > 0)
                            @foreach($changesEquityData['grouped_data'] as $categoryName => $transactions)
                                <div class="card border-primary mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="bx bx-category me-2"></i>{{ $categoryName }}
                                            </h6>
                                            <div>
                                                <span class="badge bg-light text-dark">
                                                    Net Change: {{ number_format($changesEquityData['category_totals'][$categoryName]['net_change'], 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Account</th>
                                                        <th>Description</th>
                                                        <th class="text-end">Nature</th>
                                                        <th class="text-end">Amount</th>
                                                        <th class="text-end">Impact</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($transactions as $transaction)
                                                        <tr>
                                                            <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</td>
                                                            <td>
                                                                <strong>{{ $transaction['account_name'] }}</strong>
                                                                <br><small class="text-muted">{{ $transaction['account_code'] }}</small>
                                                            </td>
                                                            <td>{{ $transaction['description'] ?: 'No description' }}</td>
                                                            <td class="text-end">
                                                                <span class="badge {{ $transaction['nature'] === 'credit' ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ ucfirst($transaction['nature']) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="fw-bold">
                                                                    {{ number_format($transaction['amount'], 2) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="fw-bold {{ $transaction['impact'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                    {{ $transaction['impact'] >= 0 ? '+' : '' }}{{ number_format($transaction['impact'], 2) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-primary">
                                                        <td colspan="4"><strong>Total for {{ $categoryName }}</strong></td>
                                                        <td class="text-end">
                                                            <strong>
                                                                {{ number_format($changesEquityData['category_totals'][$categoryName]['credit_total'], 2) }} /
                                                                {{ number_format($changesEquityData['category_totals'][$categoryName]['debit_total'], 2) }}
                                                            </strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong class="{{ $changesEquityData['category_totals'][$categoryName]['net_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ $changesEquityData['category_totals'][$categoryName]['net_change'] >= 0 ? '+' : '' }}{{ number_format($changesEquityData['category_totals'][$categoryName]['net_change'], 2) }}
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-info-circle fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">No Changes in Equity Found</h5>
                                <p class="text-muted">No equity transactions found for the selected period and filters.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateReport() {
    document.getElementById('changesEquityForm').submit();
}

function exportReport(type) {
    const form = document.getElementById('changesEquityForm');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    // Create a temporary form for export
    const exportForm = document.createElement('form');
    exportForm.method = 'POST';
    exportForm.action = '{{ route("accounting.reports.changes-equity.export") }}';
    
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    exportForm.appendChild(csrfToken);
    
    // Add form data
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        exportForm.appendChild(input);
    }
    
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
}

// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const filterInputs = document.querySelectorAll('#changesEquityForm select, #changesEquityForm input[type="date"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            generateReport();
        });
    });
});
</script>
@endsection 