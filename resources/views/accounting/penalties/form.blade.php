@php
    use Vinkla\Hashids\Facades\Hashids;
    $isEdit = isset($penalty);
@endphp

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

<form action="{{ $isEdit ? route('accounting.penalties.update', Hashids::encode($penalty->id)) : route('accounting.penalties.store') }}" method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Basic Information Section -->
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penalty Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $penalty->name ?? '') }}" placeholder="Enter penalty name" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">-- Select Status --</option>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $penalty->status ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                rows="3" placeholder="Enter penalty description">{{ old('description', $penalty->description ?? '') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Penalty Configuration Section -->
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Penalty Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penalty Type <span class="text-danger">*</span></label>
                            <select name="penalty_type" class="form-select @error('penalty_type') is-invalid @enderror"
                                required>
                                <option value="">-- Select Penalty Type --</option>
                                @foreach($penaltyTypeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('penalty_type', $penalty->penalty_type ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('penalty_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Charge Frequency <span class="text-danger">*</span></label>
                            <select name="charge_frequency" class="form-select @error('charge_frequency') is-invalid @enderror" required>
                                <option value="">-- Select Charge Frequency --</option>
                                @foreach($chargeFrequencyOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('charge_frequency', $penalty->charge_frequency ?? 'one_time') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('charge_frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                value="{{ old('amount', $penalty->amount ?? '') }}" min="0" step="0.01" 
                                placeholder="Enter penalty amount" required>
                            <div class="form-text">Enter amount (fixed) or percentage value</div>
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Deduction Type <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input @error('deduction_type') is-invalid @enderror" 
                                            type="radio" name="deduction_type" id="over_due_principal_amount" 
                                            value="over_due_principal_amount" 
                                            {{ old('deduction_type', $penalty->deduction_type ?? 'over_due_principal_amount') == 'over_due_principal_amount' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="over_due_principal_amount">
                                            <i class="bx bx-time me-2 text-danger"></i>
                                            <strong>Over Due Principal Amount</strong>
                                            <br>
                                            <small class="text-muted">Penalty calculated on overdue principal amount</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input @error('deduction_type') is-invalid @enderror" 
                                            type="radio" name="deduction_type" id="over_due_interest_amount" 
                                            value="over_due_interest_amount" 
                                            {{ old('deduction_type', $penalty->deduction_type ?? 'over_due_principal_amount') == 'over_due_interest_amount' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="over_due_interest_amount">
                                            <i class="bx bx-percentage me-2 text-warning"></i>
                                            <strong>Over Due Interest Amount</strong>
                                            <br>
                                            <small class="text-muted">Penalty calculated on overdue interest amount</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input @error('deduction_type') is-invalid @enderror" 
                                            type="radio" name="deduction_type" id="over_due_principal_and_interest" 
                                            value="over_due_principal_and_interest" 
                                            {{ old('deduction_type', $penalty->deduction_type ?? 'over_due_principal_amount') == 'over_due_principal_and_interest' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="over_due_principal_and_interest">
                                            <i class="bx bx-calculator me-2 text-danger"></i>
                                            <strong>Over Due Principal and Interest</strong>
                                            <br>
                                            <small class="text-muted">Penalty calculated on overdue principal + interest</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input @error('deduction_type') is-invalid @enderror" 
                                            type="radio" name="deduction_type" id="total_principal_amount_released" 
                                            value="total_principal_amount_released" 
                                            {{ old('deduction_type', $penalty->deduction_type ?? 'over_due_principal_amount') == 'total_principal_amount_released' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="total_principal_amount_released">
                                            <i class="bx bx-dollar-circle me-2 text-info"></i>
                                            <strong>Total Principal Amount Released</strong>
                                            <br>
                                            <small class="text-muted">Penalty calculated on total principal amount released</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('deduction_type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Account Section -->
        <div class="col-12">
            <div class="card radius-10 mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bx bx-book-open me-2"></i>Chart Accounts</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penalty Income Account <span class="text-danger">*</span></label>
                            <select name="penalty_income_account_id" class="form-select @error('penalty_income_account_id') is-invalid @enderror" required>
                                <option value="">-- Select Penalty Income Account --</option>
                                @foreach($penaltyIncomeAccounts as $account)
                                    <option value="{{ $account->id }}" 
                                        {{ old('penalty_income_account_id', $penalty->penalty_income_account_id ?? '') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_name }} ({{ $account->account_code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Select the chart account for penalty income</div>
                            @error('penalty_income_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penalty Receivable Account <span class="text-danger">*</span></label>
                            <select name="penalty_receivables_account_id" class="form-select @error('penalty_receivables_account_id') is-invalid @enderror" required>
                                <option value="">-- Select Penalty Receivable Account --</option>
                                @foreach($penaltyReceivablesAccounts as $account)
                                    <option value="{{ $account->id }}" 
                                        {{ old('penalty_receivables_account_id', $penalty->penalty_receivables_account_id ?? '') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_name }} ({{ $account->account_code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Select the chart account for penalty receivables</div>
                            @error('penalty_receivables_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Form Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('accounting.penalties.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-2"></i>{{ $isEdit ? 'Update Penalty' : 'Create Penalty' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    // Auto-update amount placeholder based on penalty type
    $('select[name="penalty_type"]').on('change', function() {
        const penaltyType = $(this).val();
        const amountInput = $('input[name="amount"]');
        
        if (penaltyType === 'percentage') {
            amountInput.attr('placeholder', 'Enter percentage (e.g., 5.5 for 5.5%)');
            amountInput.attr('max', '100');
        } else {
            amountInput.attr('placeholder', 'Enter fixed amount');
            amountInput.removeAttr('max');
        }
    });

    // Trigger change event on page load
    $(document).ready(function() {
        $('select[name="penalty_type"]').trigger('change');
    });
</script>
@endpush