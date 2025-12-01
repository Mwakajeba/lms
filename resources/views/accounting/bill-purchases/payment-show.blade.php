@extends('layouts.main')

@section('title', 'Bill Payment Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Accounting</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('accounting.bill-purchases') }}">Bill Purchases</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payment Details</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-success">
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-money me-1 font-22 text-success"></i></div>
                            <h5 class="mb-0 text-success">Bill Payment Details</h5>
                                </div>
                                <p class="mb-0 text-muted">Created on {{ $payment->created_at->format('F d, Y \a\t g:i A') }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('accounting.bill-payments.export-pdf', $payment) }}" class="btn btn-info">
                                        <i class="bx bx-download me-1"></i> Export PDF
                                    </a>
                                    <a href="{{ route('accounting.bill-purchases.payment.edit', $payment) }}" class="btn btn-warning">
                                        <i class="bx bx-edit me-1"></i> Edit
                                    </a>
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

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>
                Please fix the following errors:
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Payment Information -->
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Payment Date</label>
                                <p class="form-control-plaintext">{{ $payment->date->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Payment Amount</label>
                                <p class="form-control-plaintext h5 text-success">TZS {{ number_format($payment->amount, 2) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Bank Account</label>
                                <p class="form-control-plaintext">
                                    {{ $payment->bankAccount->name ?? 'N/A' }}<br>
                                    <small>{{ $payment->bankAccount->account_number ?? 'N/A' }}</small>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Supplier</label>
                                <p class="form-control-plaintext">
                                    {{ $payment->supplier->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Created By</label>
                                <p class="form-control-plaintext">{{ $payment->user->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Branch</label>
                                <p class="form-control-plaintext">{{ $payment->branch->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <p class="form-control-plaintext">
                                    {{ $payment->description ?: 'No description provided' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GL Transactions -->
                @if($payment->glTransactions->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bx bx-transfer me-2"></i>GL Transactions</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Account</th>
                                            <th>Nature</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payment->glTransactions as $transaction)
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
                @endif
            </div>

            <!-- Actions -->
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('accounting.bill-purchases.payment.edit', $payment->hash_id) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Edit Payment
                            </a>
                            <button type="button" class="btn btn-danger w-100 delete-payment-btn"
                                    data-payment-id="{{ $payment->hash_id }}"
                                    data-payment-reference="{{ $payment->reference }}">
                                <i class="bx bx-trash me-1"></i> Delete Payment
                            </button>
                            <form id="delete-payment-form-{{ $payment->hash_id }}" 
                                  action="{{ route('accounting.bill-purchases.payment.delete', $payment->hash_id) }}" 
                                  method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                            <a href="{{ route('accounting.bill-purchases.show', $payment->reference) }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to Bill
                            </a>
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
    // Handle delete payment button
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