@extends('layouts.main')

@section('title', 'Create Bill Purchase')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Bill Purchases', 'url' => route('accounting.bill-purchases'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Create Bill', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE BILL PURCHASE</h6>
        <hr />

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

        <!-- Bill Form -->
        <form action="{{ route('accounting.bill-purchases.store') }}" method="POST" id="billForm">
            @csrf
            <div class="row">
                <!-- Basic Information -->
                <div class="col-12">
                    <div class="card border-top border-0 border-4 border-primary">
                        <div class="card-body p-5">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Create New Bill Purchase</h5>
                            </div>
                            <hr>
                            
                            <!-- Basic Information Fields -->
                            <div class="row">
                                <!-- Bill Details -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Bill Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" 
                                           value="{{ old('date', date('Y-m-d')) }}" required>
                                    @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Due Date</label>
                                    <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" 
                                           value="{{ old('due_date') }}">
                                    @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <!-- Supplier Information -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Supplier <span class="text-danger">*</span></label>
                                    <select name="supplier_id" class="form-select select2-single @error('supplier_id') is-invalid @enderror" required>
                                        <option value="">-- Select Supplier --</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Accounting Information -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Credit Account (Accounts Payable) <span class="text-danger">*</span></label>
                                    <select name="credit_account" class="form-select select2-single @error('credit_account') is-invalid @enderror" required>
                                        <option value="">-- Select Credit Account --</option>
                                        @foreach($chartAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('credit_account') == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_code }} - {{ $account->account_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('credit_account') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Notes -->
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">Notes</label>
                                    <textarea name="note" class="form-control @error('note') is-invalid @enderror" 
                                              rows="3" placeholder="Enter any additional notes or description for this bill...">{{ old('note') }}</textarea>
                                    @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Line Items</h5>
                        </div>
                        <div class="card-body">
                            <div id="lineItemsContainer">
                                <div class="line-item row mb-3" data-index="0">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Debit Account <span class="text-danger">*</span></label>
                                        <select name="line_items[0][debit_account]" class="form-select debit-account select2-single" required data-placeholder="Select account">
                                            <option value="">-- Select Account --</option>
                                            @foreach($chartAccounts as $account)
                                                <option value="{{ $account->id }}">
                                                    {{ $account->account_code }} - {{ $account->account_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                                        <input type="number" name="line_items[0][amount]" class="form-control amount-input" 
                                               step="0.01" min="0.01" required placeholder="0.00">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Description</label>
                                        <input type="text" name="line_items[0][description]" class="form-control" 
                                               placeholder="Enter description">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger btn-sm remove-line-item" style="display: none;">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="button" class="btn btn-success btn-sm" id="addLineItem">
                                        <i class="bx bx-plus me-1"></i> Add Line Item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bx bx-calculator me-2"></i>Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Total Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">TZS</span>
                                            <input type="text" id="totalAmount" class="form-control fw-bold text-success" 
                                                   value="0.00" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm js-submit-once">
                                            <i class="bx bx-save me-1"></i> Create
                                        </button>
                                        <a href="{{ route('accounting.bill-purchases') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="bx bx-arrow-back me-1"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let lineItemIndex = 1;

    // Add line item
    $('#addLineItem').click(function() {
        const newLineItem = `
            <div class="line-item row mb-3" data-index="${lineItemIndex}">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Debit Account <span class="text-danger">*</span></label>
                    <select name="line_items[${lineItemIndex}][debit_account]" class="form-select debit-account" required>
                        <option value="">-- Select Account --</option>
                        @foreach($chartAccounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->account_code }} - {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                    <input type="number" name="line_items[${lineItemIndex}][amount]" class="form-control amount-input" 
                           step="0.01" min="0.01" required placeholder="0.00">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Description</label>
                    <input type="text" name="line_items[${lineItemIndex}][description]" class="form-control" 
                           placeholder="Enter description">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm remove-line-item">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#lineItemsContainer').append(newLineItem);
        lineItemIndex++;
        updateRemoveButtons();
    });

    // Remove line item
    $(document).on('click', '.remove-line-item', function() {
        $(this).closest('.line-item').remove();
        updateRemoveButtons();
        calculateTotal();
    });

    // Calculate total when amount changes
    $(document).on('input', '.amount-input', function() {
        calculateTotal();
    });

    function updateRemoveButtons() {
        const lineItems = $('.line-item');
        if (lineItems.length > 1) {
            $('.remove-line-item').show();
        } else {
            $('.remove-line-item').hide();
        }
    }

    function calculateTotal() {
        let total = 0;
        $('.amount-input').each(function() {
            const amount = parseFloat($(this).val()) || 0;
            total += amount;
        });
        $('#totalAmount').val(total.toFixed(2));
    }

    // Prevent double submit: disable and fade submit button
    $('#billForm').on('submit', function(e) {
        const btn = $(this).find('.js-submit-once');
        btn.prop('disabled', true).addClass('disabled').css({ opacity: 0.6, cursor: 'not-allowed' });
        const total = parseFloat($('#totalAmount').val()) || 0;
        if (total <= 0) {
            e.preventDefault();
            alert('Please add at least one line item with a valid amount.');
            btn.prop('disabled', false).removeClass('disabled').css({ opacity: 1, cursor: 'pointer' });
            return false;
        }
    });

    // Initialize
    updateRemoveButtons();
    calculateTotal();
});
</script>
@endpush 