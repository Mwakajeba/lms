@extends('layouts.main')

@section('title', 'Add Payment - ' . $billPurchase->reference)

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
                        <li class="breadcrumb-item"><a href="{{ route('accounting.bill-purchases.show', $billPurchase) }}">{{ $billPurchase->reference }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add Payment</li>
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
                                    <h5 class="mb-0 text-success">Add Payment for Bill: {{ $billPurchase->reference }}</h5>
                                </div>
                                <p class="mb-0 text-muted">Process payment to reduce the outstanding balance</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ route('accounting.bill-purchases.show', $billPurchase) }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Bill
                                </a>
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
            <!-- Bill Summary -->
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Bill Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">Total Amount</label>
                                <p class="h6 text-primary">TZS {{ $billPurchase->formatted_total_amount }}</p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">Paid Amount</label>
                                <p class="h6 text-success">TZS {{ $billPurchase->formatted_paid }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Outstanding Balance</label>
                                <p class="h5 text-danger">TZS {{ $billPurchase->formatted_balance }}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label fw-bold">Supplier</label>
                                <p class="form-control-plaintext">{{ $billPurchase->supplier->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">Due Date</label>
                                <p class="form-control-plaintext">{{ $billPurchase->formatted_due_date ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bx bx-money me-2"></i>Payment Details</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('accounting.bill-purchases.process-payment', $billPurchase) }}" method="POST" id="paymentForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" 
                                           value="{{ old('date', date('Y-m-d')) }}" required>
                                    @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">TZS</span>
                                        <input type="number" name="amount" id="paymentAmount" class="form-control @error('amount') is-invalid @enderror" 
                                               step="0.01" min="0.01" max="{{ $billPurchase->balance }}" 
                                               value="{{ old('amount', $billPurchase->balance) }}" required>
                                    </div>
                                    <small class="text-muted">Maximum: TZS {{ number_format($billPurchase->balance, 2) }}</small>
                                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bank Account <span class="text-danger">*</span></label>
                                    <select name="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror" required>
                                        <option value="">-- Select Bank Account --</option>
                                        @foreach($bankAccounts as $bankAccount)
                                            <option value="{{ $bankAccount->id }}" {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                                {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bank_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                              rows="3" placeholder="Enter payment description...">{{ old('description') }}</textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>



                            <!-- Summary -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label class="form-label fw-bold">Payment Amount</label>
                                                    <p class="h5 text-success">TZS <span id="displayPaymentAmount">0.00</span></p>
                                                </div>
                                            </div>
                                            <div class="d-grid gap-2 mt-3">
                                                <button type="submit" id="submitBtn" class="btn btn-success">
                                                    <i class="bx bx-money me-1"></i> Process Payment
                                                </button>
                                                <a href="{{ route('accounting.bill-purchases.show', $billPurchase) }}" class="btn btn-outline-secondary">
                                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update payment amount display
    $('#paymentAmount').on('input', function() {
        $('#displayPaymentAmount').text(parseFloat($(this).val() || 0).toFixed(2));
    });

    // Form validation and prevent double submission
    $('#paymentForm').submit(function(e) {
        const paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
        
        if (paymentAmount <= 0) {
            e.preventDefault();
            alert('Please enter a valid payment amount.');
            return false;
        }
        
        // Disable submit button and make it feint to prevent double submission
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.addClass('opacity-50');
        submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...');
        
        // Re-enable after 5 seconds as fallback (in case of network issues)
        setTimeout(function() {
            submitBtn.prop('disabled', false);
            submitBtn.removeClass('opacity-50');
            submitBtn.html('<i class="bx bx-money me-1"></i> Process Payment');
        }, 5000);
    });

    // Initialize
    $('#displayPaymentAmount').text(parseFloat($('#paymentAmount').val() || 0).toFixed(2));
});
</script>
@endpush 