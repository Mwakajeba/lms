@extends('layouts.main')

@section('title', 'Bank Reconciliation Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-flex align-items-center">
            <div class="me-auto">
                <h5 class="page-title text-dark fw-semibold fs-3">Bank Reconciliation Report</h5>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounting.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Bank Reconciliation Report</li>
                </ul>
            </div>
            <div class="ms-auto">
                <a href="{{ route('accounting.bank-reconciliation.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-2"></i>Back to Reconciliations
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row row-cols-1 row-cols-lg-3 mb-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-secondary">Total Reconciliations</p>
                                <h4 class="my-1 text-dark">{{ $stats['total_reconciliations'] }}</h4>
                            </div>
                            <div class="ms-auto fs-1 text-primary">
                                <i class="bx bx-list-ul"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-secondary">Completed</p>
                                <h4 class="my-1 text-success">{{ $stats['completed_reconciliations'] }}</h4>
                            </div>
                            <div class="ms-auto fs-1 text-success">
                                <i class="bx bx-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-secondary">Pending</p>
                                <h4 class="my-1 text-warning">{{ $stats['pending_reconciliations'] }}</h4>
                            </div>
                            <div class="ms-auto fs-1 text-warning">
                                <i class="bx bx-time"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Filters -->
        <div class="card radius-10">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-filter me-2"></i>Report Filters</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('accounting.reports.bank-reconciliation-report.generate') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="bank_account_id" class="form-label">Bank Account</label>
                            <select class="form-select" id="bank_account_id" name="bank_account_id">
                                <option value="">All Bank Accounts</option>
                                @foreach($bankAccounts as $bankAccount)
                                    <option value="{{ $bankAccount->id }}" {{ request('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                        {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search me-2"></i>Generate Report
                                </button>
                                <button type="submit" name="export_format" value="pdf" class="btn btn-danger">
                                    <i class="bx bx-download me-2"></i>Export PDF
                                </button>
                                <a href="{{ route('accounting.reports.bank-reconciliation-report') }}" class="btn btn-secondary">
                                    <i class="bx bx-refresh me-2"></i>Reset Filters
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Reconciliations -->
        @if($recentReconciliations->count() > 0)
        <div class="card radius-10 mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-history me-2"></i>Recent Reconciliations</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Bank Account</th>
                                <th>Reconciliation Date</th>
                                <th>Period</th>
                                <th>Bank Statement Balance</th>
                                <th>Book Balance</th>
                                <th>Difference</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentReconciliations as $reconciliation)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ms-2">
                                            <h6 class="mb-0">{{ $reconciliation->bankAccount->name }}</h6>
                                            <small class="text-muted">{{ $reconciliation->bankAccount->account_number }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $reconciliation->formatted_reconciliation_date }}</td>
                                <td>
                                    <small class="text-muted">
                                        {{ $reconciliation->formatted_start_date }} - {{ $reconciliation->formatted_end_date }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">{{ $reconciliation->formatted_bank_statement_balance }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">{{ $reconciliation->formatted_book_balance }}</span>
                                </td>
                                <td class="text-end">
                                    @if($reconciliation->difference == 0)
                                        <span class="badge bg-success">Balanced</span>
                                    @else
                                        <span class="text-danger fw-bold">{{ $reconciliation->formatted_difference }}</span>
                                    @endif
                                </td>
                                <td>{!! $reconciliation->status_badge !!}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ms-2">
                                            <h6 class="mb-0">{{ $reconciliation->user->name }}</h6>
                                            <small class="text-muted">{{ $reconciliation->created_at->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('accounting.bank-reconciliation.show', $reconciliation) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('accounting.reports.bank-reconciliation-report.show', $reconciliation) }}" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="bx bx-file"></i>
                                        </a>
                                        <a href="{{ route('accounting.reports.bank-reconciliation-report.export', $reconciliation) }}" 
                                           class="btn btn-sm btn-outline-danger">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="card radius-10 mt-4">
            <div class="card-body text-center py-5">
                <i class="bx bx-bank font-size-48 text-muted mb-3"></i>
                <h6 class="text-muted">No bank reconciliations found</h6>
                <p class="text-muted mb-0">Create your first bank reconciliation to generate reports.</p>
                <a href="{{ route('accounting.bank-reconciliation.create') }}" class="btn btn-primary mt-3">
                    <i class="bx bx-plus me-2"></i>Create Reconciliation
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set default dates if not provided
    if (!$('#start_date').val()) {
        $('#start_date').val('{{ date("Y-m-01") }}');
    }
    if (!$('#end_date').val()) {
        $('#end_date').val('{{ date("Y-m-t") }}');
    }

    // Validate end date is after start date
    $('#end_date').on('change', function() {
        const startDate = $('#start_date').val();
        const endDate = $(this).val();
        
        if (startDate && endDate && endDate < startDate) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date Range',
                text: 'End date must be after start date.'
            });
            $(this).val('');
        }
    });
});
</script>
@endpush 