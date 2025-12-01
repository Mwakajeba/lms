@extends('layouts.main')

@section('title', 'Cash Flow Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Cash Flow Report', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-money me-2"></i>Cash Flow Report</h5>
                                <small class="text-muted">Track cash flows from operating, investing, and financing activities</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="exportReport('pdf')">
                                    <i class="bx bx-file-pdf me-1"></i>Export PDF
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="exportReport('excel')">
                                    <i class="bx bx-file me-1"></i>Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section -->
                        <form id="cashFlowForm" method="GET" action="{{ route('accounting.reports.cash-flow') }}">
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

                                <!-- Branch (Assigned) -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        @if(($branches->count() ?? 0) > 1)
                                            <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All Branches</option>
                                        @endif
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Cash Flow Category -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="cash_flow_category_id" class="form-label">Cash Flow Category</label>
                                    <select class="form-select" id="cash_flow_category_id" name="cash_flow_category_id">
                                        <option value="">All Categories</option>
                                        @foreach($cashFlowCategories as $category)
                                            <option value="{{ $category->id }}" {{ $cashFlowCategoryId == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-search me-1"></i>Generate Report
                                    </button>
                                    <a href="{{ route('accounting.reports.cash-flow') }}" class="btn btn-secondary ms-2">
                                        <i class="bx bx-refresh me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <br>

                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h4 class="mb-1">{{ number_format($cashFlowData['opening_balance'], 2) }}</h4>
                                                <small>Opening Cash Balance</small>
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
                                                <h4 class="mb-1">{{ number_format($cashFlowData['overall_total'], 2) }}</h4>
                                                <small>Net Cash Flow</small>
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
                                                <h4 class="mb-1">{{ number_format($cashFlowData['closing_balance'], 2) }}</h4>
                                                <small>Closing Cash Balance</small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-wallet fs-1"></i>
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
                                                <h4 class="mb-1">{{ count($cashFlowData['grouped_data']) }}</h4>
                                                <small>Cash Flow Categories</small>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <i class="bx bx-category fs-1"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>

                        <!-- Cash Flow Data -->
                        @if(count($cashFlowData['grouped_data']) > 0)
                            @foreach($cashFlowData['grouped_data'] as $categoryName => $transactions)
                                <div class="card border-primary mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="bx bx-category me-2"></i>{{ $categoryName }}
                                            </h6>
                                            <div>
                                                <span class="badge bg-light text-dark">
                                                    Net Flow: {{ number_format($cashFlowData['category_totals'][$categoryName]['net_change'], 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Account</th>
                                                        <th>Description</th>
                                                        <th class="text-center">Nature</th>
                                                        <th class="text-end">Amount</th>
                                                        <th class="text-end">Impact</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($transactions as $transaction)
                                                        <tr>
                                                            <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</td>
                                                            <td>
                                                                <div>
                                                                    <strong>{{ $transaction['account_name'] }}</strong>
                                                                </div>
                                                                <small class="text-muted">{{ $transaction['account_code'] }}</small>
                                                            </td>
                                                            <td>{{ $transaction['description'] ?: 'No description' }}</td>
                                                            <td class="text-center">
                                                                <span class="badge {{ $transaction['nature'] === 'credit' ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ ucfirst($transaction['nature']) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end">{{ number_format($transaction['amount'], 2) }}</td>
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
                                                                {{ number_format($cashFlowData['category_totals'][$categoryName]['credit_total'], 2) }} /
                                                                {{ number_format($cashFlowData['category_totals'][$categoryName]['debit_total'], 2) }}
                                                            </strong>
                                                        </td>
                                                        <td class="text-end">
                                                            <strong class="{{ $cashFlowData['category_totals'][$categoryName]['net_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ $cashFlowData['category_totals'][$categoryName]['net_change'] >= 0 ? '+' : '' }}{{ number_format($cashFlowData['category_totals'][$categoryName]['net_change'], 2) }}
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
                                <i class="bx bx-money fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">No Cash Flow Data Found</h5>
                                <p class="text-muted">No cash flow transactions found for the selected period and filters.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport(type) {
    const form = document.getElementById('cashFlowForm');
    const exportTypeInput = document.createElement('input');
    exportTypeInput.type = 'hidden';
    exportTypeInput.name = 'export_type';
    exportTypeInput.value = type;
    form.appendChild(exportTypeInput);
    
    form.action = "{{ route('accounting.reports.cash-flow.export') }}";
    form.submit();
    
    // Remove the input after submission
    form.removeChild(exportTypeInput);
    form.action = "{{ route('accounting.reports.cash-flow') }}";
}
</script>
@endsection 