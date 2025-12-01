@extends('layouts.main')

@section('title', 'Bank Reconciliation Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Bank Reconciliation', 'url' => route('accounting.bank-reconciliation.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => $bankReconciliation->bankAccount->name . ' - ' . $bankReconciliation->formatted_reconciliation_date, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">BANK RECONCILIATION DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('accounting.reports.bank-reconciliation-report.export', $bankReconciliation) }}" class="btn btn-danger me-2">
                        <i class="bx bx-download me-2"></i>Export PDF
                    </a>
                    @if($bankReconciliation->status === 'draft')
                        <a href="{{ route('accounting.bank-reconciliation.edit', $bankReconciliation) }}" class="btn btn-warning me-2">
                            <i class="bx bx-edit me-2"></i>Edit Reconciliation
                        </a>
                    @endif
                    @if($bankReconciliation->status !== 'completed')
                                <button type="button" class="btn btn-info me-2" onclick="refreshBookBalance()" id="refreshBookBalanceBtn">
                                    <i class="bx bx-refresh me-2"></i>Refresh Book Balance
                                </button>
                    <button type="button" class="btn btn-success me-2" onclick="markAsCompleted()" title="Mark this reconciliation as completed">
                        <i class="bx bx-check-circle me-2"></i>Mark as Completed
                    </button>
                    
                    <!-- Hidden form for completion -->
                    <form id="completeForm" action="{{ route('accounting.bank-reconciliation.complete', $bankReconciliation) }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                @endif
                <a href="{{ route('accounting.bank-reconciliation.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-2"></i>Back to Reconciliations
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row row-cols-1 row-cols-lg-4 mb-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-secondary">Bank Statement Balance</p>
                                <h4 class="my-1 text-dark">{{ $bankReconciliation->formatted_bank_statement_balance }}</h4>
                            </div>
                            <div class="ms-auto fs-1 text-primary">
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
                                <p class="mb-1 text-secondary">Book Balance</p>
                                <h4 class="my-1 text-dark">{{ $bankReconciliation->formatted_book_balance }}</h4>
                                @if($bankReconciliation->status !== 'completed')
                                    <small class="text-info">
                                        <i class="bx bx-sync me-1"></i>Auto-updates with new transactions
                                    </small>
                                @endif
                            </div>
                            <div class="ms-auto fs-1 text-info">
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
                                <p class="mb-1 text-secondary">Difference</p>
                                <h4 class="my-1 {{ $bankReconciliation->difference == 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $bankReconciliation->formatted_difference }}
                                </h4>
                            </div>
                            <div class="ms-auto fs-1 {{ $bankReconciliation->difference == 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bx {{ $bankReconciliation->difference == 0 ? 'bx-check-circle' : 'bx-x-circle' }}"></i>
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
                                <p class="mb-1 text-secondary">Status</p>
                                <h4 class="my-1">{!! $bankReconciliation->status_badge !!}</h4>
                            </div>
                            <div class="ms-auto fs-1 text-warning">
                                <i class="bx bx-time"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Reconciliation Details -->
            <div class="col-lg-8">
                <div class="card radius-10">
                    <div class="card-header">
                        <h6 class="mb-0">Reconciliation Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold">Bank Account:</td>
                                        <td>{{ $bankReconciliation->bankAccount->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Account Number:</td>
                                        <td>{{ $bankReconciliation->bankAccount->account_number }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Reconciliation Date:</td>
                                        <td>{{ $bankReconciliation->formatted_reconciliation_date }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Period:</td>
                                        <td>{{ $bankReconciliation->formatted_start_date }} - {{ $bankReconciliation->formatted_end_date }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold">Created By:</td>
                                        <td>{{ $bankReconciliation->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Created At:</td>
                                        <td>{{ $bankReconciliation->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($bankReconciliation->notes)
                        <div class="mt-3">
                            <h6 class="fw-bold">Notes:</h6>
                            <p class="text-muted">{{ $bankReconciliation->notes }}</p>
                        </div>
                        @endif

                        @if($bankReconciliation->bank_statement_notes)
                        <div class="mt-3">
                            <h6 class="fw-bold">Bank Statement Notes:</h6>
                            <p class="text-muted">{{ $bankReconciliation->bank_statement_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Reconciliation Items -->
                <div class="card radius-10 mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Reconciliation Items</h6>
                        @if($bankReconciliation->status !== 'completed')
                        <div class="d-flex gap-2">
                            <a href="{{ route('accounting.receipt-vouchers.create') }}?bank_account_id={{ $bankReconciliation->bank_account_id }}&reconciliation_id={{ $bankReconciliation->id }}" 
                               class="btn btn-sm btn-success">
                                <i class="bx bx-plus me-1"></i>Add Receipt
                            </a>
                            <a href="{{ route('accounting.payment-vouchers.create') }}?bank_account_id={{ $bankReconciliation->bank_account_id }}&reconciliation_id={{ $bankReconciliation->id }}" 
                               class="btn btn-sm btn-danger">
                                <i class="bx bx-minus me-1"></i>Add Payment
                            </a>
                            <a href="{{ route('accounting.journals.create') }}?bank_account_id={{ $bankReconciliation->bank_account_id }}&reconciliation_id={{ $bankReconciliation->id }}" 
                               class="btn btn-sm btn-warning">
                                <i class="bx bx-transfer me-1"></i>Add Journal
                            </a>
                        </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="reconciliationTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="unreconciled-tab" data-bs-toggle="tab" data-bs-target="#unreconciled" type="button" role="tab">
                                    Unreconciled Items
                                    <span class="badge bg-warning ms-1">{{ $unreconciledBankItems->count() + $unreconciledBookItems->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="reconciled-tab" data-bs-toggle="tab" data-bs-target="#reconciled" type="button" role="tab">
                                    Reconciled Items
                                    <span class="badge bg-success ms-1">{{ $totalReconciledCount ?? $reconciledItems->count() }}</span>
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content mt-3" id="reconciliationTabsContent">
                            <div class="tab-pane fade show active" id="unreconciled" role="tabpanel">
                                <!-- Summary of unreconciled items -->
                                @php
                                    $unreconciledSummary = $bankReconciliation->getUnreconciledSummary();
                                @endphp
                                <div class="alert alert-info mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Bank Statement Items:</strong> {{ $unreconciledSummary['bank_items_count'] }} unreconciled
                                            @if($unreconciledSummary['bank_items_count'] > 0)
                                                <br><small class="text-muted">Total: {{ number_format($unreconciledSummary['bank_items_total'], 2) }}</small>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Book Entry Items:</strong> {{ $unreconciledSummary['book_items_count'] }} unreconciled
                                            @if($unreconciledSummary['book_items_count'] > 0)
                                                <br><small class="text-muted">Total: {{ number_format($unreconciledSummary['book_items_total'], 2) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    @if($unreconciledSummary['total_unreconciled'] > 0)
                                    <hr class="my-2">
                                    <div class="text-center">
                                        <strong>Total Unreconciled Items: {{ $unreconciledSummary['total_unreconciled'] }}</strong>
                                    </div>
                                    @endif
                                </div>
                                
                                <div class="row">
                                    <!-- Confirmed From Physical Statement (starts empty, filled by confirmations) -->
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-info">Confirmed From Physical Statement</h6>
                                        <small class="text-muted d-block mb-2">Tick a matching system entry on the right to confirm from your paper statement. Recent confirmations appear here (and also under Reconciled).</small>
                                        <div id="confirmedFromStatement">
                                            @if(($totalReconciledCount ?? 0) > 0)
                                                @foreach($reconciledItems as $item)
                                                <div class="card mb-2 border-success">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1">{{ $item->description }}</h6>
                                                                <small class="text-muted">{{ $item->formatted_transaction_date }} - {{ $item->reference }}</small>
                                                                <div class="mt-1">
                                                                    <span class="badge {{ $item->nature === 'debit' ? 'bg-danger' : 'bg-success' }}">{{ strtoupper($item->nature) }}</span>
                                                                    <span class="fw-bold ms-2">{{ $item->formatted_amount }}</span>
                                                                    <span class="badge bg-success ms-2">Reconciled</span>
                                                                </div>
                                                                @if($item->matchedWithItem)
                                                                <small class="text-muted">Matched with: {{ $item->matchedWithItem->description }}</small>
                                                                @endif
                                                            </div>
                                                            <div class="ms-2">
                                                                @if($bankReconciliation->status !== 'completed')
                                                                <form action="{{ route('accounting.bank-reconciliation.unmatch-items', $bankReconciliation) }}" method="POST" class="d-inline unmatch-form">
                                                                    @csrf
                                                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                                                    <button type="button" class="btn btn-sm btn-outline-danger unmatch-swal-btn" data-item-id="{{ $item->id }}">
                                                                        <i class="bx bx-unlink"></i>
                                                                    </button>
                                                                </form>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @endif
                                        </div>
                                        @if(($totalReconciledCount ?? 0) === 0)
                                        <div id="noConfirmedPlaceholder" class="text-center py-4">
                                            <i class="bx bx-info-circle font-size-48 text-muted mb-3"></i>
                                            <h6 class="text-muted">No confirmations yet</h6>
                                            <p class="text-muted">Use your physical statement and tick the matching system entry on the right.</p>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Book Entry Items -->
                                    <div class="col-md-6">
                                        <h6 class="fw-bold text-primary">Book Entry Items <small class="text-muted">(tick to confirm)</small></h6>
                                        <small class="text-muted d-block mb-2">Tick the system entry that matches what you see on your physical statement. It will move left as reconciled. You can reverse it.</small>
                                        @forelse($unreconciledBookItems as $item)
                                        <div class="card mb-2 border-primary">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">{{ $item->description }}</h6>
                                                        <small class="text-muted">{{ $item->formatted_transaction_date }} - {{ $item->reference }}</small>
                                                        <div class="mt-1">
                                                            <span class="badge {{ $item->nature === 'debit' ? 'bg-danger' : 'bg-success' }}">
                                                                {{ strtoupper($item->nature) }}
                                                            </span>
                                                            <span class="fw-bold ms-2">{{ $item->formatted_amount }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="ms-2 form-check">
                                                        <input class="form-check-input book-item-checkbox" type="checkbox" value="{{ $item->id }}" id="book_cb_{{ $item->id }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @empty
                                        <div class="text-center py-4">
                                            <i class="bx bx-check-circle font-size-48 text-success mb-3"></i>
                                            <h6 class="text-success">All book entry items reconciled!</h6>
                                            <p class="text-muted">No unreconciled book entry items found.</p>
                                        </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- Match Items Form removed for physical-statement-first workflow -->
                            </div>

                            <div class="tab-pane fade" id="reconciled" role="tabpanel">
                                <!-- Summary of reconciled items -->
                                @if($reconciledItems->count() > 0)
                                <div class="alert alert-success mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Reconciled Items:</strong> {{ $reconciledItems->count() }} items
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Total Amount:</strong> {{ number_format($reconciledItems->sum('amount'), 2) }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                @forelse($reconciledItems as $item)
                                <div class="card mb-2 border-success">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $item->description }}</h6>
                                                <small class="text-muted">{{ $item->formatted_transaction_date }} - {{ $item->reference }}</small>
                                                <div class="mt-1">
                                                    <span class="badge {{ $item->nature === 'debit' ? 'bg-danger' : 'bg-success' }}">
                                                        {{ strtoupper($item->nature) }}
                                                    </span>
                                                    <span class="fw-bold ms-2">{{ $item->formatted_amount }}</span>
                                                    <span class="badge bg-success ms-2">Reconciled</span>
                                                </div>
                                                @if($item->matchedWithItem)
                                                <small class="text-muted">Matched with: {{ $item->matchedWithItem->description }}</small>
                                                @endif
                                            </div>
                                            <div class="ms-2">
                                                @if($bankReconciliation->status !== 'completed')
                                                <form action="{{ route('accounting.bank-reconciliation.unmatch-items', $bankReconciliation) }}" method="POST" class="d-inline unmatch-form">
                                                    @csrf
                                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                                    <button type="button" class="btn btn-sm btn-outline-danger unmatch-swal-btn" data-item-id="{{ $item->id }}">
                                                        <i class="bx bx-unlink"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4">
                                    <i class="bx bx-info-circle font-size-48 text-muted mb-3"></i>
                                    <h6 class="text-muted">No reconciled items yet</h6>
                                    <p class="text-muted mb-0">Start matching unreconciled items to see them here.</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Adjusted Balances -->
            <div class="col-lg-4">
                <div class="card radius-10">
                    <div class="card-header">
                        <h6 class="mb-0">Adjusted Balances</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Adjusted Bank Balance</label>
                            <input type="number" step="0.01" class="form-control" 
                                   value="{{ $bankReconciliation->adjusted_bank_balance }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Adjusted Book Balance</label>
                            <input type="number" step="0.01" class="form-control" 
                                   value="{{ $bankReconciliation->adjusted_book_balance }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Difference</label>
                            <input type="number" step="0.01" class="form-control {{ $bankReconciliation->difference == 0 ? 'border-success' : 'border-danger' }}" 
                                   value="{{ $bankReconciliation->difference }}" readonly>
                        </div>
                        <div class="alert {{ $bankReconciliation->difference == 0 ? 'alert-success' : 'alert-danger' }}">
                            <i class="bx {{ $bankReconciliation->difference == 0 ? 'bx-check-circle' : 'bx-x-circle' }} me-2"></i>
                            {{ $bankReconciliation->difference == 0 ? 'Reconciliation is balanced!' : 'Reconciliation is not balanced.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Bank Statement Item Modal -->
<div class="modal fade" id="addBankStatementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Bank Statement Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('accounting.bank-reconciliation.add-bank-statement-item', $bankReconciliation) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reference" class="form-label">Reference</label>
                        <input type="text" class="form-control" id="reference" name="reference" placeholder="Transaction reference">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required placeholder="Transaction description">
                    </div>
                    <div class="mb-3">
                        <label for="transaction_date" class="form-label">Transaction Date</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label for="nature" class="form-label">Nature</label>
                        <select class="form-select" id="nature" name="nature" required>
                            <option value="">Select nature</option>
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedBankItem = null;
let selectedBookItem = null;

function selectForMatching(type, itemId) {
    if (type === 'bank') {
        selectedBankItem = itemId;
        $('#bank_item_id').val(itemId);
    } else {
        selectedBookItem = itemId;
        $('#book_item_id').val(itemId);
    }
}

function refreshBookBalance() {
    const btn = document.getElementById('refreshBookBalanceBtn');
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-2"></i>Refreshing...';
    btn.disabled = true;
    
    // Make AJAX request to update book balance
    fetch('{{ route("accounting.bank-reconciliation.update-book-balance", $bankReconciliation) }}', {
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
            location.reload();
        } else {
            Swal.fire('Error', data.message || 'Failed to refresh book balance.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Network Error', 'An error occurred while refreshing the book balance.', 'error');
    })
    .finally(() => {
        // Restore button state
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}



$(document).ready(function() {
    // SweetAlert confirm for unmatch in reconciled tab
    $(document).on('click', '.unmatch-swal-btn', function(e){
        e.preventDefault();
        const form = $(this).closest('form.unmatch-form');
        Swal.fire({
            title: 'Unmatch this item?',
            text: 'This will move the items back to Unreconciled.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, unmatch',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.trigger('submit');
            }
        });
    });
    // Set default transaction date
    $('#transaction_date').val('{{ date("Y-m-d") }}');

    // When a book item checkbox is ticked, confirm from physical statement via AJAX
    $('.book-item-checkbox').on('change', function(){
        const checkbox = this;
        if (!checkbox.checked) return;

        const bookItemId = $(checkbox).val();
        $(checkbox).prop('disabled', true);

        fetch('{{ route("accounting.bank-reconciliation.confirm-book-item", $bankReconciliation) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ book_item_id: bookItemId })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success && !data.already_reconciled) {
                Swal.fire('Error', data.message || 'Failed to confirm item.', 'error');
                $(checkbox).prop('checked', false).prop('disabled', false);
                return;
            }
            Swal.fire({
                icon: 'success',
                title: 'Confirmed',
                text: 'Item confirmed from physical statement.',
                timer: 1200,
                showConfirmButton: false
            }).then(() => location.reload());
        })
        .catch(() => {
            Swal.fire('Network Error', 'Please try again.', 'error');
            $(checkbox).prop('checked', false).prop('disabled', false);
        });
    });
});

// Show success message if exists
@if(session('success'))
    Swal.fire({
        title: 'Success!',
        text: '{{ session('success') }}',
        icon: 'success',
        confirmButtonText: 'OK'
    });
@endif

// Show error message if exists
@if(session('error') || $errors->any())
    Swal.fire({
        title: 'Error!',
        text: '{{ session('error') ?? $errors->first() }}',
        icon: 'error',
        confirmButtonText: 'OK'
    });
@endif

// Function to mark reconciliation as completed with SweetAlert
function markAsCompleted() {
    Swal.fire({
        title: 'Mark as Completed?',
        text: 'Are you sure you want to mark this reconciliation as completed? This action cannot be undone.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Mark as Completed',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Marking reconciliation as completed',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit the form
            document.getElementById('completeForm').submit();
        }
    });
}
</script>
@endpush 