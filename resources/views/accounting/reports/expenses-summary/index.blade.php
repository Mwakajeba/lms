@extends('layouts.main')

@section('title', 'Expenses Summary Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Expenses Summary', 'url' => '#', 'icon' => 'bx bx-dollar-circle']
        ]" />

        <h6 class="mb-0 text-uppercase">EXPENSES SUMMARY REPORT</h6>
        <hr />

        <!-- Filters Card -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-filter me-2"></i>Report Filters</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('accounting.reports.expenses-summary') }}" id="expensesReportForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Reporting Type</label>
                                    <select class="form-select" name="reporting_type">
                                        <option value="accrual" {{ $reportingType === 'accrual' ? 'selected' : '' }}>Accrual</option>
                                        <option value="cash" {{ $reportingType === 'cash' ? 'selected' : '' }}>Cash</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Branch</label>
                                    <select class="form-select" name="branch_id">
                                        @if(($branches->count() ?? 0) > 1)
                                            <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All My Branches</option>
                                        @endif
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Group By</label>
                                    <select class="form-select" name="group_by">
                                        <option value="account" {{ $groupBy === 'account' ? 'selected' : '' }}>Account</option>
                                        <option value="group" {{ $groupBy === 'group' ? 'selected' : '' }}>Account Group</option>
                                        <option value="date" {{ $groupBy === 'date' ? 'selected' : '' }}>Date</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-2">
                                    <label class="form-label">Sort By</label>
                                    <select class="form-select" name="sort_by">
                                        <option value="amount" {{ $sortBy === 'amount' ? 'selected' : '' }}>Amount</option>
                                        <option value="account" {{ $sortBy === 'account' ? 'selected' : '' }}>Account</option>
                                        <option value="date" {{ $sortBy === 'date' ? 'selected' : '' }}>Date</option>
                                    </select>
                                </div>
                                <div class="col-md-10 d-flex align-items-end">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-search me-1"></i> Generate Report
                                        </button>
                                        <button type="button" class="btn btn-success" onclick="exportReport('excel')">
                                            <i class="bx bx-file me-1"></i> Export Excel
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">
                                            <i class="bx bx-file-pdf me-1"></i> Export PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Comparative Columns Section -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Comparative Columns</h6>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addComparative()">
                                            <i class="bx bx-plus me-1"></i> Add Comparative Column
                                        </button>
                                    </div>
                                    <div id="comparatives_container">
                                        @if(!empty($comparativeColumns))
                                            @foreach($comparativeColumns as $idx => $col)
                                                <div class="row g-2 align-items-end mb-2 comparative-row">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" class="form-control" name="comparative_columns[{{ $idx }}][name]" value="{{ $col['name'] ?? ('Comparative '.($idx+1)) }}" placeholder="e.g. Previous Period">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Start Date</label>
                                                        <input type="date" class="form-control" name="comparative_columns[{{ $idx }}][start_date]" value="{{ $col['start_date'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">End Date</label>
                                                        <input type="date" class="form-control" name="comparative_columns[{{ $idx }}][end_date]" value="{{ $col['end_date'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-3 text-end">
                                                        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.comparative-row').remove()">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row row-cols-1 row-cols-lg-4">
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Expenses</p>
                                <h4 class="font-weight-bold text-danger">{{ number_format($expensesData['summary']['total_expenses'], 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-danger text-white">
                                <i class='bx bx-dollar-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Transactions</p>
                                <h4 class="font-weight-bold text-info">{{ number_format($expensesData['summary']['total_transactions']) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-info text-white">
                                <i class='bx bx-transfer'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Account Count</p>
                                <h4 class="font-weight-bold text-success">{{ number_format($expensesData['summary']['account_count']) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white">
                                <i class='bx bx-book-open'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Avg per Transaction</p>
                                <h4 class="font-weight-bold text-warning">{{ number_format($expensesData['summary']['average_per_transaction'], 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-warning text-white">
                                <i class='bx bx-calculator'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Period Info -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-info-circle me-2"></i>
                        <div>
                            <strong>Report Period:</strong> {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }} | 
                            <strong>Reporting Type:</strong> {{ ucfirst($reportingType) }} | 
                            <strong>Branch:</strong> {{ $branchId === 'all' ? 'All Branches' : collect($branches)->where('id', $branchId)->first()['name'] ?? 'N/A' }} |
                            <strong>Grouped By:</strong> {{ ucfirst($groupBy) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Expenses Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="expensesTable" class="table table-striped table-hover">
                                <thead class="table-light">
                                    @if($groupBy === 'group')
                                        <tr>
                                            <th>Account Group</th>
                                            <th class="text-end">Total Debit</th>
                                            <th class="text-end">Total Credit</th>
                                            <th class="text-end">Net Amount</th>
                                            @if(!empty($expensesData['comparative']))
                                                @foreach($expensesData['comparative'] as $columnName => $compData)
                                                    <th class="text-end">{{ $columnName }} Amount</th>
                                                @endforeach
                                            @endif
                                            <th class="text-center">Account Count</th>
                                            <th class="text-center">Transaction Count</th>
                                        </tr>
                                    @else
                                        <tr>
                                            <th>Date</th>
                                            <th>Account Code</th>
                                            <th>Account Name</th>
                                            <th>Account Group</th>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                            @if(!empty($expensesData['comparative']))
                                                @foreach($expensesData['comparative'] as $columnName => $compData)
                                                    <th class="text-end">{{ $columnName }} Amount</th>
                                                @endforeach
                                            @endif
                                        </tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @forelse($expensesData['expenses'] as $expense)
                                        <tr>
                                            @if($groupBy === 'group')
                                                <td>{{ $expense->group_name }}</td>
                                                <td class="text-end">{{ number_format($expense->total_debit, 2) }}</td>
                                                <td class="text-end">{{ number_format($expense->total_credit, 2) }}</td>
                                                <td class="text-end fw-bold">
                                                    {{ number_format($expense->net_amount, 2) }}
                                                </td>
                                                @if(!empty($expensesData['comparative']))
                                                    @foreach($expensesData['comparative'] as $columnName => $compData)
                                                        @php
                                                            $compGroup = collect($compData['expenses'])->firstWhere('group_name', $expense->group_name);
                                                            $compAmount = $compGroup ? $compGroup->net_amount : 0;
                                                        @endphp
                                                        <td class="text-end">{{ number_format($compAmount, 2) }}</td>
                                                    @endforeach
                                                @endif
                                                <td class="text-center">{{ $expense->account_count }}</td>
                                                <td class="text-center">{{ $expense->transaction_count }}</td>
                                            @else
                                                <td>{{ Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                                                <td>{{ $expense->account_code }}</td>
                                                <td>{{ $expense->account_name }}</td>
                                                <td>{{ $expense->group_name }}</td>
                                                <td>{{ Str::limit($expense->description, 50) }}</td>
                                                <td class="text-end fw-bold">
                                                    {{ number_format($expense->amount, 2) }}
                                                </td>
                                                @if(!empty($expensesData['comparative']))
                                                    @foreach($expensesData['comparative'] as $columnName => $compData)
                                                        @php
                                                            $compTransaction = collect($compData['expenses'])->firstWhere('transaction_id', $expense->transaction_id);
                                                            $compAmount = $compTransaction ? $compTransaction->amount : 0;
                                                        @endphp
                                                        <td class="text-end">{{ number_format($compAmount, 2) }}</td>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $groupBy === 'group' ? (6 + count($expensesData['comparative'] ?? [])) : (6 + count($expensesData['comparative'] ?? [])) }}" class="text-center text-muted py-4">
                                                <i class="bx bx-info-circle me-2"></i>No expenses found for the selected criteria
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Summary -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Additional Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="alert alert-success mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-dollar-circle me-2"></i>
                                        <div>
                                            <strong>Average per Account:</strong><br>
                                            {{ number_format($expensesData['summary']['average_per_account'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-info mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-transfer me-2"></i>
                                        <div>
                                            <strong>Total Transactions:</strong><br>
                                            {{ number_format($expensesData['summary']['total_transactions']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-book-open me-2"></i>
                                        <div>
                                            <strong>Total Accounts:</strong><br>
                                            {{ number_format($expensesData['summary']['account_count']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="alert alert-danger mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-trending-up me-2"></i>
                                        <div>
                                            <strong>Total Expenses:</strong><br>
                                            {{ number_format($expensesData['summary']['total_expenses'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#expensesTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[0, 'desc']], // Sort by date descending by default
        columnDefs: [
            @if($groupBy === 'group')
            {
                targets: [1, 2, 3], // Total Debit, Total Credit, Net Amount columns
                className: 'text-end'
            },
            {
                targets: [4, 5], // Account Count, Transaction Count columns
                className: 'text-center'
            }
            @else
            {
                targets: [5], // Amount column
                className: 'text-end'
            },
            {
                targets: [4], // Description column
                className: 'text-start'
            }
            @endif
        ],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            search: "Search transactions:",
            lengthMenu: "Show _MENU_ transactions per page",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });
});

function exportReport(type) {
    // Get current form data
    const form = document.getElementById('expensesReportForm');
    const formData = new FormData(form);
    
    // Add export type
    formData.append('export_type', type);
    
    // Create export URL
    const params = new URLSearchParams(formData);
    const exportUrl = '{{ route("accounting.reports.expenses-summary.export") }}?' + params.toString();
    
    // Show loading state
    Swal.fire({
        title: 'Generating Report...',
        text: 'Please wait while we prepare your ' + type.toUpperCase() + ' report',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Download the file
    window.location.href = exportUrl;
    
    // Close loading after a delay
    setTimeout(() => {
        Swal.close();
    }, 2000);
}

function addComparative() {
    const container = document.getElementById('comparatives_container');
    const idx = container.querySelectorAll('.comparative-row').length;
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-end mb-2 comparative-row';
    row.innerHTML = `
        <div class="col-md-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="comparative_columns[${idx}][name]" value="Comparative ${idx + 1}" placeholder="e.g. Previous Period">
        </div>
        <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" name="comparative_columns[${idx}][start_date]">
        </div>
        <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" name="comparative_columns[${idx}][end_date]">
        </div>
        <div class="col-md-3 text-end">
            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.comparative-row').remove()">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
}
</script>
@endsection 