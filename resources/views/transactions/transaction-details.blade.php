@extends('layouts.main')

@section('title', 'Transaction Details - ' . $transaction->transaction_id)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Double Entries', 'url' => route('accounting.transactions.doubleEntries', Hashids::encode($transaction->chart_account_id)), 'icon' => 'bx bx-list-ul'],
            ['label' => 'Transaction Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-book-open me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">Transaction Details</h5>
                                </div>
                                <p class="mb-0 text-muted">Complete double-entry for transaction {{ $transaction->transaction_id }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('accounting.transactions.doubleEntries', Hashids::encode($transaction->chart_account_id)) }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Double Entries
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Information -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Transaction Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Transaction ID</label>
                                    <p class="form-control-plaintext">{{ $transaction->transaction_id }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Date</label>
                                    <p class="form-control-plaintext">{{ $transaction->date ? $transaction->date->format('d-m-Y') : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Type</label>
                                    <p class="form-control-plaintext">{{ ucfirst($transaction->transaction_type) }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Description</label>
                                    <p class="form-control-plaintext">{{ $transaction->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row row-cols-1 row-cols-lg-2">
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Debit</p>
                                <h4 class="font-weight-bold text-success">{{ number_format($totalDebit, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white">
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
                                <p class="mb-0">Total Credit</p>
                                <h4 class="font-weight-bold text-danger">{{ number_format($totalCredit, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-danger text-white">
                                <i class='bx bx-trending-down'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Double Entry Details -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Double Entry Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Debit Side -->
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bx bx-trending-up me-2"></i>DEBIT SIDE</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($debitTransactions as $debitTransaction)
                                                    <tr>
                                                        <td>
                                                            <span class="fw-medium">{{ $debitTransaction->chartAccount->account_name ?? 'N/A' }}</span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="text-success fw-bold">{{ number_format($debitTransaction->amount, 2) }}</span>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">No debit entries</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                                <tfoot class="table-success">
                                                    <tr>
                                                        <td class="fw-bold">TOTAL DEBIT</td>
                                                        <td class="text-end fw-bold">{{ number_format($totalDebit, 2) }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Credit Side -->
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="bx bx-trending-down me-2"></i>CREDIT SIDE</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Account</th>
                                                        <th class="text-end">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($creditTransactions as $creditTransaction)
                                                    <tr>
                                                        <td>
                                                            <span class="fw-medium">{{ $creditTransaction->chartAccount->account_name ?? 'N/A' }}</span>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="text-danger fw-bold">{{ number_format($creditTransaction->amount, 2) }}</span>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center text-muted">No credit entries</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                                <tfoot class="table-danger">
                                                    <tr>
                                                        <td class="fw-bold">TOTAL CREDIT</td>
                                                        <td class="text-end fw-bold">{{ number_format($totalCredit, 2) }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Balance Check -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-{{ $balance == 0 ? 'success' : 'warning' }} mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bx {{ $balance == 0 ? 'bx-check-circle' : 'bx-error' }} me-2"></i>
                                        <div>
                                            <strong>Balance Check:</strong> 
                                            @if($balance == 0)
                                                ✅ Transaction is balanced (Debit = Credit)
                                            @else
                                                ⚠️ Transaction is not balanced (Debit ≠ Credit)
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Transaction Details -->
        @if($transaction->journal || $transaction->paymentVoucher || $transaction->bill || $transaction->receipt)
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-link me-2"></i>Related Document</h6>
                    </div>
                    <div class="card-body">
                        @if($transaction->journal)
                            <div class="alert alert-info mb-0">
                                <strong>Journal Entry:</strong> 
                                <a href="{{ route('accounting.journals.show', $transaction->journal) }}" class="text-decoration-none">
                                    {{ $transaction->journal->reference }}
                                </a>
                            </div>
                        @elseif($transaction->paymentVoucher)
                            <div class="alert alert-success mb-0">
                                <strong>Payment Voucher:</strong> 
                                <a href="{{ route('accounting.payment-vouchers.show', $transaction->paymentVoucher) }}" class="text-decoration-none">
                                    {{ $transaction->paymentVoucher->reference }}
                                </a>
                            </div>
                        @elseif($transaction->bill)
                            <div class="alert alert-warning mb-0">
                                <strong>Bill:</strong> 
                                <a href="{{ route('accounting.bill-purchases.show', $transaction->bill) }}" class="text-decoration-none">
                                    {{ $transaction->bill->reference }}
                                </a>
                            </div>
                        @elseif($transaction->receipt)
                            <div class="alert alert-info mb-0">
                                <strong>Receipt:</strong> 
                                <a href="{{ route('accounting.receipt-vouchers.show', $transaction->receipt) }}" class="text-decoration-none">
                                    {{ $transaction->receipt->reference }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.info-item {
    margin-bottom: 1rem;
}

.info-item label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-item p {
    font-size: 1rem;
    color: #212529;
    margin-bottom: 0;
}
</style>
@endsection 