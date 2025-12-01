@php
    $isEdit = isset($cashCollateralType);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $isEdit ? route('cash_collateral_types.update', $cashCollateralType) : route('cash_collateral_types.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" 
                value="{{ old('name', $cashCollateralType->name ?? '') }}" 
                class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="chart_account_id" class="form-label">Chart Account <span class="text-danger">*</span></label>
            <select name="chart_account_id" id="chart_account_id" class="form-select">
                <option value="">-- Select Chart Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}"
                        {{ old('chart_account_id', $cashCollateralType->chart_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $cashCollateralType->description ?? '') }}</textarea>
        </div>

        <div class="col-md-6 d-flex align-items-center mb-3">
            <div class="form-check mt-3">
            <input type="hidden" name="is_active" value="0"> <!-- default if not checked -->
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                {{ old('is_active', $cashCollateralType->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                Active
            </label>
        </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <a href="{{ route('cash_collateral_types.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
        <div class="col-md-6 text-end">
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span class="btn-text">
                    <i class="bx bx-{{ $isEdit ? 'edit' : 'plus' }} me-1"></i> {{ $isEdit ? 'Update' : 'Create' }}
                </span>
                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    $(document).ready(function() {
        const form = $('form');
        const submitBtn = $('#submitBtn');
        const btnText = submitBtn.find('.btn-text');
        const spinner = submitBtn.find('.spinner-border');

        // Handle form submission with loading state
        form.on('submit', function(e) {
            // Validate required fields
            let isValid = true;
            
            // Check name field
            if (!$('#name').val().trim()) {
                isValid = false;
                $('#name').addClass('is-invalid');
            } else {
                $('#name').removeClass('is-invalid');
            }

            // Check chart account field
            if (!$('#chart_account_id').val()) {
                isValid = false;
                $('#chart_account_id').addClass('is-invalid');
            } else {
                $('#chart_account_id').removeClass('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Disable the submit button to prevent double submission
            submitBtn.prop('disabled', true);
            
            // Show loading state
            btnText.html('<i class="bx bx-loader-alt bx-spin me-1"></i> {{ $isEdit ? 'Updating...' : 'Creating...' }}');
            spinner.removeClass('d-none');
            
            // Add loading class for visual feedback
            submitBtn.addClass('loading');
        });

        // Re-enable button if form validation fails (page doesn't redirect)
        setTimeout(function() {
            if (submitBtn.prop('disabled')) {
                submitBtn.prop('disabled', false);
                btnText.html('<i class="bx bx-{{ $isEdit ? 'edit' : 'plus' }} me-1"></i> {{ $isEdit ? 'Update' : 'Create' }}');
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
    
    .is-invalid {
        border-color: #dc3545;
    }
</style>
