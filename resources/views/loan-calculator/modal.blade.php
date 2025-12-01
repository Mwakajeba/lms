<!-- Full Loan Calculator Interface for Modal -->
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

<script>
$(document).ready(function() {
    let currentCalculation = null;
    let comparisonScenarios = [];
    
    // Handle URL parameters for pre-filling form
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('product_id')) {
        $('#product_id').val(urlParams.get('product_id')).trigger('change');
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
            // Show product details
            $('#productDetails').show();
            
            // Update product details
            $('#productInterestMethod').text(selectedOption.data('interest-method'));
            $('#productMinAmount').text(formatCurrency(selectedOption.data('min-amount')));
            $('#productMaxAmount').text(formatCurrency(selectedOption.data('max-amount')));
            $('#productMinPeriod').text(selectedOption.data('min-period'));
            $('#productMaxPeriod').text(selectedOption.data('max-period'));
            $('#productInterestRange').text(selectedOption.data('min-interest') + '% - ' + selectedOption.data('max-interest') + '%');
            
            // Update range labels
            $('#amountRangeLabel').text('Range: ' + formatCurrency(selectedOption.data('min-amount')) + ' - ' + formatCurrency(selectedOption.data('max-amount')));
            $('#periodRangeLabel').text('Range: ' + selectedOption.data('min-period') + ' - ' + selectedOption.data('max-period') + ' months');
            $('#interestRangeLabel').text('Range: ' + selectedOption.data('min-interest') + '% - ' + selectedOption.data('max-interest') + '%');
            
            // Set default values
            if (!$('#amount').val()) {
                $('#amount').val(selectedOption.data('min-amount'));
            }
            if (!$('#period').val()) {
                $('#period').val(selectedOption.data('min-period'));
            }
            if (!$('#interest_rate').val()) {
                $('#interest_rate').val(selectedOption.data('min-interest'));
            }
            // Default repayment frequency to product's, but allow change
            const productCycle = selectedOption.data('interest-cycle') || 'monthly';
            $('#interest_cycle').val(productCycle);
        } else {
            $('#productDetails').hide();
            $('#amountRangeLabel').text('');
            $('#periodRangeLabel').text('');
            $('#interestRangeLabel').text('');
        }
    });
    
    // Form submission
    $('#loanCalculatorForm').on('submit', function(e) {
        e.preventDefault();
        calculateLoan();
    });
    
    // Export buttons
    $('#exportPdfBtn').on('click', function() {
        exportCalculation('pdf');
    });
    
    $('#exportExcelBtn').on('click', function() {
        exportCalculation('excel');
    });
    
    // Compare button
    $('#compareBtn').on('click', function() {
        showComparisonModal();
    });
    
    // Calculate loan function
    function calculateLoan() {
        const formData = $('#loanCalculatorForm').serialize();
        
        $.ajax({
            url: '{{ route("loan-calculator.calculate") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    currentCalculation = response;
                    displayResults(response);
                    $('#resultsCard').show();
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
                $('#calculationResults').html(errorHtml);
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
            
            <div class="mb-4">
                <h6>Repayment Schedule (First 10 installments)</h6>
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
                            ${schedule.slice(0, 10).map((installment, index) => `
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
        
        $('#calculationResults').html(resultsHtml);
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
        
        // Create a form to submit the calculation data
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.target = '_blank';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Add calculation data
        Object.keys(currentCalculation).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = JSON.stringify(currentCalculation[key]);
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        Swal.close();
    }
    
    // Show comparison modal
    function showComparisonModal() {
        if (comparisonScenarios.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Scenarios to Compare',
                text: 'Please add at least one scenario to compare.',
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
        let comparisonHtml = '<div class="table-responsive"><table class="table table-striped">';
        comparisonHtml += '<thead><tr><th>Scenario</th><th>Monthly Payment</th><th>Total Interest</th><th>Total Amount</th><th>Interest %</th></tr></thead>';
        comparisonHtml += '<tbody>';
        
        comparisonScenarios.forEach((scenario, index) => {
            comparisonHtml += `
                <tr>
                    <td>Scenario ${index + 1}</td>
                    <td>${formatCurrency(scenario.totals.monthly_payment)}</td>
                    <td>${formatCurrency(scenario.totals.total_interest)}</td>
                    <td>${formatCurrency(scenario.totals.total_amount)}</td>
                    <td>${scenario.summary.interest_percentage}%</td>
                </tr>
            `;
        });
        
        comparisonHtml += '</tbody></table></div>';
        $('#comparisonContent').html(comparisonHtml);
    }
    
    // Reset function
    window.resetCalculator = function() {
        $('#loanCalculatorForm')[0].reset();
        $('#loanCalculatorForm').removeClass('was-validated');
        $('#productDetails').hide();
        $('#resultsCard').hide();
        $('#amountRangeLabel').text('');
        $('#periodRangeLabel').text('');
        $('#interestRangeLabel').text('');
        currentCalculation = null;
    };
    
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
});
</script>
