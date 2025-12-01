@extends('layouts.main')

@section('title', 'Cash Deposit')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
    ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
    ['label' => 'Customers', 'url' => route('customers.index'), 'icon' => 'bx bx-group'],
    ['label' => 'Customer', 'url' => route('customers.show', Hashids::encode($customer->id)), 'icon' => 'bx bx-user'],
    ['label' => 'Deposit', 'url' => '#', 'icon' => 'bx bx-user']
]" />
        
        <h5 class="mb-0 text-primary">Deposit</h5>

        <hr>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('cash_collaterals.depositStore') }}" method="POST" id="depositForm">
                    @csrf
                    <input type="hidden" name="collateral_id" value="{{ Hashids::encode($collateral->id) }}" />

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bank_account_id" class="form-label">Received To (Bank Account)</label>
                            <select name="bank_account_id" id="bank_account_id" class="form-select" required>
                                <option value="">-- Select Bank Account --</option>
                                @foreach($bankAccounts as $bankAccount)
                                <option value="{{ $bankAccount->id }}"
                                    {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                    {{ $bankAccount->name }} - {{ $bankAccount->account_number }}
                                </option>
                                @endforeach
                            </select>
                            @error('bank_account_id')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="deposit_date" class="form-label">Deposit Date</label>
                            <input type="date"
                                class="form-control"
                                id="deposit_date"
                                name="deposit_date"
                                value="{{ old('deposit_date', date('Y-m-d')) }}"
                                required>
                            @error('deposit_date')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">TSHS</span>
                                <input type="number"
                                    class="form-control"
                                    name="amount"
                                    value="{{ old('amount') }}"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0"
                                    required>
                            </div>
                            @error('amount')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="notes" class="form-label">Notes <span class="text-danger">*</span></label>
                            <textarea class="form-control"
                                id="notes"
                                name="notes"
                                rows="3"
                                placeholder="Enter notes about this deposit"
                                required>{{ old('notes') }}</textarea>
                            @error('notes')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <a href="{{ route('customers.show', Hashids::encode($customer->id))}}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Cancel
                            </a>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="btn-text">
                                    <i class="bx bx-save me-1"></i> Process Deposit
                                </span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const form = $('#depositForm');
        const submitBtn = $('#submitBtn');
        const btnText = submitBtn.find('.btn-text');
        const spinner = submitBtn.find('.spinner-border');

        // Auto-select today's date if not already set
        if (!$('#deposit_date').val()) {
            $('#deposit_date').val(new Date().toISOString().split('T')[0]);
        }

        // Handle form submission with loading state
        form.on('submit', function(e) {
            // Disable the submit button to prevent double submission
            submitBtn.prop('disabled', true);
            
            // Show loading state
            btnText.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Processing...');
            spinner.removeClass('d-none');
            
            // Add loading class for visual feedback
            submitBtn.addClass('loading');
        });

        // Re-enable button if form validation fails (page doesn't redirect)
        setTimeout(function() {
            if (submitBtn.prop('disabled')) {
                submitBtn.prop('disabled', false);
                btnText.html('<i class="bx bx-save me-1"></i> Process Deposit');
                spinner.addClass('d-none');
                submitBtn.removeClass('loading');
            }
        }, 5000); // Reset after 5 seconds if still on page
    });
</script>

<style>
    .btn.loading {
        position: relative;
        pointer-events: none;
    }
    
    .btn .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        margin-left: 0.5rem;
    }
</style>
@endpush