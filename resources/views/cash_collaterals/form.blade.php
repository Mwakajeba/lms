<form
    action="{{ isset($collateral) ? route('cash_collaterals.update', $collateral) : route('cash_collaterals.store') }}"
    method="POST" id="collateralForm">
    @csrf
    @if(isset($collateral))
    @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="customer_id" class="form-label">Customer</label>
            <select name="customer_id" id="customer_id" class="form-select" required>
                <option value="">-- Select Customer --</option>
                @foreach($customers as $customer)
                <option value="{{ $customer->id }}"
                    {{ old('customer_id', $collateral->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                    {{ $customer->name }}
                </option>
                @endforeach
            </select>
            @error('customer_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="type_id" class="form-label">Deposit Type</label>
            <select name="type_id" id="type_id" class="form-select" required>
                <option value="">-- Select Type --</option>
                @foreach($types as $index => $type)
                <option value="{{ $type->id }}"
                    {{ old('type_id', $collateral->type_id ?? '') == $type->id ? 'selected' : '' }}>
                    {{ $type->name }}
                </option>
                @endforeach
            </select>
            @error('type_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">Available Account Types</label>
            <div class="card">
                <div class="card-body">
                    @foreach($types as $index => $type)
                    <div class="form-check mb-2">
                        <input class="form-check-input account-type-checkbox" 
                               type="checkbox" 
                               value="{{ $type->id }}" 
                               id="account_type_{{ $type->id }}"
                               {{ $index === 0 ? 'checked required' : '' }}
                               name="selected_account_types[]">
                        <label class="form-check-label" for="account_type_{{ $type->id }}">
                            <strong>{{ $type->name }}</strong>
                            @if($type->description)
                            <br><small class="text-muted">{{ $type->description }}</small>
                            @endif
                        </label>
                    </div>
                    @endforeach
                    <small class="text-muted">* At least one account type must be selected</small>
                </div>
            </div>
            @error('selected_account_types') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <a href="{{ route('cash_collaterals.index') }}" class="btn btn-secondary">Back</a>
        </div>
        <div class="col-md-6 text-end">
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span class="btn-text">{{ isset($collateral) ? 'Update' : 'Create' }}</span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    $(document).ready(function() {
        const form = $('#collateralForm');
        const submitBtn = $('#submitBtn');
        const btnText = submitBtn.find('.btn-text');
        const spinner = submitBtn.find('.spinner-border');
        const typeSelect = $('#type_id');
        const checkboxes = $('.account-type-checkbox');

        // Synchronize dropdown with checkboxes
        typeSelect.on('change', function() {
            const selectedValue = $(this).val();
            checkboxes.prop('checked', false);
            if (selectedValue) {
                $(`#account_type_${selectedValue}`).prop('checked', true);
            }
        });

        // Synchronize checkboxes with dropdown
        checkboxes.on('change', function() {
            const checkedBoxes = checkboxes.filter(':checked');
            
            // Ensure at least one checkbox is checked
            if (checkedBoxes.length === 0) {
                $(this).prop('checked', true);
                alert('At least one account type must be selected.');
                return;
            }

            // Update dropdown to match the first checked checkbox
            const firstCheckedValue = checkedBoxes.first().val();
            typeSelect.val(firstCheckedValue);
        });

        // Form validation
        form.on('submit', function(e) {
            const checkedBoxes = checkboxes.filter(':checked');
            
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one account type.');
                return;
            }

            // Disable the submit button to prevent double submission
            submitBtn.prop('disabled', true);
            
            // Show loading state
            btnText.text('{{ isset($collateral) ? 'Updating...' : 'Creating...' }}');
            spinner.removeClass('d-none');
            
            // Add loading class for visual feedback
            submitBtn.addClass('loading');
        });

        // Re-enable button if form validation fails (page doesn't redirect)
        setTimeout(function() {
            if (submitBtn.prop('disabled')) {
                submitBtn.prop('disabled', false);
                btnText.text('{{ isset($collateral) ? 'Update' : 'Create' }}');
                spinner.addClass('d-none');
                submitBtn.removeClass('loading');
            }
        }, 5000); // Reset after 5 seconds if still on page
    });
</script>
@endpush

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