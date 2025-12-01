@extends('layouts.main')

@section('title', __('app.budgets'))

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-12">
                <div class="page-breadcrumb d-flex align-items-center">
                    <div class="me-auto">
                        <x-breadcrumbs-with-icons :links="[
                            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                            ['label' => __('app.budgets'), 'url' => '#', 'icon' => 'bx bx-chart']
                        ]" />
                    </div>
                    <div class="ms-auto">
                        @can('create budget')
                        <a href="{{ route('accounting.budgets.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> {{ __('app.create_budget') }}
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        <h6 class="mb-0 text-uppercase">{{ __('app.budget_management') }}</h6>
        <hr />

        <!-- Summary Cards -->
        <div class="row row-cols-1 row-cols-lg-4 g-4 mb-4">
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-1 text-muted small">{{ __('app.total_budget') }}</p>
                                <h3 class="mb-0 fw-bold text-primary">{{ $budgets->total() }}</h3>
                                <p class="mb-0 text-success small">
                                    <i class="bx bx-trending-up"></i> {{ __('app.budget_active') }}
                                </p>
                            </div>
                            <div class="widgets-icons bg-gradient-primary text-white">
                                <i class='bx bx-chart'></i>
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
                                <p class="mb-1 text-muted small">{{ __('app.budget_total_amount') }}</p>
                                <h3 class="mb-0 fw-bold text-success">TZS {{ number_format($budgets->sum(function($budget) { return $budget->total_amount; }), 2) }}</h3>
                                <p class="mb-0 text-success small">
                                    <i class="bx bx-dollar"></i> {{ __('app.budget_budgeted_amount') }}
                                </p>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white">
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
                                <p class="mb-1 text-muted small">{{ __('app.budget_this_year') }}</p>
                                <h3 class="mb-0 fw-bold text-info">{{ $budgets->where('year', date('Y'))->count() }}</h3>
                                <p class="mb-0 text-info small">
                                    <i class="bx bx-calendar"></i> {{ date('Y') }} {{ __('app.budget_budgets') }}
                                </p>
                            </div>
                            <div class="widgets-icons bg-gradient-info text-white">
                                <i class='bx bx-calendar'></i>
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
                                <p class="mb-1 text-muted small">{{ __('app.budget_average_amount') }}</p>
                                <h3 class="mb-0 fw-bold text-warning">TZS {{ $budgets->count() > 0 ? number_format($budgets->sum(function($budget) { return $budget->total_amount; }) / $budgets->count(), 2) : '0.00' }}</h3>
                                <p class="mb-0 text-warning small">
                                    <i class="bx bx-calculator"></i> {{ __('app.budget_per_budget') }}
                                </p>
                            </div>
                            <div class="widgets-icons bg-gradient-warning text-white">
                                <i class='bx bx-calculator'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="bx bx-filter-alt me-2"></i>
                            {{ __('app.search') }} & {{ __('app.filter') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('accounting.budgets.index') }}" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label fw-semibold">
                                    <i class="bx bx-search me-1"></i>
                                    {{ __('app.budget_search') }}
                                </label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="{{ __('app.budget_search_placeholder') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="year" class="form-label fw-semibold">
                                    <i class="bx bx-calendar me-1"></i>
                                    {{ __('app.budget_filter_by_year_label') }}
                                </label>
                                <select class="form-select" id="year" name="year">
                                    <option value="">{{ __('app.budget_all_years') }}</option>
                                    @for($year = date('Y') + 2; $year >= 2020; $year--)
                                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-search"></i> {{ __('app.search') }}
                                    </button>
                                    <a href="{{ route('accounting.budgets.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-refresh"></i> {{ __('app.budget_clear_filters') }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">&nbsp;</label>
                                <div>
                                    @can('create budget')
                                    <a href="{{ route('accounting.budgets.create') }}" class="btn btn-success w-100">
                                        <i class="bx bx-plus"></i> {{ __('app.budget_new_budget') }}
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budgets Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-bold text-dark">
                            <i class="bx bx-list-ul me-2"></i>
                            {{ __('app.budget_budget_list') }}
                        </h6>
                    <span class="badge bg-primary rounded-pill">{{ $budgets->total() }} {{ __('app.budget_budgets') }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                @if($budgets->count() > 0)
                    <div class="table-responsive">
                        <table id="example" class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">
                                        <i class="bx bx-hash me-1"></i>#
                                    </th>
                                    <th class="border-0">
                                        <i class="bx bx-bookmark me-1"></i>{{ __('app.budget_name') }}
                                    </th>
                                    <th class="border-0">
                                        <i class="bx bx-calendar me-1"></i>{{ __('app.budget_year') }}
                                    </th>
                                    <th class="border-0">
                                        <i class="bx bx-building me-1"></i>{{ __('app.budget_branch') }}
                                    </th>
                                    <th class="border-0">
                                        <i class="bx bx-user me-1"></i>{{ __('app.budget_created_by') }}
                                    </th>
                                    <th class="border-0">
                                        <i class="bx bx-money me-1"></i>{{ __('app.budget_total_amount') }}
                                    </th>
                                    <th class="border-0">
                                        <i class="bx bx-time me-1"></i>{{ __('app.budget_created_date') }}
                                    </th>
                                    <th class="border-0 text-center">
                                        <i class="bx bx-cog me-1"></i>{{ __('app.budget_actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budgets as $index => $budget)
                                    <tr class="budget-row">
                                        <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="budget-icon me-3">
                                                    <i class="bx bx-chart-pie text-primary"></i>
                                                </div>
                                                <div>
                                                    <a href="{{ route('accounting.budgets.show', $budget) }}" 
                                                       class="text-decoration-none fw-bold text-dark">
                                                        {{ $budget->name }}
                                                    </a>
                                                    @if($budget->description)
                                                        <br>
                                                        <small class="text-muted">{{ Str::limit($budget->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info rounded-pill">
                                                <i class="bx bx-calendar me-1"></i>{{ $budget->year }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-building text-muted me-2"></i>
                                                {{ $budget->branch->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <i class="bx bx-user-circle text-primary"></i>
                                                </div>
                                                {{ $budget->user->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold text-success fs-6">
                                                    TZS {{ number_format($budget->total_amount, 2) }}
                                                </span>
                                                <small class="text-muted ms-2">
                                                    ({{ $budget->budgetLines->count() }} lines)
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                <i class="bx bx-time me-1"></i>
                                                {{ $budget->created_at->format('d M Y, H:i') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('accounting.budgets.show', $budget) }}" 
                                                   class="btn btn-sm btn-primary view-budget-btn" 
                                                   data-budget-id="{{ $budget->id }}"
                                                   title="View Budget">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                @can('edit budget')
                                                <a href="{{ route('accounting.budgets.edit', $budget) }}" 
                                                   class="btn btn-sm btn-warning" title="Edit Budget">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                @endcan
                                                @can('delete budget')
                                                <button type="button" class="btn btn-sm btn-danger delete-budget-btn" 
                                                        data-budget-id="{{ $budget->id }}" 
                                                        data-budget-name="{{ $budget->name }}"
                                                        title="Delete Budget">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($budgets->hasPages())
                        <div class="card-footer bg-white border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $budgets->firstItem() ?? 0 }} to {{ $budgets->lastItem() ?? 0 }} of {{ $budgets->total() }} budgets
                                </div>
                                <div>
                                    {{ $budgets->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <div class="empty-state-icon mb-4">
                                <i class="bx bx-chart-pie text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted mb-3">{{ __('app.budget_no_budgets_found') }}</h5>
                            <p class="text-muted mb-4">
                                @if(request('search') || request('year'))
                                    {{ __('app.budget_no_budgets_filtered') }}
                                @else
                                    {{ __('app.budget_no_budgets_message') }}
                                @endif
                            </p>
                            <div class="d-flex justify-content-center gap-2">
                                @if(request('search') || request('year'))
                                    <a href="{{ route('accounting.budgets.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-refresh me-1"></i> {{ __('app.budget_clear_filters') }}
                                    </a>
                                @endif
                                @can('create budget')
                                <a href="{{ route('accounting.budgets.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> {{ __('app.budget_create_first') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="bx bx-bolt me-2"></i>
                            {{ __('app.budget_quick_actions') }}
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <a href="{{ route('accounting.budgets.import') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-import text-primary" style="font-size: 2rem;"></i>
                                        </div>
                                        <h6 class="card-title text-dark mb-2">Import Budget</h6>
                                        <p class="card-text text-muted small">Import budget data from Excel or CSV files</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-export text-success" style="font-size: 2rem;"></i>
                                        </div>
                                        <h6 class="card-title text-dark mb-2">Export Budgets</h6>
                                        <p class="card-text text-muted small mb-3">Export individual budgets to Excel or PDF</p>
                                        <button class="btn btn-success btn-sm" id="exportBudgetBtn">
                                            <i class="bx bx-export"></i> Export Budget
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('accounting.reports.budget-report') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-bar-chart-alt-2 text-warning" style="font-size: 2rem;"></i>
                                        </div>
                                        <h6 class="card-title text-dark mb-2">Budget Report</h6>
                                        <p class="card-text text-muted small">Budget vs Actual Analysis</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for Budget Index -->
<style>
.budget-row:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

.budget-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(45deg, #007bff, #0056b3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.avatar-sm {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state {
    padding: 2rem;
}

.widgets-icons {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

.bg-gradient-success {
    background: linear-gradient(45deg, #28a745, #1e7e34);
}

.bg-gradient-info {
    background: linear-gradient(45deg, #17a2b8, #117a8b);
}

.bg-gradient-warning {
    background: linear-gradient(45deg, #ffc107, #e0a800);
}

.btn-group .btn {
    margin: 0 1px;
}

.delete-budget-btn {
    transition: all 0.3s ease;
}

.delete-budget-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
}

/* Ensure buttons are visible */
.btn-group {
    display: flex !important;
    gap: 2px;
}

.btn-group .btn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
}

/* Make sure table cells with buttons are visible */
.table td {
    vertical-align: middle;
}

/* Ensure action buttons are not hidden by DataTable */
.dataTables_wrapper .btn-group {
    visibility: visible !important;
    opacity: 1 !important;
}

.delete-budget-btn {
    transition: all 0.3s ease;
}

.delete-budget-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
}

.table th {
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75rem;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
</style>

@push('scripts')
<!-- DataTable Initialization -->
<script>
$(document).ready(function() {
    console.log('Document ready - initializing budget page');
    
    // Fix Highcharts error #13 locally
    if (typeof Highcharts !== 'undefined') {
        Highcharts.error = function(code, stop) {
            if (code === 13) {
                console.warn('Highcharts error #13: Container not found, skipping chart rendering');
                return;
            }
            console.error('Highcharts error #' + code);
        };
    }
    
    // Test if jQuery is available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }
    console.log('jQuery is available');
    
    // Test if SweetAlert is available
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert is not loaded!');
    } else {
        console.log('SweetAlert is available');
    }
    
    // Initialize DataTable - Check if already initialized
    try {
        if ($.fn.DataTable.isDataTable('#example')) {
            console.log('DataTable already initialized, destroying and reinitializing');
            $('#example').DataTable().destroy();
        }
        
        $('#example').DataTable({
            "pageLength": 25,
            "order": [[ 0, "asc" ]],
            "responsive": false, // Disable responsive to prevent button hiding
            "autoWidth": false,
            "language": {
                "search": "{{ __('app.budget_search') }}:",
                "lengthMenu": "{{ __('app.budget_budgets') }} _MENU_ kwa ukurasa",
                "info": "Inaonyesha _START_ hadi _END_ ya _TOTAL_ {{ __('app.budget_budgets') }}",
                "infoEmpty": "Inaonyesha 0 hadi 0 ya 0 {{ __('app.budget_budgets') }}",
                "infoFiltered": "(imechujwa kutoka _MAX_ {{ __('app.budget_budgets') }} zote)",
                "emptyTable": "Hakuna {{ __('app.budget_budgets') }} zinazopatikana",
                "zeroRecords": "Hakuna {{ __('app.budget_budgets') }} zinazofanana zilizopatikana"
            },
            "columnDefs": [
                {
                    "targets": -1, // Last column (actions)
                    "orderable": false,
                    "searchable": false,
                    "width": "120px"
                }
            ]
        });
        console.log('DataTable initialized successfully');
    } catch (error) {
        console.error('DataTable initialization error:', error);
    }
    

    
    // Delete button click handler
    $(document).on('click', '.delete-budget-btn', function(e) {
        e.preventDefault();
        console.log('Delete button clicked!');
        const budgetId = $(this).data('budget-id');
        const budgetName = $(this).data('budget-name');
        console.log('Budget ID:', budgetId, 'Budget Name:', budgetName);
        
        // Use SweetAlert if available, otherwise fallback to browser confirm
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '{{ __('app.budget_confirm_delete') }}',
                text: '{{ __('app.budget_confirm_delete_message') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __('app.budget_yes_delete') }}',
                cancelButtonText: '{{ __('app.budget_cancel') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    submitDeleteForm(budgetId);
                }
            });
        } else {
            // Fallback to browser confirm
            if (confirm('{{ __('app.budget_confirm_delete') }} - {{ __('app.budget_confirm_delete_message') }}')) {
                submitDeleteForm(budgetId);
            }
        }
    });
    
    // Helper function to submit delete form
    function submitDeleteForm(budgetId) {
        console.log('Submitting delete form for budget ID:', budgetId);
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/accounting/budgets/${budgetId}`;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add method override
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }
    
    // Export button click handler
    $(document).on('click', '#exportBudgetBtn', function() {
        showExportModal();
    });
    
    // Function to show export modal
    function showExportModal() {
        // Get all budgets from the table
        const budgets = [];
        const tableRows = $('#example tbody tr');
        
        tableRows.each(function() {
            const row = $(this);
            // Get budget ID from the view button's data attribute
            const budgetId = row.find('.view-budget-btn').data('budget-id');
            
            // Get budget name from column 1 (index 1)
            const budgetNameElement = row.find('td:eq(1) a');
            const budgetName = budgetNameElement.text().trim();
            
            // Get year from column 2 (index 2) - remove the badge wrapper
            const budgetYearElement = row.find('td:eq(2) .badge');
            const budgetYear = budgetYearElement.text().trim();
            
            if (budgetId && budgetName) {
                budgets.push({
                    id: budgetId,
                    name: budgetName,
                    year: budgetYear
                });
            }
        });
        
        if (budgets.length === 0) {
            Swal.fire({
                title: 'No Budgets Found',
                text: 'No budgets available for export.',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#007bff'
            });
            return;
        }
        
        // Create budget options HTML
        let budgetOptions = '';
        budgets.forEach(function(budget) {
            budgetOptions += `
                <div class="budget-option mb-2">
                    <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                        <div>
                            <strong>${budget.name}</strong>
                            <br><small class="text-muted">Year: ${budget.year}</small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="/accounting/budgets/${budget.id}/export/excel" class="btn btn-success">
                                <i class="bx bx-export"></i> Excel
                            </a>
                            <a href="/accounting/budgets/${budget.id}/export/pdf" class="btn btn-danger">
                                <i class="bx bx-file-pdf"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        Swal.fire({
            title: 'Export Budget',
            html: `
                <div class="text-start">
                    <p class="mb-3">Select a budget to export:</p>
                    <div class="budget-list">
                        ${budgetOptions}
                    </div>
                </div>
            `,
            width: '600px',
            showConfirmButton: false,
            showCloseButton: true,
            customClass: {
                container: 'export-modal'
            }
        });
    }
});
</script>

<!-- Export Modal Styles -->
<style>
.export-modal .swal2-popup {
    max-height: 80vh;
    overflow-y: auto;
}

.budget-option {
    transition: all 0.2s ease;
}

.budget-option:hover {
    background-color: #f8f9fa;
    border-radius: 5px;
}

.budget-option .btn-group .btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
}

.budget-option .btn-group .btn i {
    font-size: 0.9rem;
}
</style>
@endpush
@endsection
                                                