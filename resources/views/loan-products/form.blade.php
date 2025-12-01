@php
    use Vinkla\Hashids\Facades\Hashids;
    $isEdit = isset($loanProduct);
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

<form
    action="{{ $isEdit ? route('loan-products.update', Hashids::encode($loanProduct->id)) : route('loan-products.store') }}"
    onsubmit="return handleSubmit(this)" method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Basic Information -->
        <div class="col-12">
            <h5 class="mb-3 text-primary">Basic Information</h5>
        </div>

        <!-- Name -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Product Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $loanProduct->name ?? '') }}" placeholder="Enter product name">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Product Type -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Product Type <span class="text-danger">*</span></label>
            <select name="product_type" class="form-select @error('product_type') is-invalid @enderror" required>
                <option value="">-- Select Product Type --</option>
                @foreach($productTypes as $key => $value)
                    <option value="{{ $key }}" {{ old('product_type', $loanProduct->product_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('product_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Interest Rate Range -->

        <div class="col-md-6 mb-3">
            <label class="form-label">Minimum Interest Rate (%) <span class="text-danger">*</span></label>
            <input type="number" name="minimum_interest_rate" step="0.000000000000001" min="0" max="100"
                class="form-control @error('minimum_interest_rate') is-invalid @enderror"
                value="{{ old('minimum_interest_rate', $loanProduct->minimum_interest_rate ?? '') }}"
                placeholder="0.00">
            <small class="text-muted">Up to 16 digits before and 15 after decimal</small>
            @error('minimum_interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Maximum Interest Rate (%) <span class="text-danger">*</span></label>
            <input type="number" name="maximum_interest_rate" step="0.000000000000001" min="0" max="100"
                class="form-control @error('maximum_interest_rate') is-invalid @enderror"
                value="{{ old('maximum_interest_rate', $loanProduct->maximum_interest_rate ?? '') }}"
                placeholder="0.00">
            <small class="text-muted">Up to 16 digits before and 15 after decimal</small>
            @error('maximum_interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Interest Cycle and Method -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Interest Cycle <span class="text-danger">*</span></label>
            <select name="interest_cycle" class="form-select @error('interest_cycle') is-invalid @enderror" required>
                <option value="">-- Select Interest Cycle --</option>
                @foreach($interestCycles as $key => $value)
                    <option value="{{ $key }}" {{ old('interest_cycle', $loanProduct->interest_cycle ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('interest_cycle') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Interest Method <span class="text-danger">*</span></label>
            <select name="interest_method" class="form-select @error('interest_method') is-invalid @enderror" required>
                <option value="">-- Select Interest Method --</option>
                @foreach($interestMethods as $key => $value)
                    <option value="{{ $key }}" {{ old('interest_method', $loanProduct->interest_method ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('interest_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Principal Range -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Minimum Principal <span class="text-danger">*</span></label>
            <input type="number" name="minimum_principal" step="0.000000000000001" min="0"
                class="form-control @error('minimum_principal') is-invalid @enderror"
                value="{{ old('minimum_principal', $loanProduct->minimum_principal ?? '') }}" placeholder="0.00">
            @error('minimum_principal') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Maximum Principal <span class="text-danger">*</span></label>
            <input type="number" name="maximum_principal" step="0.000000000000001" min="0"
                class="form-control @error('maximum_principal') is-invalid @enderror"
                value="{{ old('maximum_principal', $loanProduct->maximum_principal ?? '') }}" placeholder="0.00">
            @error('maximum_principal') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Period Range -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Minimum Period <span class="text-danger">*</span></label>
            <input type="number" name="minimum_period" min="1"
                class="form-control @error('minimum_period') is-invalid @enderror"
                value="{{ old('minimum_period', $loanProduct->minimum_period ?? '') }}" placeholder="1">
            @error('minimum_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Maximum Period <span class="text-danger">*</span></label>
            <input type="number" name="maximum_period" min="1"
                class="form-control @error('maximum_period') is-invalid @enderror"
                value="{{ old('maximum_period', $loanProduct->maximum_period ?? '') }}" placeholder="12">
            @error('maximum_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Grace Period (Optional) -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Grace Period (days)</label>
            <input type="number" name="grace_period" min="0"
                class="form-control @error('grace_period') is-invalid @enderror"
                value="{{ old('grace_period', $loanProduct->grace_period ?? '') }}" placeholder="0">
            @error('grace_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Maximum Number of Loans (Optional) -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Maximum Number of Loans</label>
            <input type="number" name="maximum_number_of_loans" min="1"
                class="form-control @error('maximum_number_of_loans') is-invalid @enderror"
                value="{{ old('maximum_number_of_loans', $loanProduct->maximum_number_of_loans ?? '') }}"
                placeholder="Leave empty for unlimited">
            <small class="text-muted">Maximum number of loans a customer can have with this product (leave empty for
                unlimited)</small>
            @error('maximum_number_of_loans') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>


        <div class="col-md-6 mb-3">
            <label class="form-label">Penalty Criteria Deduction <span class="text-danger">*</span></label>
            <select name="penalt_deduction_criteria" id="penalt_deduction_criteria"
                class="form-select @error('penalt_deduction_criteria') is-invalid @enderror">
                <option value="">-- Select Deduction Type --</option>
                @foreach($penaltycriteriaDeductions as $key => $value)
                    <option value="{{ $key }}" {{ old('penalt_deduction_criteria', $loanProduct->penalt_deduction_criteria ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('penalt_deduction_criteria') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Top Up Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Top Up Configuration</h5>
        </div>

        <div class="col-md-6 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_top_up" id="has_top_up" value="1" {{ old('has_top_up', isset($loanProduct) && !empty($loanProduct->top_up_type)) ? 'checked' : '' }}>
                <label class="form-check-label" for="has_top_up">
                    Has Top Up
                </label>
            </div>
        </div>

        <div class="col-md-6 mb-3" id="top_up_type_div" style="display: none;">
            <label class="form-label">Top Up Type <span class="text-danger">*</span></label>
            <select name="top_up_type" id="top_up_type" class="form-select @error('top_up_type') is-invalid @enderror">
                <option value="">-- Select Top Up Type --</option>
                @foreach($topUpTypes as $key => $value)
                    <option value="{{ $key }}" {{ old('top_up_type', $loanProduct->top_up_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('top_up_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3" id="top_up_value_div" style="display: none;">
            <label class="form-label">Top Up Value <span></span></label>
            <input type="number" name="top_up_type_value"
                class="form-control @error('top_up_type_value') is-invalid @enderror"
                value="{{ old('top_up_type_value', $loanProduct->top_up_type_value ?? '') }}" placeholder="0.00">
            @error('top_up_type_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <!-- Allow Push to ESS -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">ESS Configuration</h5>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-check">
                <input type="checkbox" name="allow_push_to_ess" id="allow_push_to_ess" class="form-check-input"
                    value="1" {{ old('allow_push_to_ess', $loanProduct->allow_push_to_ess ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="allow_push_to_ess">Allow Push to ESS</label>
            </div>
        </div>

        <!-- Cash Deposit Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Cash Deposit Configuration</h5>
        </div>

        <div class="col-md-6 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_cash_collateral" id="has_cash_collateral"
                    value="1" {{ old('has_cash_collateral', $loanProduct->has_cash_collateral ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="has_cash_collateral">
                    Has Cash Deposit
                </label>
            </div>
        </div>

        <div class="col-md-6 mb-3" id="cash_collateral_type_div" style="display: none;">
            <label class="form-label">Cash Deposit Account</label>
            <select name="cash_collateral_type" class="form-select @error('cash_collateral_type') is-invalid @enderror">
                <option value="">-- Select Cash Deposit Account --</option>
                @foreach($cashCollateralTypes as $collateralType)
                    <option value="{{ $collateralType->name }}" {{ old('cash_collateral_type', $loanProduct->cash_collateral_type ?? '') == $collateralType->name ? 'selected' : '' }}>
                        {{ $collateralType->name }}
                    </option>
                @endforeach
            </select>
            @error('cash_collateral_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3" id="cash_collateral_value_type_div" style="display: none;">
            <label class="form-label">Cash Deposit Value Type</label>
            <select name="cash_collateral_value_type"
                class="form-select @error('cash_collateral_value_type') is-invalid @enderror">
                <option value="">-- Select Value Type --</option>
                @foreach($cashCollateralValueTypes as $key => $value)
                    <option value="{{ $key }}" {{ old('cash_collateral_value_type', $loanProduct->cash_collateral_value_type ?? '') == $key ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
            @error('cash_collateral_value_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6 mb-3" id="cash_collateral_value_div" style="display: none;">
            <label class="form-label">Cash Deposit Value</label>
            <input type="number" name="cash_collateral_value" step="0.000000000000001" min="0"
                class="form-control @error('cash_collateral_value') is-invalid @enderror"
                value="{{ old('cash_collateral_value', $loanProduct->cash_collateral_value ?? '') }}"
                placeholder="0.00">
            @error('cash_collateral_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Approval Levels Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Approval Configuration</h5>
        </div>

        <div class="col-md-6 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_approval_levels" id="has_approval_levels"
                    value="1" {{ old('has_approval_levels', $loanProduct->has_approval_levels ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="has_approval_levels">
                    Has Approval Levels
                </label>
            </div>
        </div>

        <div class="col-12" id="approval_levels_div" style="display: none;">
            <div class="card border">
                <div class="card-body">
                    <h6 class="card-title mb-3">Approval Levels Configuration</h6>
                    <p class="text-muted small mb-3">Select roles from the left and move them to the right to define the
                        approval hierarchy. The first role selected will be the first to approve (First In, Last Out).
                    </p>

                    <div class="row">
                        <!-- Available Roles (Left) -->
                        <div class="col-md-5">
                            <label class="form-label">Available Roles</label>
                            <select id="available_roles" class="form-select" size="8" multiple>
                                @if(isset($loanProduct) && $loanProduct->approval_levels)
                                    @php
                                        $selectedRolesRaw = $loanProduct->approval_levels;
                                        $selectedRoles = is_array($selectedRolesRaw)
                                            ? array_map('trim', $selectedRolesRaw)
                                            : array_map('trim', explode(',', $selectedRolesRaw));
                                        $selectedRoleIds = [];
                                        foreach ($selectedRoles as $roleIdentifier) {
                                            $roleIdentifier = trim($roleIdentifier);
                                            if (is_numeric($roleIdentifier)) {
                                                $selectedRoleIds[] = (int) $roleIdentifier;
                                            } else {
                                                $role = $roles->where('name', $roleIdentifier)->first();
                                                if ($role) {
                                                    $selectedRoleIds[] = $role->id;
                                                }
                                            }
                                        }
                                    @endphp
                                    @foreach($roles as $role)
                                        @if(!in_array($role->id, $selectedRoleIds))
                                            <option value="{{ $role->id }}" data-description="{{ $role->description ?? '' }}">
                                                {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                            </option>
                                        @endif
                                    @endforeach
                                @else
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" data-description="{{ $role->description ?? '' }}">
                                            {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple roles</small>
                        </div>

                        <!-- Move Buttons -->
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            <button type="button" id="move_right" class="btn btn-sm btn-primary mb-2">
                                <i class="bx bx-right-arrow-alt"></i> Add
                            </button>
                            <button type="button" id="move_left" class="btn btn-sm btn-secondary">
                                <i class="bx bx-left-arrow-alt"></i> Remove
                            </button>
                        </div>

                        <!-- Selected Roles (Right) -->
                        <div class="col-md-5">
                            <label class="form-label">Approval Hierarchy</label>
                            <select id="selected_roles" name="approval_levels"
                                class="form-select @error('approval_levels') is-invalid @enderror" size="8" multiple>
                                @if(isset($loanProduct) && $loanProduct->approval_levels)
                                    @php
                                        $selectedRolesRaw = $loanProduct->approval_levels;
                                        $selectedRoles = is_array($selectedRolesRaw)
                                            ? array_map('trim', $selectedRolesRaw)
                                            : array_map('trim', explode(',', $selectedRolesRaw));
                                    @endphp
                                    @foreach($selectedRoles as $roleIdentifier)
                                        @php
                                            $roleIdentifier = trim($roleIdentifier);
                                            // Check if it's a role ID (numeric) or role name (string)
                                            if (is_numeric($roleIdentifier)) {
                                                $role = $roles->where('id', $roleIdentifier)->first();
                                            } else {
                                                $role = $roles->where('name', $roleIdentifier)->first();
                                            }
                                        @endphp
                                        @if($role)
                                            <option value="{{ $role->id }}" data-description="{{ $role->description ?? '' }}">
                                                {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Drag to reorder approval sequence</small>

                            <!-- Hidden input to ensure approval levels are sent -->
                            <input type="hidden" id="approval_levels_hidden" name="approval_levels_hidden"
                                value="{{ isset($loanProduct) && !empty($loanProduct->approval_levels) ? (is_array($loanProduct->approval_levels) ? implode(',', $loanProduct->approval_levels) : $loanProduct->approval_levels) : '' }}">
                        </div>
                    </div>

                    <!-- Role Description -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div id="role_description" class="alert alert-info" style="display: none;">
                                <strong>Role Description:</strong> <span id="description_text"></span>
                            </div>
                        </div>
                    </div>

                    @error('approval_levels') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Chart Accounts Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Chart Accounts Configuration</h5>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Principal Receivable Account <span class="text-danger">*</span></label>
            <select name="principal_receivable_account_id"
                class="form-select select2-single @error('principal_receivable_account_id') is-invalid @enderror"
                required>
                <option value="">-- Select Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('principal_receivable_account_id', $loanProduct->principal_receivable_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
            @error('principal_receivable_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Interest Receivable Account <span class="text-danger">*</span></label>
            <select name="interest_receivable_account_id"
                class="form-select select2-single @error('interest_receivable_account_id') is-invalid @enderror"
                required>
                <option value="">-- Select Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('interest_receivable_account_id', $loanProduct->interest_receivable_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
            @error('interest_receivable_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label">Interest Revenue Account <span class="text-danger">*</span></label>
            <select name="interest_revenue_account_id"
                class="form-select select2-single @error('interest_revenue_account_id') is-invalid @enderror" required>
                <option value="">-- Select Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('interest_revenue_account_id', $loanProduct->interest_revenue_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
            @error('interest_revenue_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Write Off Accounts Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Write Off Accounts</h5>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Direct Write Off Account (Expense)</label>
            <select name="direct_writeoff_account_id" class="form-select select2-single">
                <option value="">-- Select Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('direct_writeoff_account_id', $loanProduct->direct_writeoff_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Using Provision Account (Asset)</label>
            <select name="provision_writeoff_account_id" class="form-select select2-single">
                <option value="">-- Select Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('provision_writeoff_account_id', $loanProduct->provision_writeoff_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Income Provision Account (Income)</label>
            <select name="income_provision_account_id" class="form-select select2-single">
                <option value="">-- Select Account --</option>
                @foreach($chartAccounts as $account)
                    <option value="{{ $account->id }}" {{ old('income_provision_account_id', $loanProduct->income_provision_account_id ?? '') == $account->id ? 'selected' : '' }}>
                        {{ $account->account_code }} - {{ $account->account_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <!-- Fees and Penalties Configuration -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Fees and Penalties Configuration</h5>
        </div>

        <!-- Fees Configuration -->
        <div class="col-12 mb-4">
            <div class="card border">
                <div class="card-body">
                    <h6 class="card-title mb-3">Default Fees</h6>
                    <p class="text-muted small mb-3">Add multiple fees that will be applied to loans using this product.
                    </p>

                    <div id="fees_container">
                        @if(isset($loanProduct) && $loanProduct->fees_ids)
                            @foreach($loanProduct->fees_ids as $index => $feeId)
                                <div class="row fee-row mb-2">
                                    <div class="col-md-10">
                                        <select name="fees_id[]"
                                            class="form-select fee-select @error('fees_id') is-invalid @enderror">
                                            <option value="">-- Select Fee --</option>
                                            @foreach($fees as $fee)
                                                <option value="{{ $fee->id }}" {{ $feeId == $fee->id ? 'selected' : '' }}>
                                                    {{ $fee->name }} ({{ $fee->fee_type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger remove-fee">
                                            <i class="bx bx-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="row fee-row mb-2">
                                <div class="col-md-10">
                                    <select name="fees_id[]"
                                        class="form-select fee-select @error('fees_id') is-invalid @enderror">
                                        <option value="">-- Select Fee --</option>
                                        @foreach($fees as $fee)
                                            <option value="{{ $fee->id }}">
                                                {{ $fee->name }} ({{ $fee->fee_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-fee" style="display: none;">
                                        <i class="bx bx-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="button" id="add_fee" class="btn btn-sm btn-success">
                                <i class="bx bx-plus"></i> Add Another Fee
                            </button>
                        </div>
                    </div>

                    @error('fees_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Penalties Configuration -->
        <div class="col-12 mb-4">
            <div class="card border">
                <div class="card-body">
                    <h6 class="card-title mb-3">Default Penalties</h6>
                    <p class="text-muted small mb-3">Add multiple penalties that will be applied to loans using this
                        product.</p>

                    <div id="penalties_container">
                        @if(isset($loanProduct) && $loanProduct->penalty_ids)
                            @foreach($loanProduct->penalty_ids as $index => $penaltyId)
                                <div class="row penalty-row mb-2">
                                    <div class="col-md-10">
                                        <select name="penalty_id[]"
                                            class="form-select penalty-select @error('penalty_id') is-invalid @enderror">
                                            <option value="">-- Select Penalty --</option>
                                            @foreach($penalties as $penalty)
                                                <option value="{{ $penalty->id }}" {{ $penaltyId == $penalty->id ? 'selected' : '' }}>
                                                    {{ $penalty->name }} ({{ $penalty->penalty_type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-sm btn-danger remove-penalty">
                                            <i class="bx bx-trash"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="row penalty-row mb-2">
                                <div class="col-md-10">
                                    <select name="penalty_id[]"
                                        class="form-select penalty-select @error('penalty_id') is-invalid @enderror">
                                        <option value="">-- Select Penalty --</option>
                                        @foreach($penalties as $penalty)
                                            <option value="{{ $penalty->id }}">
                                                {{ $penalty->name }} ({{ $penalty->penalty_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-penalty"
                                        style="display: none;">
                                        <i class="bx bx-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="button" id="add_penalty" class="btn btn-sm btn-success">
                                <i class="bx bx-plus"></i> Add Another Penalty
                            </button>
                        </div>
                    </div>

                    @error('penalty_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Repayment Order -->
        <div class="col-12">
            <h5 class="mb-3 text-primary mt-4">Repayment Configuration</h5>
        </div>

        <div class="col-12">
            <div class="card border">
                <div class="card-body">
                    <h5 class="card-title mb-4">Repayment Order Configuration</h5>
                    <p class="text-muted">Configure the order in which loan repayments will be allocated. You have the
                        right to define the order of payment allocation. The first component will be paid first.</p>

                    <div class="row">
                        <!-- Available Components (Left) -->
                        <div class="col-md-5">
                            <label class="form-label">Available Components</label>
                            <select id="available_repayment_components" class="form-select" size="6" multiple>
                                @php
                                    $allComponents = ['principal', 'interest', 'fees', 'penalties'];
                                    $selectedComponents = [];
                                    if (isset($loanProduct) && !empty($loanProduct->repayment_order)) {
                                        $selectedComponents = is_array($loanProduct->repayment_order)
                                            ? array_map('trim', $loanProduct->repayment_order)
                                            : array_map('trim', explode(',', $loanProduct->repayment_order));
                                    }
                                    $availableComponents = array_diff($allComponents, $selectedComponents);
                                @endphp

                                @foreach($availableComponents as $component)
                                    @php
                                        $componentLabels = [
                                            'principal' => 'Principal',
                                            'interest' => 'Interest',
                                            'fees' => 'Fees',
                                            'penalties' => 'Penalties'
                                        ];
                                        $componentDescriptions = [
                                            'principal' => 'Principal amount of the loan',
                                            'interest' => 'Interest charges on the loan',
                                            'fees' => 'Additional fees and charges',
                                            'penalties' => 'Late payment penalties'
                                        ];
                                    @endphp
                                    <option value="{{ $component }}"
                                        data-description="{{ $componentDescriptions[$component] }}">
                                        {{ $componentLabels[$component] }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple components</small>
                        </div>

                        <!-- Move Buttons -->
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            <button type="button" id="move_repayment_right" class="btn btn-sm btn-primary mb-2">
                                <i class="bx bx-right-arrow-alt"></i> Add
                            </button>
                            <button type="button" id="move_repayment_left" class="btn btn-sm btn-secondary">
                                <i class="bx bx-left-arrow-alt"></i> Remove
                            </button>
                        </div>

                        <!-- Selected Components (Right) -->
                        <div class="col-md-5">
                            <label class="form-label">Repayment Order</label>
                            <select id="selected_repayment_components" name="repayment_order[]"
                                class="form-select @error('repayment_order') is-invalid @enderror" size="6" multiple>
                                @if(isset($loanProduct) && $loanProduct->repayment_order)
                                    @php
                                        $selectedComponentsRaw = $loanProduct->repayment_order;
                                        $selectedComponents = is_array($selectedComponentsRaw)
                                            ? array_map('trim', $selectedComponentsRaw)
                                            : array_map('trim', explode(',', $selectedComponentsRaw));
                                    @endphp
                                    @foreach($selectedComponents as $component)
                                        @php
                                            $componentLabels = [
                                                'principal' => 'Principal',
                                                'interest' => 'Interest',
                                                'fees' => 'Fees',
                                                'penalties' => 'Penalties'
                                            ];
                                            $componentDescriptions = [
                                                'principal' => 'Principal amount of the loan',
                                                'interest' => 'Interest charges on the loan',
                                                'fees' => 'Additional fees and charges',
                                                'penalties' => 'Late payment penalties'
                                            ];
                                        @endphp
                                        @if(isset($componentLabels[$component]))
                                            <option value="{{ $component }}"
                                                data-description="{{ $componentDescriptions[$component] }}">
                                                {{ $componentLabels[$component] }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Drag to reorder payment sequence</small>

                            <!-- Hidden input to ensure data is always sent -->
                            <input type="hidden" id="repayment_order_hidden" name="repayment_order_hidden"
                                value="{{ isset($loanProduct) && !empty($loanProduct->repayment_order) ? (is_array($loanProduct->repayment_order) ? implode(',', $loanProduct->repayment_order) : $loanProduct->repayment_order) : 'principal,interest,fees,penalties' }}">
                        </div>
                    </div>

                    <!-- Component Description -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div id="repayment_component_description" class="alert alert-info" style="display: none;">
                                <strong>Component Description:</strong> <span id="repayment_description_text"></span>
                            </div>
                        </div>
                    </div>

                    @error('repayment_order') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('loan-products.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-save"></i> {{ $isEdit ? 'Update' : 'Create' }} Loan Product
                </button>
            </div>
        </div>
    </div>
</form>

@push('styles')
    <style>
        .dragging {
            opacity: 0.5;
            background-color: #e3f2fd !important;
        }

        #available_roles,
        #selected_roles,
        #available_repayment_components,
        #selected_repayment_components {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        #available_roles option,
        #selected_roles option,
        #available_repayment_components option,
        #selected_repayment_components option {
            padding: 8px 12px;
            border-bottom: 1px solid #f8f9fa;
            cursor: pointer;
        }

        #available_roles option:hover,
        #selected_roles option:hover,
        #available_repayment_components option:hover,
        #selected_repayment_components option:hover {
            background-color: #f8f9fa;
        }

        #selected_roles option,
        #selected_repayment_components option {
            background-color: #e3f2fd;
        }

        .approval-levels-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function handleSubmit(form) {
            // Prevent multiple submissions
            if (form.dataset.submitted === "true") return false;
            form.dataset.submitted = "true";

            // Disable ALL submit buttons in this form
            form.querySelectorAll('button[type="submit"]').forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.setAttribute('aria-disabled', 'true');

                const label = btn.querySelector('.label');
                const spinner = btn.querySelector('.spinner');
                if (label) label.textContent = 'Processing...';
                if (spinner) spinner.classList.remove('hidden');
            });

            // Optional: block whole page clicks while submitting
            const ov = document.getElementById('pageOverlay');
            if (ov) ov.classList.remove('hidden');

            // Allow the submit to proceed
            return true;
        }

        // Optional safety: prevent Enter-key spamming multiple submits in some browsers
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const active = document.activeElement;
                // Only submit on Enter when focused on a button or inside a textarea (adjust to your UX)
                if (active && active.tagName !== 'TEXTAREA' && active.type !== 'submit') {
                    // e.preventDefault(); // uncomment if Enter should NOT submit forms
                }
            }
        });
    </script>
@endpush



@push('scripts')
    <script>
        (function () {
            function ensureSelect2(cb) {
                if (window.jQuery && jQuery.fn && jQuery.fn.select2) { cb(); return; }
                    var s = document.createElement('script'); s.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
                    s.onload = cb; document.head.appendChild(s);
                    var l = document.createElement('link'); l.rel = 'stylesheet'; l.href = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
            document.head.appendChild(l);
        }
                ensureSelect2(function () {
            jQuery('.select2-single').select2({ width: '100%' });
        });
    })();
    </script>
@endpush

@push('scripts')
    <script>
            (function () {
                function setDisplay(id, show) {
            var el = document.getElementById(id);
            if (!el) return;
            el.style.display = show ? '' : 'none';
        }

                function toggleTopUp() {
            var hasTopUp = document.getElementById('has_top_up');
            var isChecked = hasTopUp && hasTopUp.checked;
            setDisplay('top_up_type_div', isChecked);
            var type = document.getElementById('top_up_type');
            var showValue = isChecked && type && type.value !== '' && type.value !== 'none';
            setDisplay('top_up_value_div', showValue);
        }

                function toggleCashCollateral() {
            var hasCash = document.getElementById('has_cash_collateral');
            var isChecked = hasCash && hasCash.checked;
            setDisplay('cash_collateral_type_div', isChecked);
            setDisplay('cash_collateral_value_type_div', isChecked);
            setDisplay('cash_collateral_value_div', isChecked);
        }

                function toggleApprovalLevels() {
            var hasApproval = document.getElementById('has_approval_levels');
            var isChecked = hasApproval && hasApproval.checked;
            setDisplay('approval_levels_div', isChecked);
        }

                document.addEventListener('DOMContentLoaded', function () {
            // Initial state
            toggleTopUp();
            toggleCashCollateral();
            toggleApprovalLevels();

            // Listeners
            var hasTopUp = document.getElementById('has_top_up');
            if (hasTopUp) hasTopUp.addEventListener('change', toggleTopUp);
            var topUpType = document.getElementById('top_up_type');
            if (topUpType) topUpType.addEventListener('change', toggleTopUp);

            var hasCash = document.getElementById('has_cash_collateral');
            if (hasCash) hasCash.addEventListener('change', toggleCashCollateral);

            var hasApproval = document.getElementById('has_approval_levels');
            if (hasApproval) hasApproval.addEventListener('change', toggleApprovalLevels);
        });
    })();
    </script>
@endpush

@push('scripts')
    <script>
            (function () {
                function byId(id) { return document.getElementById(id); }

      // Function to move selected options from one select to another
                function moveSelected(fromSel, toSel) {
            const selected = Array.from(fromSel.selectedOptions);
        if (selected.length === 0) return;

            selected.forEach(opt => {
                const exists = Array.from(toSel.options).some(o => o.value === opt.value);
                if (!exists) {
                    const clone = opt.cloneNode(true);
                    toSel.add(clone);
          }
        });

        // Remove from source
        selected.forEach(opt => {
                    fromSel.remove(opt.index);
        });

        updateDescription();
        updateHiddenField();
      }

      // Function to ensure at least one component is always selected
                function ensureMinimumSelection() {
        const selected = byId('selected_repayment_components');
        const avail = byId('available_repayment_components');

        if (selected && selected.options.length === 0 && avail && avail.options.length > 0) {
          // If no components are selected, add the first available one
          const firstOption = avail.options[0];
          if (firstOption) {
            const clone = firstOption.cloneNode(true);
            selected.add(clone);
            avail.remove(firstOption.index);
            updateDescription();
            updateHiddenField();
          }
        }
      }

      // Function to remove selected options from one select and add to another
                function removeSelected(fromSel, toSel) {
        const selected = Array.from(fromSel.selectedOptions);
        if (selected.length === 0) return;

            selected.forEach(opt => {
          const exists = Array.from(toSel.options).some(o => o.value === opt.value);
                if (!exists) {
                    const clone = opt.cloneNode(true);
            toSel.add(clone);
                }
        });

        // Remove from source
        selected.forEach(opt => {
          fromSel.remove(opt.index);
        });

        updateDescription();
        // Always update hidden field after removal
        setTimeout(updateHiddenField, 10);
      }

      // Function to update the description display
                function updateDescription() {
        const sel = byId('selected_repayment_components');
        const box = byId('repayment_component_description');
        const text = byId('repayment_description_text');
            const opt = sel && sel.options[sel.selectedIndex];
                    if (opt && opt.dataset && opt.dataset.description) {
                text.textContent = opt.dataset.description;
                box.style.display = '';
            } else {
                text.textContent = '';
                box.style.display = 'none';
            }

        // Update hidden field with current selection
        updateHiddenField();
      }

      // Function to update the hidden field with current repayment order
                function updateHiddenField() {
        const selected = byId('selected_repayment_components');
        const hidden = byId('repayment_order_hidden');

        if (selected && hidden) {
          const values = Array.from(selected.options).map(opt => opt.value);
          // Allow empty selection - don't force default
          hidden.value = values.join(',');

          // Debug logging
          console.log('Updated hidden field:', hidden.value, 'Selected count:', values.length);
        }
      }

      // Function to update the role description display
                function updateRoleDescription() {
        const sel = byId('selected_roles');
                    const box = byId('role_description');
                    const text = byId('description_text');
        const opt = sel && sel.options[sel.selectedIndex];
                    if (opt && opt.dataset && opt.dataset.description) {
                        text.textContent = opt.dataset.description;
                        box.style.display = '';
                    } else {
                        text.textContent = '';
                        box.style.display = 'none';
                    }
      }

      // Function to enable drag and drop reordering
                function enableDragReorder(selectEl) {
        let dragStartIndex = null;

                    selectEl.addEventListener('dragstart', function (e) {
          const target = e.target;
                        if (target.tagName === 'OPTION') {
            dragStartIndex = Array.from(selectEl.options).indexOf(target);
            e.dataTransfer.effectAllowed = 'move';
          }
        });

                    selectEl.addEventListener('dragover', function (e) { e.preventDefault(); });

                    selectEl.addEventListener('drop', function (e) {
          e.preventDefault();
          const at = document.elementFromPoint(e.clientX, e.clientY);
          let dropIndex = -1;
                        if (at && at.tagName === 'OPTION') {
            dropIndex = Array.from(selectEl.options).indexOf(at);
          } else {
            dropIndex = selectEl.options.length - 1;
          }
                        if (dragStartIndex !== null && dropIndex >= 0 && dropIndex !== dragStartIndex) {
            const moving = selectEl.options[dragStartIndex];
            const clone = moving.cloneNode(true);
            selectEl.remove(dragStartIndex);
            selectEl.add(clone, dropIndex);
            selectEl.selectedIndex = dropIndex;
        updateDescription();
      }
          dragStartIndex = null;
        });

        Array.from(selectEl.options).forEach(opt => opt.draggable = true);
      }

      // Function to enable drag and drop reordering for roles
                function enableDragReorderRoles(selectEl) {
        let dragStartIndex = null;

                    selectEl.addEventListener('dragstart', function (e) {
          const target = e.target;
                        if (target.tagName === 'OPTION') {
            dragStartIndex = Array.from(selectEl.options).indexOf(target);
            e.dataTransfer.effectAllowed = 'move';
          }
        });

                    selectEl.addEventListener('dragover', function (e) { e.preventDefault(); });

                    selectEl.addEventListener('drop', function (e) {
          e.preventDefault();
          const at = document.elementFromPoint(e.clientX, e.clientY);
          let dropIndex = -1;
                        if (at && at.tagName === 'OPTION') {
            dropIndex = Array.from(selectEl.options).indexOf(at);
          } else {
            dropIndex = selectEl.options.length - 1;
          }
                        if (dragStartIndex !== null && dropIndex >= 0 && dropIndex !== dragStartIndex) {
            const moving = selectEl.options[dragStartIndex];
            const clone = moving.cloneNode(true);
            selectEl.remove(dragStartIndex);
            selectEl.add(clone, dropIndex);
            selectEl.selectedIndex = dropIndex;
            updateRoleDescription();
          }
          dragStartIndex = null;
        });

        Array.from(selectEl.options).forEach(opt => opt.draggable = true);
      }

      // Initialize form when DOM is loaded
                document.addEventListener('DOMContentLoaded', function () {
        // Initialize repayment components
        const avail = byId('available_repayment_components');
        const selected = byId('selected_repayment_components');
        const addBtn = byId('move_repayment_right');
        const removeBtn = byId('move_repayment_left');
        const form = document.querySelector('form');

                    if (addBtn && removeBtn && avail && selected) {
                        addBtn.addEventListener('click', function () { moveSelected(avail, selected); updateApprovalLevelsHiddenField(); });
                        removeBtn.addEventListener('click', function () { removeSelected(selected, avail); updateApprovalLevelsHiddenField(); });

          selected.addEventListener('change', updateDescription);

                        avail.addEventListener('change', function () {
            const opt = avail.options[avail.selectedIndex];
            const box = byId('repayment_component_description');
            const text = byId('repayment_description_text');
                            if (opt && opt.dataset.description) {
              text.textContent = opt.dataset.description;
              box.style.display = '';
            } else {
              text.textContent = '';
              box.style.display = 'none';
            }
          });

          enableDragReorder(selected);
          updateDescription();

          // Initialize form state for editing
          initializeFormState();
        }

        // Initialize approval levels (roles)
        const availRoles = byId('available_roles');
        const selectedRoles = byId('selected_roles');
        const addRoleBtn = byId('move_right');
        const removeRoleBtn = byId('move_left');

                    if (addRoleBtn && removeRoleBtn && availRoles && selectedRoles) {
                        addRoleBtn.addEventListener('click', function () { moveSelected(availRoles, selectedRoles); updateApprovalLevelsHiddenField(); });
                        removeRoleBtn.addEventListener('click', function () { removeSelected(selectedRoles, availRoles); updateApprovalLevelsHiddenField(); });

                        selectedRoles.addEventListener('change', function () { updateRoleDescription(); updateApprovalLevelsHiddenField(); });

                        availRoles.addEventListener('change', function () {
            const opt = availRoles.options[availRoles.selectedIndex];
            const box = byId('role_description');
            const text = byId('description_text');
                            if (opt && opt.dataset.description) {
              text.textContent = opt.dataset.description;
              box.style.display = '';
            } else {
              text.textContent = '';
              box.style.display = 'none';
            }
          });

          enableDragReorderRoles(selectedRoles);
          updateRoleDescription();
          updateApprovalLevelsHiddenField();
        }

        // Ensure hidden field is updated before form submission
        if (form) {
                        form.addEventListener('submit', function (e) {
            updateHiddenField();
            updateApprovalLevelsHiddenField();
          });
        }
      });

      function initializeFormState() {
        const avail = byId('available_repayment_components');
        const selected = byId('selected_repayment_components');

        // If we're editing and have selected components, ensure they're properly displayed
        if (selected && selected.options.length > 0) {
          // Update description for the first selected component
          if (selected.options[0]) {
            selected.selectedIndex = 0;
            updateDescription();
          }
        }
      }

      // Function to update the hidden field with current approval levels order
                function updateApprovalLevelsHiddenField() {
        const selected = byId('selected_roles');
        const hidden = byId('approval_levels_hidden');
                    if (selected && hidden) {
                        const values = Array.from(selected.options).map(function (opt) { return opt.value; });
          hidden.value = values.join(',');
          console.log('Updated approval_levels_hidden:', hidden.value);
        }
      }
    })();
    </script>
@endpush

@push('scripts')
    <script>
            (function () {
                function byId(id) { return document.getElementById(id); }

                function createFeeRow() {
        const container = byId('fees_container');
        const feeRow = document.createElement('div');
        feeRow.className = 'row fee-row mb-2';

                    const feeOptions = @json($fees->map(function ($fee) {
                        return ['id' => $fee->id, 'name' => $fee->name, 'type' => $fee->fee_type];
                    }));

        let optionsHtml = '<option value="">-- Select Fee --</option>';
        feeOptions.forEach(fee => {
          optionsHtml += `<option value="${fee.id}">${fee.name} (${fee.type})</option>`;
        });

        feeRow.innerHTML = `
          <div class="col-md-10">
            <select name="fees_id[]" class="form-select fee-select">
              ${optionsHtml}
            </select>
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-danger remove-fee">
              <i class="bx bx-trash"></i> Remove
            </button>
          </div>
        `;

        container.appendChild(feeRow);
        updateRemoveButtons();
      }

                function createPenaltyRow() {
        const container = byId('penalties_container');
        const penaltyRow = document.createElement('div');
        penaltyRow.className = 'row penalty-row mb-2';

                    const penaltyOptions = @json($penalties->map(function ($penalty) {
                        return ['id' => $penalty->id, 'name' => $penalty->name, 'type' => $penalty->penalty_type];
                    }));

        let optionsHtml = '<option value="">-- Select Penalty --</option>';
        penaltyOptions.forEach(penalty => {
          optionsHtml += `<option value="${penalty.id}">${penalty.name} (${penalty.type})</option>`;
        });

        penaltyRow.innerHTML = `
          <div class="col-md-10">
            <select name="penalty_id[]" class="form-select penalty-select">
              ${optionsHtml}
            </select>
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-danger remove-penalty">
              <i class="bx bx-trash"></i> Remove
            </button>
          </div>
        `;

        container.appendChild(penaltyRow);
        updateRemoveButtons();
      }

                function updateRemoveButtons() {
        const feeRows = document.querySelectorAll('.fee-row');
        const penaltyRows = document.querySelectorAll('.penalty-row');

        feeRows.forEach((row, index) => {
          const removeBtn = row.querySelector('.remove-fee');
          if (removeBtn) {
            removeBtn.style.display = feeRows.length > 1 ? '' : 'none';
          }
        });

        penaltyRows.forEach((row, index) => {
          const removeBtn = row.querySelector('.remove-penalty');
          if (removeBtn) {
            removeBtn.style.display = penaltyRows.length > 1 ? '' : 'none';
          }
        });
      }

                function removeFeeRow(event) {
        const row = event.target.closest('.fee-row');
        if (row) {
          row.remove();
          updateRemoveButtons();
        }
      }

                function removePenaltyRow(event) {
        const row = event.target.closest('.penalty-row');
        if (row) {
          row.remove();
          updateRemoveButtons();
        }
      }

                document.addEventListener('DOMContentLoaded', function () {
        const addFeeBtn = byId('add_fee');
        const addPenaltyBtn = byId('add_penalty');

        if (addFeeBtn) {
          addFeeBtn.addEventListener('click', createFeeRow);
        }

        if (addPenaltyBtn) {
          addPenaltyBtn.addEventListener('click', createPenaltyRow);
        }

        // Event delegation for remove buttons
                    document.addEventListener('click', function (e) {
          if (e.target.closest('.remove-fee')) {
            removeFeeRow(e);
          } else if (e.target.closest('.remove-penalty')) {
            removePenaltyRow(e);
          }
        });

        // Initial state
        updateRemoveButtons();
      });
    })();
    </script>
@endpush