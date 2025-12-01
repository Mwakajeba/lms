@extends('layouts.main')

@section('title', 'Create Bank Reconciliation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Bank Reconciliation', 'url' => route('accounting.bank-reconciliation.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Create Reconciliation', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE BANK RECONCILIATION</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-plus-circle me-2"></i>New Bank Reconciliation</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('accounting.bank-reconciliation.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="row">
                                <!-- Bank Account Selection -->
                                <div class="col-md-6 mb-3">
                                    <label for="bank_account_id" class="form-label fw-bold">
                                        <i class="bx bx-bank me-1"></i>Bank Account
                                    </label>
                                    <select class="form-select form-select-lg @error('bank_account_id') is-invalid @enderror" 
                                            id="bank_account_id" name="bank_account_id" required>
                                        <option value="">Select Bank Account</option>
                                        @foreach($bankAccounts as $bankAccount)
                                            <option value="{{ $bankAccount->id }}" 
                                                    {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                                {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bank_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Reconciliation Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="reconciliation_date" class="form-label fw-bold">
                                        <i class="bx bx-calendar me-1"></i>Reconciliation Date
                                    </label>
                                    <input type="date" class="form-control form-control-lg @error('reconciliation_date') is-invalid @enderror"
                                           id="reconciliation_date" name="reconciliation_date" 
                                           value="{{ old('reconciliation_date', date('Y-m-d')) }}" required>
                                    @error('reconciliation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Start Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label fw-bold">
                                        <i class="bx bx-calendar me-1"></i>Start Date
                                    </label>
                                    <input type="date" class="form-control form-control-lg @error('start_date') is-invalid @enderror"
                                           id="start_date" name="start_date" 
                                           value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- End Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label fw-bold">
                                        <i class="bx bx-calendar me-1"></i>End Date
                                    </label>
                                    <input type="date" class="form-control form-control-lg @error('end_date') is-invalid @enderror"
                                           id="end_date" name="end_date" 
                                           value="{{ old('end_date') }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Bank Statement Balance -->
                                <div class="col-md-6 mb-3">
                                    <label for="bank_statement_balance" class="form-label fw-bold">
                                        <i class="bx bx-money me-1"></i>Bank Statement Balance
                                    </label>
                                    <input type="number" step="0.01" class="form-control form-control-lg @error('bank_statement_balance') is-invalid @enderror"
                                           id="bank_statement_balance" name="bank_statement_balance" 
                                           value="{{ old('bank_statement_balance') }}" placeholder="0.00" required>
                                    @error('bank_statement_balance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>



                                <!-- Notes -->
                                <div class="col-12 mb-3">
                                    <label for="notes" class="form-label fw-bold">
                                        <i class="bx bx-note me-1"></i>Notes
                                    </label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" 
                                              placeholder="Enter any additional notes...">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Bank Statement Document -->
                                <div class="col-12 mb-3">
                                    <label for="bank_statement_document" class="form-label fw-bold">
                                        <i class="bx bx-file-pdf me-1"></i>Bank Statement Document
                                    </label>
                                    <input type="file" class="form-control @error('bank_statement_document') is-invalid @enderror"
                                           id="bank_statement_document" name="bank_statement_document" 
                                           accept=".pdf">
                                    <div class="form-text">Upload bank statement document (PDF only)</div>
                                    @error('bank_statement_document')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('accounting.bank-reconciliation.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-2"></i>Create Reconciliation
                                </button>
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
    // Set default dates
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