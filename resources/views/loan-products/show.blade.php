@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Loan Product Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <x-breadcrumbs-with-icons :links="[
                    ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                    ['label' => 'Loan Products', 'url' => route('loan-products.index'), 'icon' => 'bx bx-credit-card'],
                    ['label' => 'Product Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
                ]" />
                <div>
                    <!-- @can('deactivate loan product') -->
                    <button type="button" class="btn toggle-status-btn"
                        style="background-color: {{ $loanProduct->is_active ?? true ? '#fcd105' : '#23A036' }}; color: {{ $loanProduct->is_active ?? true ? 'black' : 'white' }};"
                        title="{{ $loanProduct->is_active ?? true ? 'Deactivate' : 'Activate' }} Product"
                        data-product-id="{{ Hashids::encode($loanProduct->id) }}"
                        data-product-name="{{ $loanProduct->name }}"
                        data-current-status="{{ $loanProduct->is_active ?? true ? 'active' : 'inactive' }}">
                        <i class="bx {{ $loanProduct->is_active ?? true ? 'bx-pause-circle' : 'bx-play-circle' }}"></i>
                        {{ $loanProduct->is_active ?? true ? 'Deactivate' : 'Activate' }}
                    </button>
                    <!-- @endcan -->

                    <!-- @can('edit loan product') -->
                    <a href="{{ route('loan-products.edit', Hashids::encode($loanProduct->id)) }}" class="btn" style="background-color: #fcd105; color: black;">
                        <i class="bx bx-edit"></i> Edit Product
                    </a>
                    <!-- @endcan -->

                    <!-- @can('view loan product') -->
                    <a href="{{ route('loan-products.index') }}" class="btn" style="background-color: black; color: white;">
                        <i class="bx bx-arrow-back"></i> Back to List
                    </a>
                    <!-- @endcan -->
                </div>
            </div>

            <!-- Product Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <h2 class="text-primary mb-2">{{ $loanProduct->name }}</h2>
                            <p class="text-muted mb-0">{{ ucfirst($loanProduct->product_type) }} Loan Product</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-percentage text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Interest Rate</h5>
                            <h4 class="text-primary mb-0">{{ number_format($loanProduct->minimum_interest_rate, 2) }}% - {{ number_format($loanProduct->maximum_interest_rate, 2) }}%</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-dollar-circle text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Principal Range</h5>
                            <h4 class="text-success mb-0">{{ number_format($loanProduct->minimum_principal, 0) }} - {{ number_format($loanProduct->maximum_principal, 0) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-time-five text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Period Range</h5>
                            <h4 class="text-warning mb-0">{{ $loanProduct->minimum_period }} - {{ $loanProduct->maximum_period }} {{ $loanProduct->interest_cycle }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-check-circle text-info" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Status</h5>
                            <h4 class="mb-0">
                                @if($loanProduct->is_active ?? true)
                                    <span class="badge" style="background-color: #23A036; color: white;">Active</span>
                                @else
                                    <span class="badge" style="background-color: black; color: white;">Inactive</span>
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Statistics Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="mb-3"><i class="bx bx-bar-chart-alt-2 me-2"></i>Loan Statistics</h5>
                </div>
            </div>

            <!-- Stats Cards Row -->
            <div class="row row-cols-1 row-cols-lg-4 mb-4">
                <div class="col">
                    <div class="card radius-10 bg-primary bg-gradient">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-white">Total Loans Disbursed</p>
                                    <h4 class="my-1 text-white">{{ number_format($stats['total_loans']) }}</h4>
                                </div>
                                <div class="text-white ms-auto font-35"><i class='bx bx-briefcase'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 bg-success bg-gradient">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-white">Total Amount Disbursed</p>
                                    <h4 class="my-1 text-white">TZS {{ number_format($stats['total_disbursed'], 2) }}</h4>
                                </div>
                                <div class="text-white ms-auto font-35"><i class='bx bx-money'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 bg-danger bg-gradient">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-white">Total Arrears</p>
                                    <h4 class="my-1 text-white">TZS {{ number_format($stats['total_arrears'], 2) }}</h4>
                                </div>
                                <div class="text-white ms-auto font-35"><i class='bx bx-error-circle'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 bg-warning bg-gradient">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-white">Arrears %</p>
                                    <h4 class="my-1 text-white">{{ $stats['total_disbursed'] > 0 ? number_format(($stats['total_arrears'] / $stats['total_disbursed']) * 100, 2) : 0 }}%</h4>
                                </div>
                                <div class="text-white ms-auto font-35"><i class='bx bx-line-chart'></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gender Statistics -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card radius-10">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class='bx bx-male'></i> Male Customers</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <p class="text-muted mb-1">Number of Loans</p>
                                    <h4>{{ number_format($stats['male_count']) }}</h4>
                                </div>
                                <div class="col-6">
                                    <p class="text-muted mb-1">Total Amount</p>
                                    <h4 class="text-success">TZS {{ number_format($stats['male_amount'], 2) }}</h4>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $stats['total_disbursed'] > 0 ? ($stats['male_amount'] / $stats['total_disbursed']) * 100 : 0 }}%">
                                    {{ $stats['total_disbursed'] > 0 ? number_format(($stats['male_amount'] / $stats['total_disbursed']) * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card radius-10">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class='bx bx-female'></i> Female Customers</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <p class="text-muted mb-1">Number of Loans</p>
                                    <h4>{{ number_format($stats['female_count']) }}</h4>
                                </div>
                                <div class="col-6">
                                    <p class="text-muted mb-1">Total Amount</p>
                                    <h4 class="text-success">TZS {{ number_format($stats['female_amount'], 2) }}</h4>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 20px;">
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: {{ $stats['total_disbursed'] > 0 ? ($stats['female_amount'] / $stats['total_disbursed']) * 100 : 0 }}%">
                                    {{ $stats['total_disbursed'] > 0 ? number_format(($stats['female_amount'] / $stats['total_disbursed']) * 100, 1) : 0 }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Cards -->
            <div class="row">
                <!-- Basic Information Card -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header" style="background-color: #006400; color: white;">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-info-circle me-2"></i>Basic Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Product Name</label>
                                    <p class="mb-0 fw-bold">{{ $loanProduct->name }}</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Product Type</label>
                                    <p class="mb-0">
                                        <span class="badge bg-primary">{{ ucfirst($loanProduct->product_type) }}</span>
                                    </p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Interest Cycle</label>
                                    <p class="mb-0 fw-bold">{{ ucfirst($loanProduct->interest_cycle) }}</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Interest Method</label>
                                    <p class="mb-0 fw-bold">{{ ucwords(str_replace('_', ' ', $loanProduct->interest_method)) }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Created</label>
                                    <p class="mb-0 fw-bold">{{ $loanProduct->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">Last Updated</label>
                                    <p class="mb-0 fw-bold">{{ $loanProduct->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information Card -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header" style="background-color: #23A036; color: white;">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-dollar-circle me-2"></i>Financial Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Min Interest Rate</label>
                                    <p class="mb-0 fw-bold text-success">{{ number_format($loanProduct->minimum_interest_rate, 2) }}%</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Max Interest Rate</label>
                                    <p class="mb-0 fw-bold text-success">{{ number_format($loanProduct->maximum_interest_rate, 2) }}%</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Min Principal</label>
                                    <p class="mb-0 fw-bold">{{ number_format($loanProduct->minimum_principal, 2) }}</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Max Principal</label>
                                    <p class="mb-0 fw-bold">{{ number_format($loanProduct->maximum_principal, 2) }}</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Min Period</label>
                                    <p class="mb-0 fw-bold">{{ $loanProduct->minimum_period }} {{ $loanProduct->interest_cycle == 'monthly' ? 'months' : ($loanProduct->interest_cycle == 'annually' ? 'years' : $loanProduct->interest_cycle) }}</p>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Max Period</label>
                                    <p class="mb-0 fw-bold">{{ $loanProduct->maximum_period }} {{ $loanProduct->interest_cycle == 'monthly' ? 'months' : ($loanProduct->interest_cycle == 'annually' ? 'years' : $loanProduct->interest_cycle) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Cards -->
            <div class="row">
                <!-- Top Up Configuration Card -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header" style="background-color: #fcd105; color: black;">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-up-arrow-circle me-2"></i>Top Up Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <label class="form-label text-muted small">Top Up Type</label>
                                    <p class="mb-0 fw-bold">{{ ucwords(str_replace('_', ' ', $loanProduct->top_up_type)) }}</p>
                                </div>
                                @if($loanProduct->top_up_type != 'none')
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label text-muted small">Top Up Value</label>
                                        <p class="mb-0 fw-bold text-warning">{{ number_format($loanProduct->top_up_type_value, 2) }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cash Collateral Configuration Card -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header" style="background-color: #23A036; color: white;">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-shield-check me-2"></i>Cash Collateral Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Has Cash Collateral</label>
                                    <p class="mb-0">
                                        @if($loanProduct->has_cash_collateral)
                                            <span class="badge" style="background-color: #23A036; color: white;">Yes</span>
                                        @else
                                            <span class="badge" style="background-color: black; color: white;">No</span>
                                        @endif
                                    </p>
                                </div>
                                @if($loanProduct->has_cash_collateral)
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label text-muted small">Collateral Type</label>
                                        <p class="mb-0 fw-bold">{{ $loanProduct->cash_collateral_type ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label text-muted small">Value Type</label>
                                        <p class="mb-0 fw-bold">{{ ucwords(str_replace('_', ' ', $loanProduct->cash_collateral_value_type)) }}</p>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label text-muted small">Collateral Value</label>
                                        <p class="mb-0 fw-bold text-info">{{ number_format($loanProduct->cash_collateral_value, 2) }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Configuration Card -->
            @if($loanProduct->has_approval_levels && $loanProduct->approval_levels)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header" style="background-color: #006400; color: white;">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-user-check me-2"></i>Approval Configuration
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label text-muted small">Approval Hierarchy</label>
                                        <div class="mb-2">
                                            @php
                                                $approvalRoles = is_array($loanProduct->approval_levels) 
                                                    ? $loanProduct->approval_levels 
                                                    : explode(',', $loanProduct->approval_levels);
                                                foreach ($approvalRoles as $index => $roleIdentifier) {
                                                    $roleIdentifier = trim($roleIdentifier);
                                                    if (is_numeric($roleIdentifier)) {
                                                        $role = \App\Models\Role::find($roleIdentifier);
                                                    } else {
                                                        $role = \App\Models\Role::where('name', $roleIdentifier)->first();
                                                    }
                                                    if ($role) {
                                                        echo '<span class="badge bg-primary me-2 mb-1">' . ($index + 1) . '. ' . ucwords(str_replace('-', ' ', $role->name)) . '</span>';
                                                    }
                                                }
                                            @endphp
                                        </div>
                                        <small class="text-muted">
                                            <i class="bx bx-info-circle"></i>
                                            Approval flow: First In, Last Out (First role approves first, last role approves last)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Chart Accounts Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header" style="background-color: darkgreen; color: white;">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-book-open me-2"></i>Chart Accounts Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card border h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-dollar-circle text-primary mb-2" style="font-size: 1.5rem;"></i>
                                            <h6 class="card-title">Principal Receivable</h6>
                                            <p class="mb-1 fw-bold">{{ $loanProduct->principalReceivableAccount->account_code ?? 'N/A' }}</p>
                                            <p class="text-muted mb-0 small">{{ $loanProduct->principalReceivableAccount->account_name ?? 'Not assigned' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card border h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-percentage text-success mb-2" style="font-size: 1.5rem;"></i>
                                            <h6 class="card-title">Interest Receivable</h6>
                                            <p class="mb-1 fw-bold">{{ $loanProduct->interestReceivableAccount->account_code ?? 'N/A' }}</p>
                                            <p class="text-muted mb-0 small">{{ $loanProduct->interestReceivableAccount->account_name ?? 'Not assigned' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card border h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-trending-up text-warning mb-2" style="font-size: 1.5rem;"></i>
                                            <h6 class="card-title">Interest Revenue</h6>
                                            <p class="mb-1 fw-bold">{{ $loanProduct->interestRevenueAccount->account_code ?? 'N/A' }}</p>
                                            <p class="text-muted mb-0 small">{{ $loanProduct->interestRevenueAccount->account_name ?? 'Not assigned' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repayment Configuration Card -->
            @if($loanProduct->repayment_order)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header" style="background-color: #006400; color: white;">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-sort-alt-2 me-2"></i>Repayment Configuration
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <label class="form-label text-muted small">Repayment Order</label>
                                        <div class="mb-2">
                                            @php
                                                $orderArray = is_array($loanProduct->repayment_order) 
                                                    ? $loanProduct->repayment_order 
                                                    : explode(',', $loanProduct->repayment_order);
                                                $componentLabels = [
                                                    'principal' => 'Principal',
                                                    'interest' => 'Interest',
                                                    'fees' => 'Fees',
                                                    'penalties' => 'Penalties'
                                                ];
                                                $componentColors = [
                                                    'principal' => 'primary',
                                                    'interest' => 'success',
                                                    'fees' => 'warning',
                                                    'penalties' => 'danger'
                                                ];
                                            @endphp
                                            @foreach($orderArray as $index => $component)
                                                @php
                                                    $component = trim($component);
                                                    $label = $componentLabels[$component] ?? ucfirst($component);
                                                    $color = $componentColors[$component] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }} me-2 mb-1">{{ $index + 1 }}. {{ $label }}</span>
                                            @endforeach
                                        </div>
                                        <small class="text-muted">
                                            <i class="bx bx-info-circle"></i>
                                            Payment allocation order: First component receives payment priority
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Fees and Penalties Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header" style="background-color: #fcd105; color: black;">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-money me-2"></i>Fees and Penalties Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Fees -->
                                <div class="col-md-6">
                                    <h6 class="text-success mb-3">
                                        <i class="bx bx-check-circle me-1"></i>Default Fees
                                    </h6>
                                    @if($loanProduct->fees_ids && count($loanProduct->fees_ids) > 0)
                                        <div class="list-group">
                                            @foreach($loanProduct->fees_ids as $feeId)
                                                @php
                                                    $fee = \App\Models\Fee::find($feeId);
                                                @endphp
                                                @if($fee)
                                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 bg-light">
                                                        <div>
                                                            <strong>{{ $fee->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $fee->fee_type }}</small>
                                                        </div>
                                                        <span class="badge" style="background-color: #23A036; color: white;">{{ $fee->status }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">No fees configured</p>
                                    @endif
                                </div>

                                <!-- Penalties -->
                                <div class="col-md-6">
                                    <h6 class="text-warning mb-3">
                                        <i class="bx bx-error-circle me-1"></i>Default Penalties
                                    </h6>
                                    @if($loanProduct->penalty_ids && count($loanProduct->penalty_ids) > 0)
                                        <div class="list-group">
                                            @foreach($loanProduct->penalty_ids as $penaltyId)
                                                @php
                                                    $penalty = \App\Models\Penalty::find($penaltyId);
                                                @endphp
                                                @if($penalty)
                                                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 bg-light">
                                                        <div>
                                                            <strong>{{ $penalty->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $penalty->penalty_type }}</small>
                                                        </div>
                                                        <span class="badge" style="background-color: #fcd105; color: black;">{{ $penalty->status }}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">No penalties configured</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
        }
        
        .card-header {
            border-bottom: none;
            font-weight: 600;
        }
        
        .bg-purple {
            background-color: #6f42c1 !important;
        }
        
        .form-label {
            font-weight: 500;
        }
        
        .list-group-item {
            border-radius: 0.5rem !important;
            margin-bottom: 0.5rem;
        }
        
        .badge {
            font-size: 0.75em;
            font-weight: 500;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        .fw-bold {
            font-weight: 600 !important;
        }
    </style>
@endpush

@push('scripts')
<script>
    // Toggle status confirmation with SweetAlert2
    $('.toggle-status-btn').on('click', function (e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        var productName = $(this).data('product-name');
        var currentStatus = $(this).data('current-status');
        var newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        var actionText = currentStatus === 'active' ? 'deactivate' : 'activate';

        Swal.fire({
            title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Loan Product?`,
            text: `Are you sure you want to ${actionText} "${productName}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: currentStatus === 'active' ? '#ffc107' : '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText} it!`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create and submit form for status toggle
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = `/loan-products/${productId}/toggle-status`;
                
                var csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                var methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'PATCH';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
</script>
@endpush