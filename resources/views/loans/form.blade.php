@php
$isEdit = isset($loan);
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

<form action="{{ $isEdit ? route('loans.update', Hashids::encode($loan->id)) : route('loans.store') }}"
    method="POST" enctype="multipart/form-data" onsubmit="return handleSubmit(this)">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row">
        <!-- Customer -->
        <div class="row">
            <!-- Customer -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Customer <span class="text-danger">*</span></label>
                <select name="customer_id" id="customer_id" class="form-select select2-single @error('customer_id') is-invalid @enderror" required>
                    <option value="">Select Customer</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id', $loan->customer_id ?? '') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} - {{ $customer->phone1 }}
                    </option>
                    @endforeach
                </select>
                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- Displayed Group (disabled text input) -->
            <div class="col-md-6 mb-3">
                <label class="form-label">Group</label>
                <input type="text" id="group_name" class="form-control" value="" readonly>
            </div>

            <!-- Hidden Group ID for form submission -->
            <input type="hidden" name="group_id" id="group_id" value="{{ old('group_id', $loan->group_id ?? '') }}">
        </div>
        <!-- Loan Officer -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Loan Officer <span class="text-danger">*</span></label>
            <select name="loan_officer"
                class="form-select  select2-single @error('loan_officer') is-invalid @enderror" required>
                <option value="">-- Select Loan Officer --</option>
                @foreach($loanOfficers as $officer)
                <option value="{{ $officer->id }}" {{ old('loan_officer', $loan->loan_officer_id ?? '') == $officer->id ? 'selected' : '' }}>
                    {{ $officer->name }} ({{ $officer->email }})
                </option>
                @endforeach
            </select>
            @error('loan_officer')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <!-- Product Select -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Product</label>
            <select id="productSelect" name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                <option value="">Select Product</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" {{ old('product_id', $loan->product_id ?? '') == $product->id ? 'selected' : '' }}>
                    {{ $product->name }}
                </option>
                @endforeach
            </select>
            @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>


        <!----account from --->
        <div class="col-md-6 mb-3">
                <label class="form-label">From Account</label>
                    <select name="account_id" class="form-select @error('account_id') is-invalid @enderror">
                        <option value="">Select Account From</option>
                        @foreach($bankAccounts as $bankAccount)
                        <option value="{{ $bankAccount->id }}" {{ old('account_id', $loan->bank_account_id ?? '') == $bankAccount->id ? 'selected' : '' }}>
                            {{ $bankAccount->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Date Applied -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Date Disbursed <span class="text-danger">*</span></label>
            <input
                type="date"
                name="date_applied"
                class="form-control @error('date_applied') is-invalid @enderror"
                value="{{ old('date_applied', $loan->date_applied ?? now()->toDateString()) }}"
                required>
            @error('date_applied') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>


        <!-- Amount -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Amount <span class="text-danger">*</span>
                <small id="amountRangeLabel" class="text-muted ms-2"></small>
            </label>
            <input type="number" id="amountInput" step="0.000000000000001" name="amount" class="form-control @error('amount') is-invalid @enderror"
                value="{{ old('amount', $loan->amount ?? '') }}" placeholder="Enter loan amount" required>
            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
          <!-- Interest Rate -->
          <div class="col-md-6 mb-3">
            <label class="form-label">
                Interest Rate (%) <span class="text-danger">*</span>
                <small id="interestRangeLabel" class="text-muted ms-2"></small>
            </label>
            <input type="number" id="interestInput" step="0.000000000000001" name="interest" class="form-control @error('interest') is-invalid @enderror"
                value="{{ old('interest', $loan->interest ?? '') }}" placeholder="Enter interest in %" required>
            @error('interest') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <!-- Interest Cycle and Method -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Interest Cycle <span class="text-danger">*</span></label>
            <select name="interest_cycle" class="form-select @error('interest_cycle') is-invalid @enderror" required>
                <option value="">-- Select Interest Cycle --</option>
                @foreach($interestCycles as $key => $value)
                <option value="{{ $key }}" {{ old('interest_cycle', $loan->interest_cycle ?? '') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
                @endforeach
            </select>
            @error('interest_cycle') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>


        <!-- Period -->
        <div class="col-md-6 mb-3">
            <label class="form-label">
                Period <span class="text-danger">*</span>
                <small id="periodRangeLabel" class="text-muted ms-2"></small>
            </label>
            <div class="input-group">
                <input type="number" id="periodInput" name="period" class="form-control @error('period') is-invalid @enderror"
                    value="{{ old('period', $loan->period ?? '') }}" placeholder="Enter period in months" required>
                <button type="button" class="btn btn-outline-primary" id="loanCalculatorBtn" title="Open Loan Calculator">
                    <i class="bx bx-calculator"></i> Calculate
                </button>
            </div>
            @error('period') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

      

        <!-- Sector -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Sector</label>
            <select name="sector" class="form-select @error('sector') is-invalid @enderror">
                <option value="">Select Sector</option>
                @foreach($sectors as $sector)
                <option value="{{ $sector }}" {{ old('sector', $loan->sector ?? '') == $sector ? 'selected' : '' }}>
                    {{ $sector }}
                </option>
                @endforeach
            </select>
            @error('sector') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <!-- Add other loan fields as needed -->
    </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('loans.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Loans
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Update Loan' : 'Create Loan' }}
        </button>
    </div>
</form>

<!-- Loan Calculator Modal -->
<div class="modal fade" id="loanCalculatorModal" tabindex="-1" aria-labelledby="loanCalculatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loanCalculatorModalLabel">
                    <i class="bx bx-calculator me-2"></i>Loan Calculator
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Full Loan Calculator Interface -->
                <div class="row">
                    <div class="col-12">
                        <!-- Calculator Form -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-calculator me-2"></i>Loan Calculator
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="loanCalculatorForm" class="needs-validation" novalidate>
                                    @csrf
                                    <div class="row">
                                        <!-- Product Selection -->
                                        <div class="col-md-6 mb-3">
                                            <label for="product_id" class="form-label">Loan Product <span class="text-danger">*</span></label>
                                            <select class="form-select" id="product_id" name="product_id" required>
                                                <option value="">Select a loan product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                            data-min-amount="{{ $product->minimum_principal }}"
                                                            data-max-amount="{{ $product->maximum_principal }}"
                                                            data-min-period="{{ $product->minimum_period }}"
                                                            data-max-period="{{ $product->maximum_period }}"
                                                            data-min-interest="{{ $product->minimum_interest_rate }}"
                                                            data-max-interest="{{ $product->maximum_interest_rate }}"
                                                            data-interest-method="{{ $product->interest_method }}">
                                                        {{ $product->name }} ({{ $product->interest_method }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback">Please select a loan product.</div>
                                        </div>

                                        <!-- Loan Amount -->
                                        <div class="col-md-6 mb-3">
                                            <label for="amount" class="form-label">Loan Amount (TZS) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="amount" name="amount" 
                                                   min="1" step="0.01" required>
                                            <div class="invalid-feedback">Please enter a valid loan amount.</div>
                                            <small id="amountRangeLabel" class="text-muted"></small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Period -->
                                        <div class="col-md-6 mb-3">
                                            <label for="period" class="form-label">Period (Months) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="period" name="period" 
                                                   min="1" required>
                                            <div class="invalid-feedback">Please enter a valid period.</div>
                                            <small id="periodRangeLabel" class="text-muted"></small>
                                        </div>

                                        <!-- Interest Rate -->
                                        <div class="col-md-6 mb-3">
                                            <label for="interest_rate" class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="interest_rate" name="interest_rate" 
                                                   min="0" step="0.01" required>
                                            <div class="invalid-feedback">Please enter a valid interest rate.</div>
                                            <small id="interestRangeLabel" class="text-muted"></small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- Start Date -->
                                        <div class="col-md-6 mb-3">
                                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                                   value="{{ date('Y-m-d') }}" required>
                                            <div class="invalid-feedback">Please select a start date.</div>
                                        </div>

                                        <!-- Additional Fees -->
                                        <div class="col-md-6 mb-3">
                                            <label for="additional_fees" class="form-label">Additional Fees (TZS)</label>
                                            <input type="number" class="form-control" id="additional_fees" name="additional_fees" 
                                                   value="0" min="0" step="0.01">
                                            <div class="invalid-feedback">Please enter a valid amount.</div>
                                        </div>
                                    </div>

                                    <!-- Product Details -->
                                    <div id="productDetails" class="mb-4" style="display: none;">
                                        <div class="card border-info">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Product Details</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Interest Method:</strong> <span id="productInterestMethod"></span></p>
                                                        <p><strong>Min Amount:</strong> <span id="productMinAmount"></span></p>
                                                        <p><strong>Max Amount:</strong> <span id="productMaxAmount"></span></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Min Period:</strong> <span id="productMinPeriod"></span> months</p>
                                                        <p><strong>Max Period:</strong> <span id="productMaxPeriod"></span> months</p>
                                                        <p><strong>Interest Range:</strong> <span id="productInterestRange"></span></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bx bx-calculator me-2"></i>Calculate Loan
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-lg ms-2" onclick="resetCalculator()">
                                            <i class="bx bx-reset me-2"></i>Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Results Section -->
                        <div id="resultsCard" class="card mt-4" style="display: none;">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-chart me-2"></i>Calculation Results
                                </h5>
                                <div>
                                    <button type="button" class="btn btn-outline-primary btn-sm me-2" id="exportPdfBtn">
                                        <i class="bx bx-file-pdf me-1"></i>Export PDF
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm me-2" id="exportExcelBtn">
                                        <i class="bx bx-file me-1"></i>Export Excel
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="calculationResults">
                                    <!-- Results will be displayed here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="applyCalculationBtn" style="display: none;">
                    <i class="bx bx-check me-1"></i>Apply to Form
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Comparison Modal -->
<div class="modal fade" id="comparisonModal" tabindex="-1" aria-labelledby="comparisonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="comparisonModalLabel">
                    <i class="bx bx-git-compare me-2"></i>Loan Comparison
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="comparisonContent">
                    <!-- Comparison content will be displayed here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    const products = @json($products);

    document.addEventListener("DOMContentLoaded", function() {
        const productSelect = document.getElementById("productSelect");
        const periodInput = document.getElementById("periodInput");
        const interestInput = document.getElementById("interestInput");
        const amountInput = document.getElementById("amountInput");
        const periodRangeLabel = document.getElementById("periodRangeLabel");
        const amountRangeLabel = document.getElementById("amountRangeLabel");
        const interestRangeLabel = document.getElementById("interestRangeLabel");

        productSelect.addEventListener("change", function() {
            const selectedId = parseInt(this.value);
            const product = products.find(p => p.id === selectedId);

            if (product) {
                // Set period limits
                periodInput.min = product.minimum_period;
                periodInput.max = product.maximum_period;
                periodRangeLabel.innerText = `(min: ${product.minimum_period}, max: ${product.maximum_period})`;

                // Set interest limits
                interestInput.min = product.minimum_interest_rate;
                interestInput.max = product.maximum_interest_rate;
                interestRangeLabel.innerText = `(min: ${product.minimum_interest_rate}%, max: ${product.maximum_interest_rate}%)`;

                ///set amount principal limit

                amountInput.min = product.minimum_principal;
                amountInput.max = product.maximum_principal;
                amountRangeLabel.innerText = `(min: ${product.minimum_principal}, max: ${product.maximum_principal})`;

            } else {
                periodInput.removeAttribute('min');
                periodInput.removeAttribute('max');
                interestInput.removeAttribute('min');
                interestInput.removeAttribute('max');
                periodRangeLabel.innerText = '';
                interestRangeLabel.innerText = '';
            }
        });

        // Initialize Select2 for all .select2-single selects
        if (window.jQuery) {
            $('.select2-single').select2({
                placeholder: 'Select Customer',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });
        }

        // Group update logic for customer select
        const customers = @json($customers);
        const groupIdInput = document.getElementById('group_id');
        const groupNameDisplay = document.getElementById('group_name');

        function updateGroupForCustomer(customerId) {
            const selectedCustomer = customers.find(c => c.id == customerId);
            groupIdInput.value = '';
            groupNameDisplay.value = '';
            if (selectedCustomer && selectedCustomer.groups && selectedCustomer.groups.length > 0) {
                const group = selectedCustomer.groups[0];
                groupIdInput.value = group.id;
                groupNameDisplay.value = group.name;
            }
        }

        // On edit, set group from $loan if available
        @if($isEdit && isset($loan) && isset($loan->group))
            groupIdInput.value = '{{ $loan->group_id }}';
            groupNameDisplay.value = '{{ $loan->group->name }}';
        @else
            // Otherwise, use customer selection logic
            if (window.jQuery) {
                $('#customer_id').on('change', function() {
                    updateGroupForCustomer(this.value);
                });
                $('#customer_id').trigger('change');
            } else {
                const customerSelect = document.getElementById('customer_id');
                customerSelect.addEventListener('change', function() {
                    updateGroupForCustomer(this.value);
                });
                if (customerSelect.value) {
                    updateGroupForCustomer(customerSelect.value);
                }
            }
        @endif
    });
</script>

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

            // Add loading overlay to prevent any further interactions
            const overlay = document.createElement('div');
            overlay.id = 'form-loading-overlay';
            overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: 9999; display: flex; align-items: center; justify-content: center;';
            overlay.innerHTML = '<div style="background: white; padding: 20px; border-radius: 8px; text-align: center;"><i class="bx bx-loader-alt bx-spin" style="font-size: 24px; color: #007bff;"></i><br><span style="margin-top: 10px; display: block;">Processing...</span></div>';
            document.body.appendChild(overlay);

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

        // Loan Calculator Integration
        document.getElementById('loanCalculatorBtn').addEventListener('click', function() {
            openLoanCalculator();
        });

        function openLoanCalculator() {
            // Get current form values
            const productId = document.getElementById('productSelect').value;
            const amount = document.getElementById('amountInput').value;
            const period = document.getElementById('periodInput').value;
            const interestRate = document.getElementById('interestInput').value;
            const startDate = document.querySelector('input[name="date_applied"]').value;

            // Validate required fields
            if (!productId || !amount || !period || !interestRate || !startDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please fill in all required fields (Product, Amount, Period, Interest Rate, and Date) before using the calculator.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#007bff'
                });
                return;
            }

            // Open calculator in modal with pre-filled values
            showCalculatorModal(productId, amount, period, interestRate, startDate);
        }

        // Listen for product selection changes to update calculator context
        document.getElementById('productSelect').addEventListener('change', function() {
            const productId = this.value;
            if (productId) {
                // You can add logic here to fetch product details and update form validation
                console.log('Product selected:', productId);
            }
        });

        // Show calculator modal function
        function showCalculatorModal(productId, amount, period, interestRate, startDate) {
            // Pre-fill the form with the provided values
            if (productId) {
                $('#loanCalculatorModal #product_id').val(productId).trigger('change');
            }
            if (amount) {
                $('#loanCalculatorModal #amount').val(amount);
            }
            if (period) {
                $('#loanCalculatorModal #period').val(period);
            }
            if (interestRate) {
                $('#loanCalculatorModal #interest_rate').val(interestRate);
            }
            if (startDate) {
                $('#loanCalculatorModal #start_date').val(startDate);
            }
            
            // Show modal
            $('#loanCalculatorModal').modal('show');
            
            // Initialize calculator functionality
            initializeCalculatorInModal();
            
            // Auto-calculate if all parameters are provided
            if (productId && amount && period && interestRate && startDate) {
                setTimeout(function() {
                    $('#loanCalculatorModal #loanCalculatorForm').submit();
                }, 1000);
            }
        }

        // Initialize calculator functionality in modal
        function initializeCalculatorInModal() {
            // Product selection change
            $('#loanCalculatorModal #product_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const productId = selectedOption.val();
                
                if (productId) {
                    // Show product details
                    $('#loanCalculatorModal #productDetails').show();
                    
                    // Update product details
                    $('#loanCalculatorModal #productInterestMethod').text(selectedOption.data('interest-method'));
                    $('#loanCalculatorModal #productMinAmount').text(formatCurrency(selectedOption.data('min-amount')));
                    $('#loanCalculatorModal #productMaxAmount').text(formatCurrency(selectedOption.data('max-amount')));
                    $('#loanCalculatorModal #productMinPeriod').text(selectedOption.data('min-period'));
                    $('#loanCalculatorModal #productMaxPeriod').text(selectedOption.data('max-period'));
                    $('#loanCalculatorModal #productInterestRange').text(selectedOption.data('min-interest') + '% - ' + selectedOption.data('max-interest') + '%');
                    
                    // Update range labels
                    $('#loanCalculatorModal #amountRangeLabel').text('Range: ' + formatCurrency(selectedOption.data('min-amount')) + ' - ' + formatCurrency(selectedOption.data('max-amount')));
                    $('#loanCalculatorModal #periodRangeLabel').text('Range: ' + selectedOption.data('min-period') + ' - ' + selectedOption.data('max-period') + ' months');
                    $('#loanCalculatorModal #interestRangeLabel').text('Range: ' + selectedOption.data('min-interest') + '% - ' + selectedOption.data('max-interest') + '%');
                } else {
                    $('#loanCalculatorModal #productDetails').hide();
                    $('#loanCalculatorModal #amountRangeLabel').text('');
                    $('#loanCalculatorModal #periodRangeLabel').text('');
                    $('#loanCalculatorModal #interestRangeLabel').text('');
                }
            });
            
            // Form submission
            $('#loanCalculatorModal #loanCalculatorForm').on('submit', function(e) {
                e.preventDefault();
                calculateLoanInModal();
            });
            
            // Export buttons
            $('#loanCalculatorModal #exportPdfBtn').on('click', function() {
                exportCalculation('pdf');
            });
            
            $('#loanCalculatorModal #exportExcelBtn').on('click', function() {
                exportCalculation('excel');
            });
            
            // Compare button
            $('#loanCalculatorModal #compareBtn').on('click', function() {
                showComparisonModal();
            });
        }

        // Calculate loan in modal
        function calculateLoanInModal() {
            const formData = $('#loanCalculatorModal #loanCalculatorForm').serialize();
            
            $.ajax({
                url: '{{ route("loan-calculator.calculate") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    console.log('Full response:', response);
                    if (response.success) {
                        // Display results in modal
                        displayResultsInModal(response);
                        $('#loanCalculatorModal #resultsCard').show();
                        $('#applyCalculationBtn').show();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Calculation Failed',
                            text: response.error || 'An error occurred during calculation.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorHtml = '<div class="alert alert-danger"><ul>';
                    Object.keys(errors).forEach(field => {
                        errorHtml += '<li>' + errors[field][0] + '</li>';
                    });
                    errorHtml += '</ul></div>';
                    $('#loanCalculatorModal #calculationResults').html(errorHtml);
                }
            });
        }

        // Display results in modal
        function displayResultsInModal(calculation) {
            const { product, totals, schedule, fees, penalties, summary } = calculation;
            
            // Debug: Check if schedule exists and has data
            console.log('Schedule data:', schedule);
            console.log('Schedule length:', schedule ? schedule.length : 'undefined');
            
            if (!schedule || !Array.isArray(schedule) || schedule.length === 0) {
                console.error('Schedule data is missing or empty');
                Swal.fire({
                    icon: 'error',
                    title: 'Schedule Error',
                    text: 'Repayment schedule data is missing. Please try again.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }
            
            const resultsHtml = `
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-primary mb-1">${formatCurrency(totals.principal)}</h4>
                            <small class="text-muted">Loan Amount</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-success mb-1">${formatCurrency(totals.total_interest)}</h4>
                            <small class="text-muted">Total Interest</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-warning mb-1">${formatCurrency(totals.total_fees)}</h4>
                            <small class="text-muted">Total Fees</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <h4 class="text-danger mb-1">${formatCurrency(totals.total_amount)}</h4>
                            <small class="text-muted">Total Amount</small>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1">${formatCurrency(totals.monthly_payment)}</h3>
                                <p class="mb-0">Monthly Payment</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1">${summary.interest_percentage}%</h3>
                                <p class="mb-0">Interest Percentage</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Repayment Schedule</h6>
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm me-2" id="viewAllInstallmentsBtn">
                                <i class="bx bx-list-ul me-1"></i>View All (${schedule.length})
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="viewFirstTenBtn" style="display: none;">
                                <i class="bx bx-chevron-up me-1"></i>Show First 10
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-striped table-sm" id="installmentsTable">
                            <thead class="sticky-top bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Principal</th>
                                    <th>Interest</th>
                                    <th>Fees</th>
                                    <th>Total</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody id="installmentsTableBody">
                                ${schedule.slice(0, 10).map((installment, index) => `
                                    <tr>
                                        <td>${installment.installment_number}</td>
                                        <td>${formatDate(installment.due_date)}</td>
                                        <td>${formatCurrency(installment.principal)}</td>
                                        <td>${formatCurrency(installment.interest)}</td>
                                        <td>${formatCurrency(installment.fee_amount)}</td>
                                        <td><strong>${formatCurrency(installment.total_amount)}</strong></td>
                                        <td>${formatCurrency(installment.remaining_balance)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            $('#loanCalculatorModal #calculationResults').html(resultsHtml);
            
            // Add event handlers for installment view buttons
            $('#loanCalculatorModal #viewAllInstallmentsBtn').on('click', function() {
                showAllInstallments(calculation.schedule);
            });
            
            $('#loanCalculatorModal #viewFirstTenBtn').on('click', function() {
                showFirstTenInstallments(calculation.schedule);
            });
        }
        
        // Show all installments
        function showAllInstallments(schedule) {
            const tableBody = $('#loanCalculatorModal #installmentsTableBody');
            tableBody.empty();
            
            schedule.forEach((installment, index) => {
                tableBody.append(`
                    <tr>
                        <td>${installment.installment_number}</td>
                        <td>${formatDate(installment.due_date)}</td>
                        <td>${formatCurrency(installment.principal)}</td>
                        <td>${formatCurrency(installment.interest)}</td>
                        <td>${formatCurrency(installment.fee_amount)}</td>
                        <td><strong>${formatCurrency(installment.total_amount)}</strong></td>
                        <td>${formatCurrency(installment.remaining_balance)}</td>
                    </tr>
                `);
            });
            
            $('#loanCalculatorModal #viewAllInstallmentsBtn').hide();
            $('#loanCalculatorModal #viewFirstTenBtn').show();
        }
        
        // Show first 10 installments
        function showFirstTenInstallments(schedule) {
            const tableBody = $('#loanCalculatorModal #installmentsTableBody');
            tableBody.empty();
            
            schedule.slice(0, 10).forEach((installment, index) => {
                tableBody.append(`
                    <tr>
                        <td>${installment.installment_number}</td>
                        <td>${formatDate(installment.due_date)}</td>
                        <td>${formatCurrency(installment.principal)}</td>
                        <td>${formatCurrency(installment.interest)}</td>
                        <td>${formatCurrency(installment.fee_amount)}</td>
                        <td><strong>${formatCurrency(installment.total_amount)}</strong></td>
                        <td>${formatCurrency(installment.remaining_balance)}</td>
                    </tr>
                `);
            });
            
            $('#loanCalculatorModal #viewAllInstallmentsBtn').show();
            $('#loanCalculatorModal #viewFirstTenBtn').hide();
        }
        
        // Export calculation
        function exportCalculation(format) {
            Swal.fire({
                title: 'Exporting...',
                text: 'Please wait while we prepare your ' + format.toUpperCase() + ' file.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const url = format === 'pdf' ? 
                '{{ route("loan-calculator.export-pdf") }}' : 
                '{{ route("loan-calculator.export-excel") }}';
            
            // Get form data and build query string
            const formData = $('#loanCalculatorModal #loanCalculatorForm').serialize();
            const exportUrl = url + '?' + formData;
            
            // Open in new window
            window.open(exportUrl, '_blank');
            
            Swal.close();
        }
        
        // Reset calculator
        window.resetCalculator = function() {
            $('#loanCalculatorModal #loanCalculatorForm')[0].reset();
            $('#loanCalculatorModal #productDetails').hide();
            $('#loanCalculatorModal #resultsCard').hide();
            $('#loanCalculatorModal #amountRangeLabel').text('');
            $('#loanCalculatorModal #periodRangeLabel').text('');
            $('#loanCalculatorModal #interestRangeLabel').text('');
        };

        // Apply calculation to form
        $('#applyCalculationBtn').on('click', function() {
            // You can add logic here to apply calculated values back to the form
            Swal.fire({
                icon: 'success',
                title: 'Calculation Applied',
                text: 'Loan calculation has been applied to the form.',
                timer: 2000,
                showConfirmButton: false
            });
            $('#loanCalculatorModal').modal('hide');
        });

        // Comparison functionality
        let comparisonScenarios = [];
        
        // Show comparison modal
        function showComparisonModal() {
            if (comparisonScenarios.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Scenarios to Compare',
                    text: 'Please add at least one scenario to compare. You can save the current calculation as a scenario.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#007bff'
                });
                return;
            }
            
            $('#comparisonModal').modal('show');
            displayComparison();
        }
        
        // Display comparison
        function displayComparison() {
            let comparisonHtml = `
                <div class="row mb-4">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-primary" id="addCurrentScenarioBtn">
                            <i class="bx bx-plus me-1"></i>Add Current as Scenario
                        </button>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearScenariosBtn">
                            <i class="bx bx-trash me-1"></i>Clear All
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Scenario</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Period</th>
                                <th>Interest Rate</th>
                                <th>Monthly Payment</th>
                                <th>Total Interest</th>
                                <th>Total Amount</th>
                                <th>Interest %</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            comparisonScenarios.forEach((scenario, index) => {
                comparisonHtml += `
                    <tr>
                        <td>Scenario ${index + 1}</td>
                        <td>${scenario.product.name}</td>
                        <td>${formatCurrency(scenario.totals.principal)}</td>
                        <td>${scenario.summary.period} months</td>
                        <td>${scenario.summary.interest_rate}%</td>
                        <td><strong>${formatCurrency(scenario.totals.monthly_payment)}</strong></td>
                        <td>${formatCurrency(scenario.totals.total_interest)}</td>
                        <td>${formatCurrency(scenario.totals.total_amount)}</td>
                        <td>${scenario.summary.interest_percentage}%</td>
                        <td>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeScenario(${index})">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            comparisonHtml += `
                        </tbody>
                    </table>
                </div>
            `;
            
            $('#comparisonContent').html(comparisonHtml);
            
            // Add event handlers
            $('#addCurrentScenarioBtn').on('click', function() {
                addCurrentScenario();
            });
            
            $('#clearScenariosBtn').on('click', function() {
                clearAllScenarios();
            });
        }
        
        // Add current calculation as scenario
        function addCurrentScenario() {
            if (!currentCalculation) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Current Calculation',
                    text: 'Please calculate a loan first before adding it as a scenario.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#007bff'
                });
                return;
            }
            
            comparisonScenarios.push(currentCalculation);
            displayComparison();
            
            Swal.fire({
                icon: 'success',
                title: 'Scenario Added',
                text: 'Current calculation has been added as a comparison scenario.',
                timer: 2000,
                showConfirmButton: false
            });
        }
        
        // Remove scenario
        window.removeScenario = function(index) {
            comparisonScenarios.splice(index, 1);
            displayComparison();
        };
        
        // Clear all scenarios
        function clearAllScenarios() {
            Swal.fire({
                title: 'Clear All Scenarios?',
                text: 'This will remove all comparison scenarios. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, clear all',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    comparisonScenarios = [];
                    displayComparison();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Scenarios Cleared',
                        text: 'All comparison scenarios have been removed.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }

        // Utility functions
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-TZ', {
                style: 'currency',
                currency: 'TZS',
                minimumFractionDigits: 2
            }).format(amount);
        }
        
        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-TZ');
        }
    </script>
@endpush