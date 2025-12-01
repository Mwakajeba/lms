@extends('layouts.main')


@section('title', 'Loan Calculator')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Loan Calculator', 'url' => '#', 'icon' => 'bx bx-calculator']
        ]" />

            <h6 class="mb-0 text-uppercase">LOAN CALCULATOR</h6>
            <hr />

    <div class="row">
        <!-- Calculator Form -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-calculator me-2"></i>Loan Calculator
                    </h5>
                </div>
                <div class="card-body">
                    <form id="loanCalculatorForm">
                        @csrf
                        
                        <!-- Product Selection -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Loan Product <span class="text-danger">*</span></label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">Select Loan Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product['id'] }}" 
                                            data-min-rate="{{ $product['min_interest_rate'] }}"
                                            data-max-rate="{{ $product['max_interest_rate'] }}"
                                            data-min-amount="{{ $product['min_principal'] }}"
                                            data-max-amount="{{ $product['max_principal'] }}"
                                            data-min-period="{{ $product['min_period'] }}"
                                            data-max-period="{{ $product['max_period'] }}"
                                            data-method="{{ $product['interest_method'] }}"
                                            data-cycle="{{ $product['interest_cycle'] }}">
                                        {{ $product['name'] }} ({{ ucfirst($product['product_type']) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Loan Amount -->
                        <div class="mb-3">
                            <label for="amount" class="form-label">Loan Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">TZS</span>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       placeholder="Enter loan amount" required min="1" step="0.01">
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted" id="amount-range"></small>
                        </div>

                        <!-- Loan Period -->
                        <div class="mb-3">
                            <label for="period" class="form-label">Loan Period <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="period" name="period" 
                                       placeholder="Enter period" required min="1">
                                <select class="form-select" id="interest_cycle" name="interest_cycle" style="max-width: 180px;">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly" selected>Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="semi_annually">Semi Annually</option>
                                    <option value="annually">Annually</option>
                                </select>
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted" id="period-range"></small>
                        </div>

                        <!-- Interest Rate -->
                        <div class="mb-3">
                            <label for="interest_rate" class="form-label">Interest Rate <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="interest_rate" name="interest_rate" 
                                       placeholder="Enter interest rate" required min="0" step="0.01">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="form-text text-muted" id="rate-range"></small>
                        </div>

                        <!-- Start Date -->
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ date('Y-m-d') }}" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="calculateBtn">
                                <i class="bx bx-calculator me-1"></i>Calculate Loan
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="compareBtn" disabled>
                                <i class="bx bx-git-compare me-1"></i>Compare Scenarios
                            </button>
                            <button type="button" class="btn btn-outline-info" id="resetBtn">
                                <i class="bx bx-reset me-1"></i>Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Product Details -->
            <div class="card mt-3" id="productDetailsCard" style="display: none;">
                <div class="card-header">
                    <h6 class="card-title mb-0">Product Details</h6>
                </div>
                <div class="card-body" id="productDetails">
                    <!-- Product details will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-8">
            <!-- Loading State -->
            <div class="card" id="loadingCard" style="display: none;">
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Calculating...</span>
                    </div>
                    <p class="mt-3 mb-0">Calculating loan details...</p>
                </div>
            </div>

            <!-- Error State -->
            <div class="card" id="errorCard" style="display: none;">
                <div class="card-body">
                    <div class="alert alert-danger mb-0" id="errorMessage">
                        <!-- Error message will be displayed here -->
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="card" id="resultsCard" style="display: none;">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Calculation Results</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="exportPdfBtn">
                                <i class="bx bx-file-pdf me-1"></i>PDF
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" id="exportExcelBtn">
                                <i class="bx bx-file-excel me-1"></i>Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="resultsContent">
                    <!-- Results will be displayed here -->
                </div>
            </div>

            <!-- Comparison Results -->
            <div class="card" id="comparisonCard" style="display: none;">
                <div class="card-header">
                    <h5 class="card-title mb-0">Comparison Results</h5>
                </div>
                <div class="card-body" id="comparisonContent">
                    <!-- Comparison results will be displayed here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comparison Modal -->
<div class="modal fade" id="comparisonModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compare Loan Scenarios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="comparisonForm">
                    <!-- Comparison form will be generated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="compareScenariosBtn">Compare</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentCalculation = null;
    let comparisonScenarios = [];
    
    // Handle URL parameters for pre-filling form
    const urlParams = new URLSearchParams(window.location.search);
    let cameFromForm = false;
    
    if (urlParams.get('product_id')) {
        $('#product_id').val(urlParams.get('product_id')).trigger('change');
        cameFromForm = true;
    }
    if (urlParams.get('amount')) {
        $('#amount').val(urlParams.get('amount'));
    }
    if (urlParams.get('period')) {
        $('#period').val(urlParams.get('period'));
    }
    if (urlParams.get('interest_rate')) {
        $('#interest_rate').val(urlParams.get('interest_rate'));
    }
    if (urlParams.get('start_date')) {
        $('#start_date').val(urlParams.get('start_date'));
    }
    
    // Show back button if came from form
    if (cameFromForm) {
        $('#backToFormBtn').show();
    }
    
    // Auto-calculate if all parameters are provided
    if (urlParams.get('product_id') && urlParams.get('amount') && urlParams.get('period') && 
        urlParams.get('interest_rate') && urlParams.get('start_date')) {
        setTimeout(function() {
            calculateLoan();
        }, 1000); // Wait for product details to load
    }
    
    // Product selection change
    $('#product_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const productId = selectedOption.val();
        
        if (productId) {
            loadProductDetails(productId);
            updateFormLimits(selectedOption);
        } else {
            hideProductDetails();
            resetFormLimits();
        }
    });
    
    // Form submission
    $('#loanCalculatorForm').on('submit', function(e) {
        e.preventDefault();
        calculateLoan();
    });
    
    // Compare button
    $('#compareBtn').on('click', function() {
        if (currentCalculation) {
            showComparisonModal();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No Calculation',
                text: 'Please calculate a loan first before comparing scenarios.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#007bff'
            });
        }
    });
    
    // Reset button
    $('#resetBtn').on('click', function() {
        Swal.fire({
            title: 'Reset Calculator',
            text: 'Are you sure you want to reset all fields and clear the current calculation?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reset',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                resetForm();
                Swal.fire({
                    icon: 'success',
                    title: 'Reset Complete',
                    text: 'Calculator has been reset successfully!',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    });
    
    // Export buttons
    $('#exportPdfBtn').on('click', function() {
        exportCalculation('pdf');
    });
    
    $('#exportExcelBtn').on('click', function() {
        exportCalculation('excel');
    });
    
    // Back to form button
    $('#backToFormBtn').on('click', function() {
        // Go back to previous page
        window.history.back();
    });
    
    // Load product details
    function loadProductDetails(productId) {
        $.ajax({
            url: '{{ route("loan-calculator.product-details") }}',
            method: 'GET',
            data: { product_id: productId },
            success: function(response) {
                if (response.success) {
                    displayProductDetails(response.product);
                }
            },
            error: function(xhr) {
                console.error('Error loading product details:', xhr.responseText);
            }
        });
    }
    
    // Display product details
    function displayProductDetails(product) {
        const detailsHtml = `
            <div class="row">
                <div class="col-6">
                    <strong>Product Type:</strong><br>
                    <span class="text-capitalize">${product.product_type}</span>
                </div>
                <div class="col-6">
                    <strong>Interest Method:</strong><br>
                    <span class="text-capitalize">${product.interest_method.replace(/_/g, ' ')}</span>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-6">
                    <strong>Interest Cycle:</strong><br>
                    <span class="text-capitalize">${product.interest_cycle}</span>
                </div>
                <div class="col-6">
                    <strong>Grace Period:</strong><br>
                    <span>${product.grace_period} days</span>
                </div>
            </div>
            ${product.fees.length > 0 ? `
                <hr>
                <strong>Fees (${product.fees.length}):</strong><br>
                ${product.fees.map(fee => `${fee.name} (${fee.type})`).join(', ')}
            ` : ''}
            ${product.penalties.length > 0 ? `
                <hr>
                <strong>Penalties (${product.penalties.length}):</strong><br>
                ${product.penalties.map(penalty => `${penalty.name} (${penalty.type})`).join(', ')}
            ` : ''}
        `;
        
        $('#productDetails').html(detailsHtml);
        $('#productDetailsCard').show();
    }
    
    // Hide product details
    function hideProductDetails() {
        $('#productDetailsCard').hide();
    }
    
    // Update form limits based on product
    function updateFormLimits(selectedOption) {
        const minAmount = parseFloat(selectedOption.data('min-amount'));
        const maxAmount = parseFloat(selectedOption.data('max-amount'));
        const minPeriod = parseInt(selectedOption.data('min-period'));
        const maxPeriod = parseInt(selectedOption.data('max-period'));
        const minRate = parseFloat(selectedOption.data('min-rate'));
        const maxRate = parseFloat(selectedOption.data('max-rate'));
        const cycle = selectedOption.data('cycle');
        
        // Update amount field
        $('#amount').attr('min', minAmount).attr('max', maxAmount);
        $('#amount-range').text(`Range: ${formatCurrency(minAmount)} - ${formatCurrency(maxAmount)}`);
        
        // Update period field
        $('#period').attr('min', minPeriod).attr('max', maxPeriod);
        $('#period-range').text(`Range: ${minPeriod} - ${maxPeriod} ${cycle}`);
        $('#interest_cycle').val(cycle);
        
        // Update interest rate field
        $('#interest_rate').attr('min', minRate).attr('max', maxRate);
        $('#rate-range').text(`Range: ${minRate}% - ${maxRate}%`);
        
        // Set default interest rate
        $('#interest_rate').val(minRate);
    }
    
    // Reset form limits
    function resetFormLimits() {
        $('#amount').removeAttr('min max');
        $('#period').removeAttr('min max');
        $('#interest_rate').removeAttr('min max');
        $('#amount-range, #period-range, #rate-range').text('');
        $('#period-unit').text('Months');
    }
    
    // Calculate loan
    function calculateLoan() {
        const formData = $('#loanCalculatorForm').serialize();
        
        showLoading();
        
        $.ajax({
            url: '{{ route("loan-calculator.calculate") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    currentCalculation = response;
                    displayResults(response);
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Calculation Complete',
                        text: 'Loan calculation completed successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    showError(response.error || 'Calculation failed');
                }
            },
            error: function(xhr) {
                hideLoading();
                const errors = xhr.responseJSON?.errors || {};
                showValidationErrors(errors);
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Calculation Failed',
                    text: 'Please check your inputs and try again.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    }
    
    // Display results
    function displayResults(calculation) {
        const { product, totals, schedule, fees, penalties, summary } = calculation;
        
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

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 bg-success text-white">
                        <div class="card-body text-center">
                            <h4 class="mb-1">${formatCurrency(totals.principal - totals.total_fees)}</h4>
                            <p class="mb-0">Net Amount After Fees (Disbursed)</p>
                        </div>
                    </div>
                </div>
            </div>
            
            ${fees.length > 0 ? `
                <div class="mb-4">
                    <h6>Fees Breakdown</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fee Name</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Application</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${fees.map(fee => `
                                    <tr>
                                        <td>${fee.name}</td>
                                        <td><span class="badge bg-${fee.type === 'percentage' ? 'info' : 'primary'}">${fee.type}</span></td>
                                        <td>${formatCurrency(fee.amount)}</td>
                                        <td><small class="text-muted">${fee.criteria.replace(/_/g, ' ')}</small></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            ` : ''}
            
            <div class="mb-4">
                <h6>Repayment Schedule</h6>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
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
                        <tbody>
                            ${schedule.map((installment, index) => `
                                <tr>
                                    <td>${installment.installment_number}</td>
                                    <td>${formatDate(installment.due_date)}</td>
                                    <td>${formatCurrency(installment.principal)}</td>
                                    <td>${formatCurrency(installment.interest)}</td>
                                    <td>${formatCurrency(installment.fee_amount)}</td>
                                    <td><strong>${formatCurrency(installment.total_amount)}</strong></td>
                                    <td>${formatCurrency(installment.remaining_balance ?? 0)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        
        $('#resultsContent').html(resultsHtml);
        $('#resultsCard').show();
        $('#compareBtn').prop('disabled', false);
    }
    
    // Show comparison modal
    function showComparisonModal() {
        const modalHtml = `
            <div class="row">
                <div class="col-12">
                    <h6>Current Scenario</h6>
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Product:</strong> ${currentCalculation.product.name}<br>
                                    <strong>Amount:</strong> ${formatCurrency(currentCalculation.totals.principal)}<br>
                                    <strong>Period:</strong> ${$('#period').val()} months
                                </div>
                                <div class="col-6">
                                    <strong>Rate:</strong> ${$('#interest_rate').val()}%<br>
                                    <strong>Monthly Payment:</strong> ${formatCurrency(currentCalculation.totals.monthly_payment)}<br>
                                    <strong>Total Amount:</strong> ${formatCurrency(currentCalculation.totals.total_amount)}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-12">
                    <h6>Compare With</h6>
                    <div id="comparisonScenarios">
                        <!-- Comparison scenarios will be added here -->
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addScenarioBtn">
                        <i class="bx bx-plus me-1"></i>Add Scenario
                    </button>
                </div>
            </div>
        `;
        
        $('#comparisonForm').html(modalHtml);
        $('#comparisonModal').modal('show');
        
        // Add scenario button
        $('#addScenarioBtn').on('click', function() {
            addComparisonScenario();
        });
    }
    
    // Add comparison scenario
    function addComparisonScenario() {
        const scenarioIndex = comparisonScenarios.length;
        const scenarioHtml = `
            <div class="card mb-3" data-scenario="${scenarioIndex}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Scenario ${scenarioIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeScenario(${scenarioIndex})">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Product</label>
                            <select class="form-select scenario-product" data-scenario="${scenarioIndex}">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product['id'] }}">{{ $product['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control scenario-amount" data-scenario="${scenarioIndex}" placeholder="Amount">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <label class="form-label">Period</label>
                            <input type="number" class="form-control scenario-period" data-scenario="${scenarioIndex}" placeholder="Period">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Interest Rate</label>
                            <input type="number" class="form-control scenario-rate" data-scenario="${scenarioIndex}" placeholder="Rate">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control scenario-date" data-scenario="${scenarioIndex}" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#comparisonScenarios').append(scenarioHtml);
        comparisonScenarios.push({
            product_id: '',
            amount: '',
            period: '',
            interest_rate: '',
            start_date: '{{ date("Y-m-d") }}',
            name: `Scenario ${scenarioIndex + 1}`
        });
    }
    
    // Remove scenario
    window.removeScenario = function(index) {
        $(`[data-scenario="${index}"]`).remove();
        comparisonScenarios.splice(index, 1);
        
        // Renumber remaining scenarios
        $('[data-scenario]').each(function() {
            const currentIndex = parseInt($(this).data('scenario'));
            if (currentIndex > index) {
                $(this).data('scenario', currentIndex - 1);
                $(this).find('.card-header h6').text(`Scenario ${currentIndex}`);
            }
        });
    };
    
    // Compare scenarios
    $('#compareScenariosBtn').on('click', function() {
        const scenarios = [];
        
        // Add current scenario
        scenarios.push({
            product_id: $('#product_id').val(),
            amount: parseFloat($('#amount').val()),
            period: parseInt($('#period').val()),
            interest_rate: parseFloat($('#interest_rate').val()),
            start_date: $('#start_date').val(),
            name: 'Current Scenario'
        });
        
        // Add comparison scenarios
        $('[data-scenario]').each(function() {
            const scenarioIndex = $(this).data('scenario');
            const productId = $(this).find('.scenario-product').val();
            const amount = parseFloat($(this).find('.scenario-amount').val());
            const period = parseInt($(this).find('.scenario-period').val());
            const rate = parseFloat($(this).find('.scenario-rate').val());
            const date = $(this).find('.scenario-date').val();
            
            if (productId && amount && period && rate && date) {
                scenarios.push({
                    product_id: productId,
                    amount: amount,
                    period: period,
                    interest_rate: rate,
                    start_date: date,
                    name: `Scenario ${scenarioIndex + 1}`
                });
            }
        });
        
        if (scenarios.length < 2) {
            Swal.fire({
                icon: 'warning',
                title: 'Insufficient Scenarios',
                text: 'Please add at least one comparison scenario',
                confirmButtonText: 'OK',
                confirmButtonColor: '#007bff'
            });
            return;
        }
        
        compareScenarios(scenarios);
    });
    
    // Compare scenarios function
    function compareScenarios(scenarios) {
        showLoading();
        
        $.ajax({
            url: '{{ route("loan-calculator.compare") }}',
            method: 'POST',
            data: {
                scenarios: scenarios,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                hideLoading();
                $('#comparisonModal').modal('hide');
                
                if (response.success) {
                    displayComparison(response);
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Comparison Complete',
                        text: 'Loan scenarios compared successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    showError(response.error || 'Comparison failed');
                }
            },
            error: function(xhr) {
                hideLoading();
                showError('Comparison failed: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }
    
    // Display comparison results
    function displayComparison(comparison) {
        const { comparisons } = comparison;
        
        let comparisonHtml = `
            <div class="row mb-4">
                <div class="col-12">
                    <h5>Comparison Results</h5>
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
                            <th>Rate</th>
                            <th>Monthly Payment</th>
                            <th>Total Interest</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        comparisons.forEach(comparison => {
            if (comparison.result.success) {
                const { product, totals } = comparison.result;
                comparisonHtml += `
                    <tr>
                        <td><strong>${comparison.name}</strong></td>
                        <td>${product.name}</td>
                        <td>${formatCurrency(totals.principal)}</td>
                        <td>${comparison.result.summary.period} months</td>
                        <td>${comparison.result.summary.interest_rate}%</td>
                        <td><strong>${formatCurrency(totals.monthly_payment)}</strong></td>
                        <td>${formatCurrency(totals.total_interest)}</td>
                        <td><strong>${formatCurrency(totals.total_amount)}</strong></td>
                    </tr>
                `;
            }
        });
        
        comparisonHtml += `
                    </tbody>
                </table>
            </div>
        `;
        
        $('#comparisonContent').html(comparisonHtml);
        $('#comparisonCard').show();
    }
    
    // Export calculation
    function exportCalculation(format) {
        if (!currentCalculation) {
            Swal.fire({
                icon: 'warning',
                title: 'No Calculation',
                text: 'Please calculate a loan first before exporting.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#007bff'
            });
            return;
        }
        
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
        
        const params = {
            product_id: $('#product_id').val(),
            amount: $('#amount').val(),
            period: $('#period').val(),
            interest_rate: $('#interest_rate').val(),
            start_date: $('#start_date').val(),
            interest_cycle: $('#interest_cycle').val()
        };
        
        const query = $.param(params);
        const exportUrl = url + '?' + query;
        window.open(exportUrl, '_blank');
        Swal.close();
    }
    
    // Utility functions
    function showLoading() {
        $('#loadingCard').show();
        $('#errorCard, #resultsCard, #comparisonCard').hide();
    }
    
    function hideLoading() {
        $('#loadingCard').hide();
    }
    
    function showError(message) {
        $('#errorMessage').text(message);
        $('#errorCard').show();
        $('#loadingCard, #resultsCard, #comparisonCard').hide();
    }
    
    function showValidationErrors(errors) {
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Show new errors
        Object.keys(errors).forEach(field => {
            $(`#${field}`).addClass('is-invalid');
            $(`#${field}`).siblings('.invalid-feedback').text(errors[field][0]);
        });
    }
    
    function resetForm() {
        $('#loanCalculatorForm')[0].reset();
        $('#product_id').val('');
        hideProductDetails();
        resetFormLimits();
        $('#errorCard, #resultsCard, #comparisonCard').hide();
        currentCalculation = null;
        $('#compareBtn').prop('disabled', true);
    }
    
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
});
</script>
@endpush
