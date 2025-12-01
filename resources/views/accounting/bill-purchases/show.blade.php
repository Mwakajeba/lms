@extends('layouts.main')

@section('title', 'Bill Purchase Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Bill Purchases', 'url' => route('accounting.bill-purchases'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Bill #' . $billPurchase->reference, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">BILL PURCHASE DETAILS</h6>
        <hr />

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-receipt me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">Bill Purchase: {{ $billPurchase->reference }}</h5>
                                </div>
                                <p class="mb-0 text-muted">Created on {{ $billPurchase->created_at->format('F d, Y \a\t g:i A') }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('accounting.bill-purchases.export-pdf', $billPurchase) }}" class="btn btn-info">
                                        <i class="bx bx-download me-1"></i> Export PDF
                                    </a>
                                    <a href="{{ route('accounting.bill-purchases.edit', $billPurchase) }}" class="btn btn-warning">
                                        <i class="bx bx-edit me-1"></i> Edit
                                    </a>
                                    @if(!$billPurchase->isPaid())
                                        <a href="{{ route('accounting.bill-purchases.payment', $billPurchase) }}" class="btn btn-success">
                                            <i class="bx bx-money me-1"></i> Add Payment
                                        </a>
                                    @endif
                                    <a href="{{ route('accounting.bill-purchases') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Bill Information -->
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Bill Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Reference</label>
                                <p class="form-control-plaintext">{{ $billPurchase->reference }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <p class="form-control-plaintext">{!! $billPurchase->status_badge !!}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Bill Date</label>
                                <p class="form-control-plaintext">{{ $billPurchase->formatted_date }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Due Date</label>
                                <p class="form-control-plaintext">{{ $billPurchase->formatted_due_date ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Supplier</label>
                                <p class="form-control-plaintext">{{ $billPurchase->supplier->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Branch</label>
                                <p class="form-control-plaintext">{{ $billPurchase->branch->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Credit Account</label>
                                <p class="form-control-plaintext">
                                    {{ $billPurchase->creditAccount->account_code ?? 'N/A' }} - 
                                    {{ $billPurchase->creditAccount->account_name ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Created By</label>
                                <p class="form-control-plaintext">{{ $billPurchase->user->name ?? 'N/A' }}</p>
                            </div>
                            @if($billPurchase->note)
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Notes</label>
                                    <p class="form-control-plaintext">{{ $billPurchase->note }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Line Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Account</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($billPurchase->billItems as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->debitAccount->account_name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $item->debitAccount->account_code ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $item->description ?: 'No description' }}</td>
                                            <td class="text-end">TZS {{ number_format($item->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No line items found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-primary">
                                        <td colspan="3" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold">TZS {{ $billPurchase->formatted_total_amount }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payments -->
                @if($billPurchase->payments->count() > 0)
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bx bx-money me-2"></i>Payments</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Reference</th>
                                            <th>Date</th>
                                            <th>Bank Account</th>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($billPurchase->payments as $index => $payment)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <a href="{{ route('accounting.bill-purchases.payment.show', $payment->hash_id) }}" class="text-primary">{{ $payment->reference }}</a>
                                                </td>
                                                <td>{{ $payment->date->format('Y-m-d') }}</td>
                                                <td>{{ $payment->bankAccount->name ?? 'N/A' }}</td>
                                                <td>{{ $payment->description ?: 'No description' }}</td>
                                                <td class="text-end">TZS {{ number_format($payment->amount, 2) }}</td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('accounting.bill-purchases.payment.show', $payment->hash_id) }}" 
                                                           class="btn btn-sm btn-info" title="View Payment">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <a href="{{ route('accounting.bill-purchases.payment.edit', $payment->hash_id) }}" 
                                                           class="btn btn-sm btn-warning" title="Edit Payment">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-danger delete-payment-btn" 
                                                                title="Delete Payment" 
                                                                data-payment-id="{{ $payment->hash_id }}"
                                                                data-payment-reference="{{ $payment->reference }}">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                        <form id="delete-payment-form-{{ $payment->hash_id }}" 
                                                              action="{{ route('accounting.bill-purchases.payment.delete', $payment->hash_id) }}" 
                                                              method="POST" class="d-none">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-success">
                                            <td colspan="6" class="text-end fw-bold">Total Paid:</td>
                                            <td class="text-end fw-bold">TZS {{ $billPurchase->formatted_paid }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Summary -->
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bx bx-calculator me-2"></i>Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">Total Amount</label>
                                <p class="h5 text-primary">TZS {{ $billPurchase->formatted_total_amount }}</p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">Paid Amount</label>
                                <p class="h5 text-success">TZS {{ $billPurchase->formatted_paid }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Balance</label>
                                <p class="h4 {{ $billPurchase->balance > 0 ? 'text-danger' : 'text-success' }}">
                                    TZS {{ $billPurchase->formatted_balance }}
                                </p>
                            </div>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            @if(!$billPurchase->isPaid())
                                <a href="{{ route('accounting.bill-purchases.payment', $billPurchase) }}" class="btn btn-success">
                                    <i class="bx bx-money me-1"></i> Add Payment
                                </a>
                            @else
                                <button class="btn btn-success" disabled>
                                    <i class="bx bx-check-circle me-1"></i> Fully Paid
                                </button>
                            @endif
                                                            <a href="{{ route('accounting.bill-purchases.edit', $billPurchase) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Edit Bill
                            </a>
                            @if($billPurchase->payments()->count() == 0)
                            <form action="{{ route('accounting.bill-purchases.destroy', $billPurchase) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this bill?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bx bx-trash me-1"></i> Delete Bill
                                </button>
                            </form>
                            @else
                            <button type="button" class="btn btn-danger" disabled title="Cannot delete bill with existing payments">
                                <i class="bx bx-trash me-1"></i> Delete Bill
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- GL Transactions -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bx bx-transfer me-2"></i>GL Transactions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th>Nature</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($billPurchase->glTransactions as $transaction)
                                        <tr>
                                            <td>
                                                <small>{{ $transaction->chartAccount->account_code ?? 'N/A' }}</small><br>
                                                <strong>{{ $transaction->chartAccount->account_name ?? 'N/A' }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->nature == 'debit' ? 'danger' : 'success' }}">
                                                    {{ strtoupper($transaction->nature) }}
                                                </span>
                                            </td>
                                            <td class="text-end">TZS {{ number_format($transaction->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete payment buttons
    document.querySelectorAll('.delete-payment-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const paymentId = this.getAttribute('data-payment-id');
            const paymentReference = this.getAttribute('data-payment-reference');
            
            Swal.fire({
                title: 'Delete Payment?',
                html: `<div class="text-center">
                    <i class="bx bx-trash text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>Are you sure you want to delete payment <strong>${paymentReference}</strong>?</p>
                    <p class="text-muted small">This action cannot be undone and will affect the bill's paid amount.</p>
                </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, delete it!',
                cancelButtonText: '<i class="bx bx-x me-1"></i> Cancel',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the payment.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    document.getElementById(`delete-payment-form-${paymentId}`).submit();
                }
            });
        });
    });
});
</script>
@endpush 