@extends('layouts.main')

@section('title', 'Create Receipt from Loan')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.list'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Create Receipt from Loan', 'url' => '#', 'icon' => 'bx bx-plus-circle']
        ]" />

            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-header bg-secondary text-white">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h5 class="mb-0 text-white">
                                        <i class="bx bx-receipt me-2"></i>Create Receipt from Loan
                                    </h5>
                                    <p class="mb-0 opacity-75">Create a receipt voucher for loan: {{ $loan->loanNo }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Loan Information Card -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-primary">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0 fw-bold">
                                                <i class="bx bx-info-circle me-2"></i>Loan Information
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>Loan Number:</strong><br>
                                                    <span class="text-primary">{{ $loan->loanNo }}</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Customer:</strong><br>
                                                    <span>{{ $loan->customer->name ?? 'N/A' }}</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Product:</strong><br>
                                                    <span>{{ $loan->product->name ?? 'N/A' }}</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <strong>Amount:</strong><br>
                                                    <span class="text-success">{{ number_format($loan->amount, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form id="receiptVoucherForm"
                                action="{{ route('accounting.loans.store-receipt', Hashids::encode($loan->id)) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf

                                <!-- Header Section -->
                                <div class="row mb-4">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="date" class="form-label fw-bold">
                                                <i class="bx bx-calendar me-1"></i>Date <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control @error('date') is-invalid @enderror"
                                                id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                            @error('date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="reference" class="form-label fw-bold">
                                                <i class="bx bx-hash me-1"></i>Reference Number
                                            </label>
                                            <input type="text" class="form-control @error('reference') is-invalid @enderror"
                                                id="reference" name="reference"
                                                value="{{ old('reference', 'RV-' . strtoupper(uniqid())) }}"
                                                placeholder="Enter reference number" readonly>
                                            <div class="form-text">Auto-generated reference for loan {{ $loan->loanNo }}
                                            </div>
                                            @error('reference')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Bank Account Section -->
                                <div class="row mb-4">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="bx bx-wallet me-1"></i>Bank Account <span
                                                    class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('bank_account_id') is-invalid @enderror"
                                                id="bank_account_id" name="bank_account_id" data-live-search="true"
                                                required>
                                                <option value="">-- Select Bank Account --</option>
                                                @foreach($bankAccounts as $bankAccount)
                                                    <option value="{{ $bankAccount->id }}" {{ old('bank_account_id', $loan->bank_account_id) == $bankAccount->id ? 'selected' : '' }}>
                                                        {{ $bankAccount->name }} - {{ $bankAccount->account_number }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('bank_account_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Payee Section -->
                                <div class="row mb-4">
                                    <div class="col-lg-12">
                                        <div class="card border-primary">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0 fw-bold">
                                                    <i class="bx bx-user me-2"></i>Payee Information
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="payee_type" class="form-label fw-bold">
                                                                Payee Type <span class="text-danger">*</span>
                                                            </label>
                                                            <select
                                                                class="form-select @error('payee_type') is-invalid @enderror"
                                                                id="payee_type" name="payee_type" required>
                                                                <option value="">-- Select Payee Type --</option>
                                                                <option value="customer" {{ old('payee_type', 'customer') == 'customer' ? 'selected' : '' }}>Customer
                                                                </option>
                                                                <option value="other" {{ old('payee_type') == 'other' ? 'selected' : '' }}>Other</option>
                                                            </select>
                                                            @error('payee_type')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <!-- Customer Selection (shown when payee_type is customer) -->
                                                    <div class="col-lg-8" id="customerSection">
                                                        <div class="mb-3">
                                                            <label for="customer_id" class="form-label fw-bold">
                                                                Select Customer <span class="text-danger">*</span>
                                                            </label>
                                                            <select
                                                                class="form-select @error('customer_id') is-invalid @enderror"
                                                                id="customer_id" name="customer_id">
                                                                <option value="">-- Select Customer --</option>
                                                                @foreach($customers as $customer)
                                                                    <option value="{{ $customer->id }}" {{ old('customer_id', $loan->customer_id) == $customer->id ? 'selected' : '' }}>
                                                                        {{ $customer->name }} ({{ $customer->customerNo }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('customer_id')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <!-- Other Payee Name (shown when payee_type is other) -->
                                                    <div class="col-lg-8" id="otherPayeeSection" style="display: none;">
                                                        <div class="mb-3">
                                                            <label for="payee_name" class="form-label fw-bold">
                                                                Payee Name <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text"
                                                                class="form-control @error('payee_name') is-invalid @enderror"
                                                                id="payee_name" name="payee_name"
                                                                value="{{ old('payee_name') }}"
                                                                placeholder="Enter payee name">
                                                            @error('payee_name')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transaction Description and Attachment -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-bold">
                                                <i class="bx bx-message-square-detail me-1"></i>Transaction Description
                                            </label>
                                            <textarea class="form-control @error('description') is-invalid @enderror"
                                                id="description" name="description" rows="3"
                                                placeholder="Enter transaction description">{{ old('description', "Receipt for loan {$loan->loanNo} - {$loan->customer->name}") }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="attachment" class="form-label fw-bold">
                                                <i class="bx bx-paperclip me-1"></i>Attachment (Optional)
                                            </label>
                                            <input type="file"
                                                class="form-control @error('attachment') is-invalid @enderror"
                                                id="attachment" name="attachment" accept=".pdf">
                                            <div class="form-text">Supported format: PDF only (Max: 2MB)</div>
                                            @error('attachment')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Line Items Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border-primary">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0 fw-bold">
                                                    <i class="bx bx-list-ul me-2"></i>Line Items
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div id="lineItemsContainer">
                                                    <!-- Line items will be added here dynamically -->
                                                </div>

                                                <div class="text-left mt-3">
                                                    <button type="button" class="btn btn-success" id="addLineBtn">
                                                        <i class="bx bx-plus me-2"></i>Add Line
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total and Actions -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="d-flex justify-content-start">
                                            <a href="{{ route('loans.list') }}" class="btn btn-secondary me-2">
                                                <i class="bx bx-arrow-back me-2"></i>Cancel
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <div class="me-4">
                                                <h4 class="mb-0 text-danger fw-bold">
                                                    Total Amount: <span id="totalAmount">0.00</span>
                                                </h4>
                                            </div>
                                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                                <i class="bx bx-plus-circle me-2"></i>Create Receipt
                                            </button>
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

@push('styles')
    <style>
        .form-control,
        .form-select {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .btn {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }

        .line-item-row {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
        }

        .line-item-row:hover {
            background: #e9ecef;
        }

        .remove-line-btn {
            color: #dc3545;
            border: none;
            background: none;
            font-size: 1.2rem;
            padding: 0;
            line-height: 1;
        }

        .remove-line-btn:hover {
            color: #c82333;
        }

        .total-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border: 2px solid #28a745;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            let lineItemCount = 0;

            // Handle payee type change
            $('#payee_type').change(function () {
                const payeeType = $(this).val();

                if (payeeType === 'customer') {
                    $('#customerSection').show();
                    $('#otherPayeeSection').hide();
                    $('#customer_id').prop('required', true);
                    $('#payee_name').prop('required', false);
                } else if (payeeType === 'other') {
                    $('#customerSection').hide();
                    $('#otherPayeeSection').show();
                    $('#customer_id').prop('required', false);
                    $('#payee_name').prop('required', true);
                } else {
                    $('#customerSection').hide();
                    $('#otherPayeeSection').hide();
                    $('#customer_id').prop('required', false);
                    $('#payee_name').prop('required', false);
                }
            });

            // Trigger change event on page load if value exists
            if ($('#payee_type').val()) {
                $('#payee_type').trigger('change');
            }

            // Add line item
            $('#addLineBtn').click(function () {
                addLineItem();
            });

            // Add initial line item
            addLineItem();

            function addLineItem() {
                lineItemCount++;
                const lineItemHtml = `
                                    <div class="line-item-row" id="lineItem_${lineItemCount}">
                                        <div class="row">
                                            <div class="col-lg-5">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Chart Account <span class="text-danger">*</span></label>
                                                    <select class="form-select chart-account-select" name="line_items[${lineItemCount}][chart_account_id]" required>
                                                        <option value="">-- Select Chart Account --</option>
                                                        @foreach($chartAccounts as $chartAccount)
                                                            <option value="{{ $chartAccount->id }}">{{ $chartAccount->account_name }} ({{ $chartAccount->account_code }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control amount-input" name="line_items[${lineItemCount}][amount]" 
                                                           step="0.01" min="0.01" placeholder="0.00" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Description</label>
                                                    <input type="text" class="form-control" name="line_items[${lineItemCount}][description]" 
                                                           placeholder="Optional description">
                                                </div>
                                            </div>
                                            <div class="col-lg-1">
                                                <div class="mb-3">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn remove-line-btn" onclick="removeLineItem(${lineItemCount})">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                $('#lineItemsContainer').append(lineItemHtml);
            }

            // Remove line item
            window.removeLineItem = function (index) {
                if ($('.line-item-row').length > 1) {
                    $(`#lineItem_${index}`).remove();
                    calculateTotal();
                } else {
                    alert('At least one line item is required.');
                }
            };

            // Calculate total
            function calculateTotal() {
                let total = 0;
                $('.amount-input').each(function () {
                    const amount = parseFloat($(this).val()) || 0;
                    total += amount;
                });
                $('#totalAmount').text(total.toFixed(2));
            }

            // Handle amount input changes
            $(document).on('input', '.amount-input', function () {
                calculateTotal();
            });

            // Form validation
            $('#receiptVoucherForm').submit(function (e) {
                console.log('Form submission started');
                console.log('Form data:', $(this).serialize());

                e.preventDefault();

                const payeeType = $('#payee_type').val();
                console.log('Payee type:', payeeType);

                // Clear previous validation messages
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                let hasErrors = false;

                if (payeeType === 'customer' && !$('#customer_id').val()) {
                    $('#customer_id').addClass('is-invalid');
                    $('#customer_id').after('<div class="invalid-feedback">Please select a customer.</div>');
                    hasErrors = true;
                }

                if (payeeType === 'other' && !$('#payee_name').val()) {
                    $('#payee_name').addClass('is-invalid');
                    $('#payee_name').after('<div class="invalid-feedback">Please enter payee name.</div>');
                    hasErrors = true;
                }

                if ($('.line-item-row').length === 0) {
                    alert('At least one line item is required.');
                    hasErrors = true;
                }

                const total = parseFloat($('#totalAmount').text());
                if (total <= 0) {
                    alert('Total amount must be greater than zero.');
                    hasErrors = true;
                }

                // Check if all required fields are filled
                const requiredFields = ['date', 'bank_account_id', 'payee_type'];
                requiredFields.forEach(field => {
                    if (!$(`#${field}`).val()) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}`).after('<div class="invalid-feedback">This field is required.</div>');
                        hasErrors = true;
                    }
                });

                // Check line items
                $('.line-item-row').each(function (index) {
                    const accountSelect = $(this).find('.chart-account-select');
                    const amountInput = $(this).find('.amount-input');

                    if (!accountSelect.val()) {
                        accountSelect.addClass('is-invalid');
                        accountSelect.after('<div class="invalid-feedback">Please select an account.</div>');
                        hasErrors = true;
                    }

                    if (!amountInput.val() || parseFloat(amountInput.val()) <= 0) {
                        amountInput.addClass('is-invalid');
                        amountInput.after('<div class="invalid-feedback">Please enter a valid amount.</div>');
                        hasErrors = true;
                    }
                });

                if (hasErrors) {
                    console.log('Validation errors found');
                    return false;
                }

                console.log('Form validation passed, submitting...');

                // Show loading state
                $('#saveBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Saving...');

                // Submit the form
                this.submit();
            });
        });
    </script>
@endpush