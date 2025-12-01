@extends('layouts.main')

@section('title', 'Bank Reconciliation Report Results')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-flex align-items-center">
            <div class="me-auto">
                <h5 class="page-title text-dark fw-semibold fs-3">Bank Reconciliation Report Results</h5>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounting.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accounting.reports.bank-reconciliation-report') }}">Bank Reconciliation Report</a></li>
                    <li class="breadcrumb-item active">Results</li>
                </ul>
            </div>
            <div class="ms-auto">
                <a href="{{ route('accounting.reports.bank-reconciliation-report') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-2"></i>Back to Report
                </a>
            </div>
        </div>

        <!-- Report Statistics -->
        <div class="row row-cols-1 row-cols-lg-4 mb-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-secondary">Total Reconciliations</p>
                                <h4 class="my-1 text-dark">{{ $reportStats['total_reconciliations'] }}</h4>
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
                                <p class="mb-1 text-secondary">Total Bank Balance</p>
                                <h4 class="my-1 text-dark">{{ number_format($reportStats['total_bank_balance'], 2) }}</h4>
                            </div>
                            <div class="ms-auto fs-1 text-info">
                                <i class="bx bx-bank"></i>
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
                                <p class="mb-1 text-secondary">Total Book Balance</p>
                                <h4 class="my-1 text-dark">{{ number_format($reportStats['total_book_balance'], 2) }}</h4>
                            </div>
                            <div class="ms-auto fs-1 text-success">
                                <i class="bx bx-book"></i>
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
                                <p class="mb-1 text-secondary">Total Difference</p>
                                <h4 class="my-1 {{ $reportStats['total_difference'] == 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($reportStats['total_difference'], 2) }}
                                </h4>
                            </div>
                            <div class="ms-auto fs-1 {{ $reportStats['total_difference'] == 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bx {{ $reportStats['total_difference'] == 0 ? 'bx-check-circle' : 'bx-x-circle' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Applied -->
        @if(!empty(array_filter($filters)))
        <div class="card radius-10 mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-filter me-2"></i>Filters Applied</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($filters['bank_account_id'])
                        <div class="col-md-3">
                            <strong>Bank Account:</strong> 
                            @php
                                $bankAccount = $bankAccounts->where('id', $filters['bank_account_id'])->first();
                            @endphp
                            {{ $bankAccount ? $bankAccount->name : 'Unknown' }}
                        </div>
                    @endif
                    @if($filters['start_date'])
                        <div class="col-md-3">
                            <strong>Start Date:</strong> {{ \Carbon\Carbon::parse($filters['start_date'])->format('M d, Y') }}
                        </div>
                    @endif
                    @if($filters['end_date'])
                        <div class="col-md-3">
                            <strong>End Date:</strong> {{ \Carbon\Carbon::parse($filters['end_date'])->format('M d, Y') }}
                        </div>
                    @endif
                    @if($filters['status'])
                        <div class="col-md-3">
                            <strong>Status:</strong> 
                            <span class="badge bg-{{ $filters['status'] === 'completed' ? 'success' : ($filters['status'] === 'draft' ? 'secondary' : 'warning') }}">
                                {{ ucfirst(str_replace('_', ' ', $filters['status'])) }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Export Options -->
        <div class="card radius-10 mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-download me-2"></i>Export Options</h6>
            </div>
            <div class="card-body">
                <div class="d-flex gap-2">
                    <form action="{{ route('accounting.reports.bank-reconciliation-report.generate') }}" method="GET" class="d-inline">
                        @foreach($filters as $key => $value)
                            @if($value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <input type="hidden" name="export_format" value="pdf">
                        <button type="submit" class="btn btn-danger">
                            <i class="bx bx-file-pdf me-2"></i>Export to PDF
                        </button>
                    </form>
                    <a href="{{ route('accounting.reports.bank-reconciliation-report') }}" class="btn btn-secondary">
                        <i class="bx bx-refresh me-2"></i>New Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Reconciliation Results -->
        <div class="card radius-10">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-table me-2"></i>Reconciliation Results ({{ $reconciliations->count() }} records)</h6>
            </div>
            <div class="card-body">
                @if($reconciliations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="reconciliationsTable">
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
                            @foreach($reconciliations as $reconciliation)
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
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('accounting.reports.bank-reconciliation-report.show', $reconciliation) }}" 
                                           class="btn btn-sm btn-outline-info" title="View Report">
                                            <i class="bx bx-file"></i>
                                        </a>
                                        <a href="{{ route('accounting.reports.bank-reconciliation-report.export', $reconciliation) }}" 
                                           class="btn btn-sm btn-outline-danger" title="Export PDF">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bx bx-search font-size-48 text-muted mb-3"></i>
                    <h6 class="text-muted">No reconciliations found</h6>
                    <p class="text-muted mb-0">Try adjusting your filters or create new reconciliations.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Fix Highcharts error #13
    if (typeof Highcharts !== 'undefined') {
        Highcharts.error = function(code, stop) {
            if (code === 13) {
                console.warn('Highcharts error #13: Container not found, skipping chart rendering');
                return;
            }
            console.error('Highcharts error #' + code);
        };
    }
    
    // Initialize DataTable with error handling
    if ($('#reconciliationsTable').length) {
        try {
            var table = $('#reconciliationsTable').DataTable({
        "pageLength": 25,
        "order": [[1, "desc"]], // Sort by reconciliation date descending
        "columnDefs": [
                    { "orderable": false, "targets": -1 } // Last column (Actions) not sortable
                ],
                "responsive": true,
                "autoWidth": false,
                "deferRender": true,
                "processing": true,
                "serverSide": false
            });
        } catch (error) {
            console.warn('DataTable initialization error:', error);
        }
    }
});
</script>
@endpush 