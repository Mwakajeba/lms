@extends('layouts.main')

@section('title', __('app.create_budget'))
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => __('app.budgets'), 'url' => route('accounting.budgets.index'), 'icon' => 'bx bx-chart'],
            ['label' => __('app.create_budget'), 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">{{ __('app.create_budget') }}</h6>
        <hr />

        @can('create budget')
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bx bx-plus-circle me-2"></i>
                            {{ __('app.budget_new_budget') }} {{ __('app.info') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="budgetForm" action="{{ route('accounting.budgets.store') }}" method="POST">
                            @csrf
                            
                                                         <!-- Basic Information Section -->
                             <div class="row mb-4">
                                 <div class="col-12">
                                     <h6 class="text-primary fw-bold mb-3">
                                         <i class="bx bx-info-circle me-2"></i>
                                         {{ __('app.info') }} {{ __('app.basic') }}
                                     </h6>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="form-group">
                                         <label class="form-label fw-bold">
                                             <i class="bx bx-bookmark me-1"></i>
                                             {{ __('app.budget_name') }} <span class="text-danger">*</span>
                                         </label>
                                         <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                name="name" value="{{ old('name') }}" 
                                                placeholder="Weka jina la bajeti (mfano: Bajeti ya Mwaka 2024)" required>
                                         @error('name')
                                             <div class="invalid-feedback">{{ $message }}</div>
                                         @enderror
                                     </div>
                                 </div>
                                 <div class="col-md-3">
                                     <div class="form-group">
                                         <label class="form-label fw-bold">
                                             <i class="bx bx-calendar me-1"></i>
                                             {{ __('app.budget_year') }} <span class="text-danger">*</span>
                                         </label>
                                         <select class="form-select select2-single @error('year') is-invalid @enderror" name="year" required>
                                             <option value="">{{ __('app.select') }} {{ __('app.budget_year') }}</option>
                                             @for($year = date('Y') - 2; $year <= date('Y') + 3; $year++)
                                                 <option value="{{ $year }}" {{ old('year') == $year ? 'selected' : '' }}>
                                                     {{ $year }}
                                                 </option>
                                             @endfor
                                         </select>
                                         @error('year')
                                             <div class="invalid-feedback">{{ $message }}</div>
                                         @enderror
                                     </div>
                                 </div>
                                 <div class="col-md-3">
                                     <div class="form-group">
                                         <label class="form-label fw-bold">
                                             <i class="bx bx-building me-1"></i>
                                             {{ __('app.budget_branch') }}
                                         </label>
                                         <input type="text" class="form-control" value="{{ Auth::user()->branch->name ?? 'N/A' }}" readonly>
                                         <small class="text-muted">{{ __('app.budget_branch_automatically_set') }}</small>
                                     </div>
                                 </div>
                                 <div class="col-12 mt-3">
                                     <div class="form-group">
                                         <label class="form-label fw-bold">
                                             <i class="bx bx-message-square-detail me-1"></i>
                                             {{ __('app.budget_description') }}
                                         </label>
                                         <textarea class="form-control @error('description') is-invalid @enderror" 
                                                   name="description" rows="3" 
                                                   placeholder="Weka maelezo ya bajeti (si lazima)">{{ old('description') }}</textarea>
                                         @error('description')
                                             <div class="invalid-feedback">{{ $message }}</div>
                                         @enderror
                                     </div>
                                 </div>
                             </div>

                                                         <!-- Budget Lines Section -->
                             <div class="row mb-4">
                                 <div class="col-12">
                                     <div class="d-flex justify-content-between align-items-center mb-3">
                                         <h6 class="text-primary fw-bold mb-0">
                                             <i class="bx bx-list-ul me-2"></i>
                                             {{ __('app.budget_lines') }}
                                         </h6>
                                     </div>
                                     
                                     <div class="alert alert-info">
                                         <i class="bx bx-info-circle me-2"></i>
                                         <strong>{{ __('app.tip') }}:</strong> {{ __('app.budget_tip_add_lines') }}
                                     </div>
                                     
                                    
                                    <div id="budgetLinesContainer">
                                        <!-- Budget lines will be added here dynamically -->
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row mb-2">
                                <div class="col-12">
                                    <div class="d-flex justify-content-start mb-3">
                                        <button type="button" class="btn btn-primary" id="addBudgetLine">
                                            <i class="bx bx-plus"></i> {{ __('app.budget_add_line') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                                                        <a href="{{ route('accounting.budgets.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x"></i> {{ __('app.cancel') }}
                                        </a>
                                        <button type="submit" id="submitBtn" class="btn btn-primary">
                                            <i class="bx bx-save"></i> {{ __('app.create_budget') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="bx bx-lock-open me-2"></i>
            {{ __('app.create_budget_permission_message') }}
        </div>
        @endcan
    </div>
</div>

<!-- Budget Line Template -->
<template id="budgetLineTemplate">
    <div class="budget-line-item card mb-3 border-primary">
 
                         <div class="card-body">
                     <div class="row">
                         <div class="col-md-4">
                             <div class="form-group">
                                 <label class="form-label fw-bold">
                                     <i class="bx bx-account me-1"></i>
                                                                                  {{ __('app.account') }} <span class="text-danger">*</span>
                                 </label>
                                 <select class="form-select select2-single account-select" name="budget_lines[{index}][account_id]" required>
                                                                                  <option value="">{{ __('app.select_account') }}</option>
                                     @foreach($accounts as $account)
                                         <option value="{{ $account->id }}">
                                             {{ $account->account_code }} - {{ $account->account_name }}
                                             @if($account->accountClassGroup)
                                                 ({{ $account->accountClassGroup->name }})
                                             @endif
                                         </option>
                                     @endforeach
                                 </select>
                             </div>
                         </div>
                         <div class="col-md-3">
                             <div class="form-group">
                                 <label class="form-label fw-bold">
                                     <i class="bx bx-money me-1"></i>
                                                                                  {{ __('app.amount') }} <span class="text-danger">*</span>
                                 </label>
                                 <div class="input-group">
                                     <span class="input-group-text">TZS</span>
                                     <input type="number" class="form-control amount-input" 
                                            name="budget_lines[{index}][amount]" step="0.01" min="0" 
                                            placeholder="0.00" required>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-3">
                             <div class="form-group">
                                 <label class="form-label fw-bold">
                                     <i class="bx bx-category me-1"></i>
                                                                                  {{ __('app.category') }} <span class="text-danger">*</span>
                                 </label>
                                 <select class="form-select select2-single category-select" name="budget_lines[{index}][category]" required>
                                                                                  <option value="">{{ __('app.select_category') }}</option>
                                                                            <option value="Revenue">{{ __('app.revenue') }}</option>
                                       <option value="Expense">{{ __('app.expense') }}</option>
                                       <option value="Capital Expenditure">{{ __('app.capital_expenditure') }}</option>
                                 </select>
                             </div>
                         </div>
                         <div class="col-md-2">
                             <div class="form-group">
                                 <label class="form-label fw-bold">
                                     <i class="bx bx-trash me-1"></i>
                                                                                  {{ __('app.actions') }}
                                 </label>
                                 <div class="d-flex justify-content-center">
                                     <button type="button" class="btn btn-outline-danger btn-sm remove-line" title="Remove this line">
                                         <i class="bx bx-trash me-1"></i>
                                         {{ __('app.budget_remove_line') }}
                                     </button>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let lineIndex = 0;
    const accounts = @json($accounts);
    
    // Initialize Select2 for existing selects
    function initSelect2(context) {
        const scope = context ? $(context) : $(document);
        scope.find('select.select2-single').select2({ width: '100%' });
    }
    initSelect2();

    // Add budget line
    $('#addBudgetLine').click(function() {
        const template = document.getElementById('budgetLineTemplate').innerHTML;
        const newLine = template.replace(/{index}/g, lineIndex);
        
        const $node = $(newLine);
        $('#budgetLinesContainer').append($node);
        lineIndex++;
        
        // Update line numbers
        updateLineNumbers();
        
        // Add animation
        const $last = $('.budget-line-item').last();
        $last.hide().fadeIn(300);

        // Initialize select2 on newly added selects
        initSelect2($last);
    });
    
    // Remove budget line
    $(document).on('click', '.remove-line', function() {
        const item = $(this).closest('.budget-line-item');
        item.fadeOut(300, function() {
            $(this).remove();
            updateLineNumbers();
        });
    });
    
    // Update line numbers
    function updateLineNumbers() {
        $('.budget-line-item').each(function(index) {
            $(this).find('.line-number').text(index + 1);
            $(this).find('select, input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                }
            });
        });
    }
    
    // Add first line by default
    $('#addBudgetLine').click();
    
    // Form validation
    $('#budgetForm').submit(function(e) {
        // Disable submit button to prevent double submission
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.addClass('opacity-50');
        submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> {{ __('app.processing') ?? 'Processing...' }}');

        const lines = $('.budget-line-item');
        if (lines.length === 0) {
            e.preventDefault();
            // Re-enable on validation error
            submitBtn.prop('disabled', false);
            submitBtn.removeClass('opacity-50');
            submitBtn.html('<i class="bx bx-save"></i> {{ __('app.create_budget') }}');
            Swal.fire({
                icon: 'warning',
                title: '{{ __('app.no_budget_lines') }}',
                text: '{{ __('app.no_budget_lines_error') }}',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }
        
        // Check for duplicate accounts
        const accounts = [];
        let hasDuplicates = false;
        
        $('.account-select').each(function() {
            const accountId = $(this).val();
            if (accountId && accounts.includes(accountId)) {
                hasDuplicates = true;
                return false;
            }
            if (accountId) {
                accounts.push(accountId);
            }
        });
        
        if (hasDuplicates) {
            e.preventDefault();
            // Re-enable on validation error
            submitBtn.prop('disabled', false);
            submitBtn.removeClass('opacity-50');
            submitBtn.html('<i class="bx bx-save"></i> {{ __('app.create_budget') }}');
            Swal.fire({
                icon: 'error',
                title: '{{ __('app.duplicate_accounts') }}',
                text: '{{ __('app.duplicate_accounts_error') }}',
                confirmButtonColor: '#d33'
            });
            return false;
        }
    });
    
    // Format amount inputs
    // $(document).on('input', '.amount-input', function() {
    //     const value = parseFloat($(this).val());
    //     if (!isNaN(value)) {
    //         $(this).val(value.toFixed(2));
    //     }
    // });
    
    // Auto-select current year
    $('select[name="year"]').val('{{ date("Y") }}').trigger('change');
    
    // Add hover effects
    $('.budget-line-item').hover(
        function() { $(this).addClass('shadow-sm'); },
        function() { $(this).removeClass('shadow-sm'); }
    );
});
</script>
@endpush

@push('styles')
<style>
.budget-line-item {
    transition: all 0.3s ease;
    border-left: 4px solid #007bff !important;
}

.budget-line-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.card-header.bg-primary {
    background: linear-gradient(45deg, #007bff, #0056b3) !important;
}

.form-label {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.alert-info {
    background: linear-gradient(45deg, #d1ecf1, #bee5eb);
    border-color: #bee5eb;
}

.btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #0056b3, #004085);
    transform: translateY(-1px);
}

.page-breadcrumb {
    background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.btn-outline-danger {
    border-color: #dc3545;
    color: #dc3545;
    transition: all 0.3s ease;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.remove-line {
    font-size: 0.8rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.remove-line:hover {
    transform: scale(1.05);
}
</style>
@endpush 