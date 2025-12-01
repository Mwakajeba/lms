@extends('layouts.main')

@section('title', 'Bank Reconciliation')

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
                            ['label' => 'Bank Reconciliation', 'url' => '#', 'icon' => 'bx bx-credit-card']
                        ]" />
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-info me-2" onclick="refreshAllReconciliations()" id="refreshAllBtn">
                            <i class="bx bx-refresh me-2"></i>Refresh All
                        </button>
                        <a href="{{ route('accounting.reports.bank-reconciliation-report') }}" class="btn btn-danger me-2">
                            <i class="bx bx-file-pdf me-2"></i>Reports
                        </a>
                        <a href="{{ route('accounting.bank-reconciliation.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-2"></i>New Reconciliation
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <h6 class="mb-0 text-uppercase">BANK RECONCILIATION</h6>
        <hr />

        <!-- Statistics Cards -->
        <div class="row row-cols-1 row-cols-lg-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-secondary">Total Reconciliations</p>
                                <h4 class="my-1 text-dark">{{ $stats['total'] }}</h4>
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
                                <p class="mb-1 text-secondary">
                                    Completed 
                                    <i class="bx bx-info-circle text-muted" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Reconciliations that have been finalized and marked as complete"></i>
                                </p>
                                <h4 class="my-1 text-success">{{ $stats['completed'] }}</h4>
                                <small class="text-muted">Finalized</small>
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
                                <p class="mb-1 text-secondary">
                                    In Progress 
                                    <i class="bx bx-info-circle text-muted" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Reconciliations that are being worked on but not yet completed"></i>
                                </p>
                                <h4 class="my-1 text-warning">{{ $stats['in_progress'] }}</h4>
                                <small class="text-muted">Not yet completed</small>
                            </div>
                            <div class="ms-auto fs-1 text-warning">
                                <i class="bx bx-time"></i>
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
                                <p class="mb-1 text-secondary">
                                    Draft 
                                    <i class="bx bx-info-circle text-muted" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Reconciliations that are in initial setup phase"></i>
                                </p>
                                <h4 class="my-1 text-secondary">{{ $stats['draft'] }}</h4>
                                <small class="text-muted">Initial setup</small>
                            </div>
                            <div class="ms-auto fs-1 text-secondary">
                                <i class="bx bx-edit"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Explanation -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            Reconciliation Status Guide
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-secondary me-2">Draft</span>
                                    <small class="text-muted">Initial setup phase - can be edited</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-warning me-2">In Progress</span>
                                    <small class="text-muted">Being worked on - auto-updates with new transactions</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="badge bg-success me-2">Completed</span>
                                    <small class="text-muted">Finalized - no longer auto-updates</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reconciliation List -->
        <div class="card radius-10">
            <div class="card-header">
                <h6 class="mb-0">Bank Reconciliations</h6>
            </div>
            <div class="card-body">
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
                            @forelse($reconciliations as $reconciliation)
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
                                        <a href="{{ route('accounting.reports.bank-reconciliation-report.export', $reconciliation) }}" 
                                           class="btn btn-sm btn-outline-danger" title="Export PDF">
                                            <i class="bx bx-download"></i>
                                        </a>
                                        @if($reconciliation->status === 'draft')
                                        <a href="{{ route('accounting.bank-reconciliation.edit', $reconciliation) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteReconciliation({{ $reconciliation->id }}, '{{ $reconciliation->bankAccount->name }}')"
                                                title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-bank font-size-48 text-muted mb-3"></i>
                                        <h6 class="text-muted">No bank reconciliations found</h6>
                                        <p class="text-muted mb-0">Create your first bank reconciliation to get started.</p>
                                        <a href="{{ route('accounting.bank-reconciliation.create') }}" class="btn btn-primary mt-3">
                                            <i class="bx bx-plus me-2"></i>Create Reconciliation
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($reconciliations->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $reconciliations->links() }}
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

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function refreshAllReconciliations() {
    const btn = document.getElementById('refreshAllBtn');
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Refreshing...';
    btn.disabled = true;
    
    // Make AJAX request to refresh all reconciliations
    fetch('{{ route("accounting.bank-reconciliation.refresh-all") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'An error occurred while refreshing reconciliations.',
            icon: 'error'
        });
    })
    .finally(() => {
        // Restore button state
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function deleteReconciliation(id, bankName) {
    Swal.fire({
        title: 'Delete Reconciliation?',
        text: `Are you sure you want to delete the reconciliation for ${bankName}? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/accounting/bank-reconciliation/${id}`;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            
            form.appendChild(methodInput);
            form.appendChild(tokenInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush 