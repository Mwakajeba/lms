@extends('layouts.main')

@section('title', 'Edit Cash Deposit Transaction')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Customers', 'url' => route('customers.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Customer', 'url' => route('customers.show', Hashids::encode($collateral->customer_id)), 'icon' => 'bx bx-user'],
            ['label' => 'Cash Deposit', 'url' => route('cash_collaterals.show', Hashids::encode($collateral->id)), 'icon' => 'bx bx-money'],
            ['label' => 'Edit Deposit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        
        <h5 class="mb-0 text-primary">Edit Deposit</h5>

        <hr>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('receipts.update', Hashids::encode($receipt->id)) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bank_account_id" class="form-label">Bank Account</label>
                            <select name="bank_account_id" id="bank_account_id" class="form-select" required>
                                <option value="">-- Select Bank Account --</option>
                                @foreach($bankAccounts as $bankAccount)
                                <option value="{{ $bankAccount->id }}"
                                    {{ old('bank_account_id', $receipt->bank_account_id) == $bankAccount->id ? 'selected' : '' }}>
                                    {{ $bankAccount->name }} - {{ $bankAccount->account_number }}
                                </option>
                                @endforeach
                            </select>
                            @error('bank_account_id')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Deposit Date</label>
                            <input type="date"
                                class="form-control"
                                id="date"
                                name="date"
                                value="{{ old('date', $receipt->date->format('Y-m-d')) }}"
                                required>
                            @error('date')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">TSHS</span>
                                <input type="number"
                                    class="form-control"
                                    id="amount"
                                    name="amount"
                                    value="{{ old('amount', $receipt->amount) }}"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    required>
                            </div>
                            @error('amount')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control"
                                id="description"
                                name="description"
                                rows="3"
                                placeholder="Enter description for this deposit">{{ old('description', $receipt->description) }}</textarea>
                            @error('description')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        @can('view cah collateral')
                        <div class="col-md-6">
                            <a href="{{ route('cash_collaterals.show', Hashids::encode($collateral->id)) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back
                            </a>
                        </div>
                        @endcan
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Update Deposit
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
        // Format amount input
        $('#amount').on('input', function() {
            let value = $(this).val();
            if (value && !isNaN(value)) {
                $(this).val(parseFloat(value).toFixed(2));
            }
        });
    });
</script>
@endpush 