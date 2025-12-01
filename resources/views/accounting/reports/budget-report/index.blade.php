@extends('layouts.main')

@section('title', 'Budget Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Budget Report', 'url' => '#', 'icon' => 'bx bx-chart']
        ]" />
        
        <h6 class="mb-0 text-uppercase">Budget Report</h6>
        <hr />

        <!-- Filters -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light border-0">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bx bx-filter-alt me-2"></i>
                    Report Filters
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.reports.budget-report') }}" id="budgetReportForm">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="year" class="form-label fw-semibold">Year</label>
                            <select class="form-select" id="year" name="year">
                                @for($y = date('Y') + 2; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="budget_id" class="form-label fw-semibold">Budget</label>
                            <select class="form-select" id="budget_id" name="budget_id">
                                <option value="">All Budgets</option>
                                @foreach($availableBudgets as $budget)
                                    <option value="{{ $budget->id }}" {{ $budgetId == $budget->id ? 'selected' : '' }}>
                                        {{ $budget->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="branch_id" class="form-label fw-semibold">Branch</label>
                            <select class="form-select" id="branch_id" name="branch_id">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="account_class_id" class="form-label fw-semibold">Account Class</label>
                            <select class="form-select" id="account_class_id" name="account_class_id">
                                <option value="">All Classes</option>
                                @foreach($accountClasses as $class)
                                    <option value="{{ $class->id }}" {{ $accountClassId == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="account_group_id" class="form-label fw-semibold">Account Group</label>
                            <select class="form-select" id="account_group_id" name="account_group_id">
                                <option value="">All Groups</option>
                                @foreach($accountGroups as $group)
                                    <option value="{{ $group->id }}" {{ $accountGroupId == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-1">
                            <label for="category" class="form-label fw-semibold">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All</option>
                                <option value="Revenue" {{ $category == 'Revenue' ? 'selected' : '' }}>Revenue</option>
                                <option value="Expense" {{ $category == 'Expense' ? 'selected' : '' }}>Expense</option>
                                <option value="Capital Expenditure" {{ $category == 'Capital Expenditure' ? 'selected' : '' }}>Capital</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-md-2">
                            <label for="date_type" class="form-label fw-semibold">Date Type</label>
                            <select class="form-select" id="date_type" name="date_type">
                                <option value="custom" {{ $dateType == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                <option value="monthly" {{ $dateType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ $dateType == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="yearly" {{ $dateType == 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_from" class="form-label fw-semibold">Date From</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ $dateFrom }}" {{ $dateType != 'custom' ? 'readonly' : '' }}>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_to" class="form-label fw-semibold">Date To</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ $dateTo }}" {{ $dateType != 'custom' ? 'readonly' : '' }}>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Variance Filter</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show_only_over_budget" 
                                       name="show_only_over_budget" value="1" {{ $showOnlyOverBudget ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_only_over_budget">
                                    Over Budget Only
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show_only_under_budget" 
                                       name="show_only_under_budget" value="1" {{ $showOnlyUnderBudget ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_only_under_budget">
                                    Under Budget Only
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search"></i> Generate Report
                                </button>
                                <a href="{{ route('accounting.reports.budget-report') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-refresh"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <div class="d-grid">
                                <a href="{{ route('accounting.reports.budget-report.export') }}?{{ http_build_query(request()->all()) }}" 
                                   class="btn btn-success">
                                    <i class="bx bx-export"></i> Export Excel
                                </a>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <div class="d-grid">
                                <a href="{{ route('accounting.reports.budget-report.export-pdf') }}?{{ http_build_query(request()->all()) }}" 
                                   class="btn btn-danger">
                                    <i class="bx bx-file-pdf"></i> Export PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row row-cols-1 row-cols-lg-4 g-4 mb-4">
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-1 text-muted small">Total Budgeted</p>
                                <h3 class="mb-0 fw-bold text-primary">TZS {{ number_format($budgetData['summary']['total_budgeted'], 2) }}</h3>
                                <p class="mb-0 text-primary small">
                                    <i class="bx bx-target-lock"></i> Planned Amount
                                </p>
                            </div>
                            <div class="widgets-icons bg-gradient-primary text-white">
                                <i class='bx bx-target-lock'></i>
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
                                <p class="mb-1 text-muted small">Total Actual</p>
                                <h3 class="mb-0 fw-bold text-info">TZS {{ number_format($budgetData['summary']['total_actual'], 2) }}</h3>
                                <p class="mb-0 text-info small">
                                    <i class="bx bx-money"></i> Spent Amount
                                </p>
                            </div>
                            <div class="widgets-icons bg-gradient-info text-white">
                                <i class='bx bx-money'></i>
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
                                <p class="mb-1 text-muted small">Total Variance</p>
                                <h3 class="mb-0 fw-bold {{ $budgetData['summary']['total_variance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    TZS {{ number_format($budgetData['summary']['total_variance'], 2) }}
                                </h3>
                                <p class="mb-0 {{ $budgetData['summary']['total_variance'] >= 0 ? 'text-success' : 'text-danger' }} small">
                                    <i class="bx bx-trending-up"></i> {{ $budgetData['summary']['variance_percentage'] }}%
                                </p>
                            </div>
                            <div class="widgets-icons {{ $budgetData['summary']['total_variance'] >= 0 ? 'bg-gradient-success' : 'bg-gradient-danger' }} text-white">
                                <i class='bx bx-trending-up'></i>
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
                                <p class="mb-1 text-muted small">Accounts</p>
                                <h3 class="mb-0 fw-bold text-warning">{{ $budgetData['summary']['total_accounts'] }}</h3>
                                <p class="mb-0 text-warning small">
                                    <i class="bx bx-list-ul"></i> Budget Lines
                                </p>
                            </div>
                            <div class="widgets-icons bg-gradient-warning text-white">
                                <i class='bx bx-list-ul'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Status Cards -->
        <div class="row row-cols-1 row-cols-lg-3 g-4 mb-4">
            <div class="col">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="text-success mb-1">{{ $budgetData['summary']['under_budget_count'] }}</h4>
                        <p class="text-muted mb-0">Under Budget</p>
                    </div>
                </div>
            </div>
            
            <div class="col">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-x-circle text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="text-danger mb-1">{{ $budgetData['summary']['over_budget_count'] }}</h4>
                        <p class="text-muted mb-0">Over Budget</p>
                    </div>
                </div>
            </div>
            
            <div class="col">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-minus-circle text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="text-warning mb-1">{{ $budgetData['summary']['on_budget_count'] }}</h4>
                        <p class="text-muted mb-0">On Budget</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Report Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bx bx-table me-2"></i>
                        Budget vs Actual Report
                    </h6>
                    <span class="badge bg-primary rounded-pill">{{ $budgetData['summary']['total_accounts'] }} Accounts</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if(count($budgetData['items']) > 0)
                    <div class="table-responsive">
                        <table id="budgetReportTable" class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Account Class</th>
                                    <th>Account Group</th>
                                    <th>Category</th>
                                    <th class="text-end">Budgeted Amount</th>
                                    <th class="text-end">Actual Amount</th>
                                    <th class="text-end">Variance</th>
                                    <th class="text-end">Variance %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budgetData['items'] as $item)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ $item->account_code }}</span>
                                        </td>
                                        <td>{{ $item->account_name }}</td>
                                        <td>{{ $item->account_class }}</td>
                                        <td>{{ $item->account_group }}</td>
                                        <td>
                                            @if($item->category == 'Revenue')
                                                <span class="badge bg-success">{{ $item->category }}</span>
                                            @elseif($item->category == 'Expense')
                                                <span class="badge bg-danger">{{ $item->category }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ $item->category }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">
                                            TZS {{ number_format($item->budgeted_amount, 2) }}
                                        </td>
                                        <td class="text-end">
                                            TZS {{ number_format($item->actual_amount, 2) }}
                                        </td>
                                        <td class="text-end fw-bold {{ $item->variance >= 0 ? 'text-success' : 'text-danger' }}">
                                            TZS {{ number_format($item->variance, 2) }}
                                        </td>
                                        <td class="text-end">
                                            <span class="badge {{ $item->variance >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $item->variance_percentage }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <td colspan="5" class="fw-bold">TOTAL</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($budgetData['summary']['total_budgeted'], 2) }}</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($budgetData['summary']['total_actual'], 2) }}</td>
                                    <td class="text-end fw-bold {{ $budgetData['summary']['total_variance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        TZS {{ number_format($budgetData['summary']['total_variance'], 2) }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        <span class="badge {{ $budgetData['summary']['total_variance'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $budgetData['summary']['variance_percentage'] }}%
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <div class="empty-state-icon mb-4">
                                <i class="bx bx-chart-pie text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted mb-3">No Budget Data Found</h5>
                            <p class="text-muted mb-4">
                                No budget data matches your current filters. Try adjusting your search criteria.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle date type changes
    $('#date_type').on('change', function() {
        const dateType = $(this).val();
        const dateFromInput = $('#date_from');
        const dateToInput = $('#date_to');
        
        if (dateType === 'custom') {
            dateFromInput.prop('readonly', false);
            dateToInput.prop('readonly', false);
        } else {
            dateFromInput.prop('readonly', true);
            dateToInput.prop('readonly', true);
            
            // Auto-submit form when date type changes (except for custom)
            $('#budgetReportForm').submit();
        }
    });
    
    // Auto-submit form when year or branch changes
    $('#year, #branch_id').on('change', function() {
        $('#budgetReportForm').submit();
    });
});
</script>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#budgetReportTable').DataTable({
        "pageLength": 25,
        "order": [[ 0, "asc" ]],
        "responsive": true,
        "autoWidth": false,
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "emptyTable": "No budget data available",
            "zeroRecords": "No matching budget records found"
        },
        "columnDefs": [
            {
                "targets": [5, 6, 7, 8], // Amount columns
                "className": "text-end"
            }
        ]
    });

    // Initialize datepickers
    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        todayHighlight: true
    });

    // Auto-submit form when year or branch changes
    $('#year, #branch_id').change(function() {
        $('#budgetReportForm').submit();
    });
});
</script>
@endpush

@endsection 