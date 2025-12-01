@extends('layouts.main')

@section('title', 'Create Payment Voucher')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <!-- Breadcrumb -->
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Payment Vouchers', 'url' => route('accounting.payment-vouchers.index'), 'icon' => 'bx bx-receipt'],
                ['label' => 'Create Payment Voucher', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">CREATE PAYMENT VOUCHER</h6>
            <hr />
            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-header bg-secondary text-white">
                            <div class="d-flex align-items-center">
                                <div>
                                    <h5 class="mb-0 text-white">
                                        <i class="bx bx-receipt me-2"></i>New Payment Voucher
                                    </h5>
                                    <p class="mb-0 opacity-75">Create a new payment voucher entry</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="paymentVoucherForm" action="{{ route('accounting.payment-vouchers.store') }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf

                                <!-- Header Section -->
                                <div class="row mb-4">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="date" class="form-label fw-bold">
                                                <i class="bx bx-calendar me-1"></i>Date <span class="text-danger">*</span>
                                            </label>
                                            <input type="date"
                                                class="form-control form-control-lg @error('date') is-invalid @enderror"
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
                                            <input type="text"
                                                class="form-control form-control-lg @error('reference') is-invalid @enderror"
                                                id="reference" name="reference" value="{{ old('reference') }}"
                                                placeholder="Enter reference number">
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
                                                <i class="bx bx-wallet me-1"></i>Bank Account <span class="text-danger">*</span>
                                            </label>
                                            <select
                                                class="form-select form-select-lg select2-single mt-2 @error('bank_account_id') is-invalid @enderror"
                                                id="bank_account_id" name="bank_account_id" required>
                                                <option value="">-- Select Bank Account --</option>
                                                @foreach($bankAccounts as $bankAccount)
                                                    <option value="{{ $bankAccount->id }}" {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
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
                                        <div class="card border-danger">
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
                                                                class="form-select form-select-lg select2-single @error('payee_type') is-invalid @enderror"
                                                                id="payee_type" name="payee_type" required>
                                                                <option value="">-- Select Payee Type --</option>
                                                                <option value="customer" {{ old('payee_type') == 'customer' ? 'selected' : '' }}>Customer</option>
                                                                <option value="supplier" {{ old('payee_type') == 'supplier' ? 'selected' : '' }}>Supplier</option>
                                                                <option value="other" {{ old('payee_type') == 'other' ? 'selected' : '' }}>Other</option>
                                                            </select>
                                                            @error('payee_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                                    <!-- Customer Selection (shown when payee_type is customer) -->
                                                    <div class="col-lg-8" id="customerSection" style="display: none;">
                                        <div class="mb-3">
                                            <label for="customer_id" class="form-label fw-bold">
                                                                Select Customer <span class="text-danger">*</span>
                                            </label>
                                            <select
                                                class="form-select form-select-lg select2-single @error('customer_id') is-invalid @enderror"
                                                id="customer_id" name="customer_id">
                                                                <option value="">-- Select Customer --</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                        {{ $customer->name }} ({{ $customer->customerNo }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('customer_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                                        </div>
                                                    </div>

                                                    <!-- Supplier Selection (shown when payee_type is supplier) -->
                                                    <div class="col-lg-8" id="supplierSection" style="display: none;">
                                                        <div class="mb-3">
                                                            <label for="supplier_id" class="form-label fw-bold">
                                                                Select Supplier <span class="text-danger">*</span>
                                                            </label>
                                                            <select
                                                                class="form-select form-select-lg select2-single @error('supplier_id') is-invalid @enderror"
                                                                id="supplier_id" name="supplier_id">
                                                                <option value="">-- Select Supplier --</option>
                                                                @foreach($suppliers as $supplier)
                                                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                                        {{ $supplier->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            @error('supplier_id')
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
                                                                class="form-control form-control-lg @error('payee_name') is-invalid @enderror"
                                                                id="payee_name" name="payee_name"
                                                                value="{{ old('payee_name') }}"
                                                                placeholder="Enter payee name"
                                                                required>
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

                                <!-- Transaction Description -->
                                <div class="row mb-4">
                                    <div class="col-12 mb-3">
                                        <label for="description" class="form-label fw-bold">
                                            <i class="bx bx-message-square-detail me-1"></i>Description
                                        </label>
                                        <textarea class="form-control form-control-lg @error('description') is-invalid @enderror"
                                            id="description" name="description" rows="3"
                                            placeholder="Enter payment voucher description">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="attachment" class="form-label fw-bold">
                                            <i class="bx bx-paperclip me-1"></i>Attachment (Optional)
                                        </label>
                                        <input type="file" class="form-control form-control-lg @error('attachment') is-invalid @enderror"
                                            id="attachment" name="attachment" accept=".pdf">
                                        <div class="form-text">Supported format: PDF only (Max: 2MB)</div>
                                        @error('attachment')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Line Items Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card border-danger">
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
                                            <a href="{{ route('accounting.payment-vouchers.index') }}"
                                                class="btn btn-secondary me-2">
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
                                            @can('create payment voucher')
                                            <button type="submit" class="btn btn-success" id="saveBtn">
                                                <i class="bx bx-save me-2"></i>Save
                                            </button>
                                            @endcan
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
        .form-control-lg,
        .form-select-lg {
            font-size: 1.1rem;
            padding: 0.75rem 1rem;
        }

        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1.1rem;
        }

        .line-item-row {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .line-item-row:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .line-item-row .form-label {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .line-item-row .form-select,
        .line-item-row .form-control {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .line-item-row {
                padding: 15px;
            }

            .line-item-row .col-md-4,
            .line-item-row .col-md-3 {
                margin-bottom: 15px;
            }

            .line-item-row .col-md-1 {
                margin-bottom: 15px;
                text-align: center;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            let lineItemCount = 0;

            // Handle payee type selection
            $('#payee_type').on('change', function() {
                const payeeType = $(this).val();
                
                // Hide all sections first
                $('#customerSection, #supplierSection, #otherPayeeSection').hide();
                
                // Reset required attributes and disable all fields
                $('#customer_id, #supplier_id, #payee_name').prop('required', false).prop('disabled', true);
                
                // Show relevant section based on selection and set required fields
                if (payeeType === 'customer') {
                    $('#customerSection').show();
                    $('#customer_id').prop('required', true).prop('disabled', false);
                } else if (payeeType === 'supplier') {
                    $('#supplierSection').show();
                    $('#supplier_id').prop('required', true).prop('disabled', false);
                } else if (payeeType === 'other') {
                    $('#otherPayeeSection').show();
                    $('#payee_name').prop('required', true).prop('disabled', false);
                }
            });

            // Trigger change event on page load if payee_type has a value
            if ($('#payee_type').val()) {
                $('#payee_type').trigger('change');
            }

            // Initialize with one line item
            addLineItem();
            
            // Initialize Select2 for existing chart account selects
            $('.chart-account-select').select2({
                placeholder: 'Select Account',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });

            // Initialize Select2 for all select elements with select2-single class
            $('.select2-single').select2({
                placeholder: 'Select an option',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5'
            });

            // Add line item button
            $('#addLineBtn').on('click', function () {
                addLineItem();
            });

            // Remove line item
            $(document).on('click', '.remove-line-btn', function () {
                $(this).closest('.line-item-row').remove();
                calculateTotal();
            });

            // Calculate total when amounts change
            $(document).on('input', '.amount-input', function () {
                calculateTotal();
            });

            // Form validation
            $('#paymentVoucherForm').on('submit', function (e) {
                e.preventDefault();

                // Validate required fields
                if (!validateForm()) {
                    return false;
                }

                // Disable submit button and show loading state
                $('#saveBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Saving...');

                // Submit form
                this.submit();
            });

            function addLineItem() {
                lineItemCount++;
                const lineItemHtml = `
                                        <div class="line-item-row">
                                            <div class="row">
                                                <div class="col-md-4 mb-2">
                                                    <label for="line_items_${lineItemCount}_chart_account_id" class="form-label fw-bold">
                                                        Account <span class="text-danger">*</span>
                                                    </label>
                                                    <select class="form-select chart-account-select select2-single" name="line_items[${lineItemCount}][chart_account_id]" required>
                                                        <option value="">--- Select Account ---</option>
                                                        @foreach($chartAccounts as $chartAccount)
                                                            <option value="{{ $chartAccount->id }}">
                                                                {{ $chartAccount->account_name }} ({{ $chartAccount->account_code }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <label for="line_items_${lineItemCount}_description" class="form-label fw-bold">
                                                        Description
                                                    </label>
                                                    <input type="text" class="form-control description-input" 
                                                           name="line_items[${lineItemCount}][description]" 
                                                           placeholder="Enter description">
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="line_items_${lineItemCount}_amount" class="form-label fw-bold">
                                                        Amount <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="number" class="form-control amount-input" 
                                                           name="line_items[${lineItemCount}][amount]" 
                                                           step="0.01" min="0" placeholder="0.00" required>
                                                </div>
                                                <div class="col-md-1 mb-2 d-flex align-items-end">
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-line-btn" title="Remove Line">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    `;

                $('#lineItemsContainer').append(lineItemHtml);
                
                // Initialize Select2 for the new chart account select
                setTimeout(function() {
                    $('#lineItemsContainer .chart-account-select').last().select2({
                        placeholder: 'Select Account',
                        allowClear: true,
                        width: '100%',
                        theme: 'bootstrap-5'
                    });
                }, 100);
            }

            function calculateTotal() {
                let total = 0;
                $('.amount-input').each(function () {
                    const amount = parseFloat($(this).val()) || 0;
                    total += amount;
                });

                $('#totalAmount').text(total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                // Update save button state
                if (total > 0) {
                    $('#saveBtn').prop('disabled', false);
                } else {
                    $('#saveBtn').prop('disabled', true);
                }
            }

            function validateForm() {
                let isValid = true;

                // Validate payee information
                const payeeType = $('#payee_type').val();
                if (!payeeType) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select a payee type.',
                        confirmButtonColor: '#dc3545'
                    });
                    isValid = false;
                    return isValid;
                }

                if (payeeType === 'customer' && !$('#customer_id').val()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select a customer.',
                        confirmButtonColor: '#dc3545'
                    });
                    isValid = false;
                    return isValid;
                }

                if (payeeType === 'supplier' && !$('#supplier_id').val()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select a supplier.',
                        confirmButtonColor: '#dc3545'
                    });
                    isValid = false;
                    return isValid;
                }

                if (payeeType === 'other' && !$('#payee_name').val().trim()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please enter a payee name.',
                        confirmButtonColor: '#dc3545'
                    });
                    isValid = false;
                    return isValid;
                }

                // Check if at least one line item has both account and amount
                let hasValidLineItem = false;
                $('.line-item-row').each(function () {
                    const account = $(this).find('.chart-account-select').val();
                    const amount = parseFloat($(this).find('.amount-input').val()) || 0;

                    if (account && amount > 0) {
                        hasValidLineItem = true;
                    }
                });

                if (!hasValidLineItem) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please add at least one line item with account and amount.',
                        confirmButtonColor: '#dc3545'
                    });
                    isValid = false;
                }

                return isValid;
            }

            // Initialize total calculation
            calculateTotal();
        });
    </script>
@endpush 