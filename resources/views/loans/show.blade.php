@extends('layouts.main')

@section('title', 'Loan Details')

@section('content')
    <div class="page-wrapper">
        <input type="hidden" id="loan_id" value="{{ $loan->id }}">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.list'), 'icon' => 'bx bx-wallet'],
            ['label' => 'Loan Details', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h4 class="fw-bold text-dark mb-0">Loan Details for {{ $loan->customer->name }}</h4>
                    <div class="d-flex gap-2" style="margin-left: 16px;">
                        <a href="{{ route('loans.writeoff', Vinkla\Hashids\Facades\Hashids::encode($loan->id)) }}"
                            class="btn btn-danger">Write Off Loans</a>

                        @if($loan->isEligibleForTopUp())
                            <button type="button" class="btn btn-success" onclick="showTopUpModal()">
                                <i class="bx bx-plus-circle me-2"></i>Apply for Top-Up
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary" disabled title="Loan not eligible for top-up">
                                <i class="bx bx-plus-circle me-2"></i>Top-Up Not Available
                            </button>
                        @endif
                        <a href="{{ route('loans.fees_receipt', Vinkla\Hashids\Facades\Hashids::encode($loan->id)) }}"
                            class="btn btn-success"><i class="bx bx-plus-circle me-2"></i> Loan Fees Receipt</a>

                        @if($loan->status === 'active' || $loan->status === 'disbursed')
                            <button type="button" class="btn btn-warning" onclick="showSettleLoanModal()">
                                <i class="bx bx-check-circle me-2"></i>Settle Loan
                            </button>
                        @endif

                        @if($loan->status === 'active')
                            <a href="{{ route('loans.export-details', Vinkla\Hashids\Facades\Hashids::encode($loan->id)) }}" 
                               class="btn btn-info">
                                <i class="bx bx-download me-2"></i>Export Loan Details
                            </a>
                        @endif

                    </div>
                </div>
                <div class="d-flex gap-2">
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        @php
                            $status = strtolower($loan->status);
                            $badgeClass = match ($status) {
                                'pending' => 'bg-secondary',
                                'checked' => 'bg-info',
                                'approved' => 'bg-success',
                                'active' => 'bg-primary',
                                'disbursed' => 'bg-primary',
                                'completed' => 'bg-success',
                                'defaulted' => 'bg-danger',
                                'rejected' => 'bg-danger',
                                'cancelled' => 'bg-dark',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }} fs-6">{{ ucfirst($loan->status) }}</span>
                    </div>
                    <div class="text-end">
                        @php
                            $totalPaid = $loan->repayments?->sum(function ($r) {
                                return ($r->principal + $r->interest);
                            }) ?? 0;
                            $progress = $loan->amount_total > 0 ? min(100, round(($totalPaid / $loan->amount_total) * 100)) : 0;
                            $progressBarClass = match (true) {
                                $progress === 100 => 'bg-success',
                                $progress >= 75 => 'bg-primary',
                                $progress >= 50 => 'bg-info',
                                $progress >= 25 => 'bg-warning',
                                default => 'bg-danger',
                            };
                        @endphp
                        <p class="mb-1 fw-bold text-dark">
                            {{ $progress }}% Complete
                            @if($progress >= 100)
                                <span class="badge bg-success ms-2">Fully Paid</span>
                            @elseif($progress == 0)
                                <span class="badge bg-danger ms-2">No Repayments</span>
                            @else
                                <span class="badge bg-warning text-dark ms-2">Partially Paid</span>
                            @endif
                        </p>
                        <div class="progress" style="width: 250px; height: 10px;">
                            <div class="progress-bar {{ $progressBarClass }}" role="progressbar"
                                style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0"
                                aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Arrears Information Card -->
            @if($loan->is_in_arrears)
                <div class="card shadow-sm border-0 mb-4 border-start border-danger border-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-danger-subtle text-danger rounded-circle p-3">
                                            <i class="bx bx-error-circle fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 text-danger fw-bold">Amount in Arrears</h6>
                                        <h4 class="mb-0 text-danger fw-bold">TZS {{ number_format($loan->arrears_amount, 2) }}
                                        </h4>
                                        <small class="text-muted">Total overdue amount</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-warning-subtle text-warning rounded-circle p-3">
                                            <i class="bx bx-time-five fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 text-warning fw-bold">Days in Arrears</h6>
                                        <h4 class="mb-0 text-warning fw-bold">{{ round($loan->days_in_arrears) }} days</h4>
                                        <small class="text-muted">Since first overdue payment</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($loan->days_in_arrears > 30)
                            <div class="alert alert-danger mt-3 mb-0">
                                <i class="bx bx-error-circle me-2"></i>
                                <strong>Warning:</strong> This loan has been in arrears for more than 30 days. Consider taking
                                appropriate action.
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card shadow-sm border-0 mb-4 border-start border-success border-4">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success-subtle text-success rounded-circle p-2">
                                    <i class="bx bx-check-circle fs-5"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 text-success fw-bold">Loan is Current</h6>
                                <small class="text-muted">No overdue payments</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Top-Up Information Card -->
            @if($loan->product && $loan->product->top_up_type && $loan->isEligibleForTopUp())
                <div class="card shadow-sm border-0 mb-4 border-start border-info border-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-info-subtle text-info rounded-circle p-3">
                                            <i class="bx bx-plus-circle fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 text-info fw-bold">Top-Up Eligibility</h6>
                                        @if($loan->isEligibleForTopUp())
                                            <span class="badge bg-success">Eligible</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="bg-warning-subtle text-warning rounded-circle p-3">
                                            <i class="bx bx-calculator fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1 text-warning fw-bold">Current Loan Balance</h6>
                                        <h4 class="mb-0 text-warning fw-bold">TZS
                                            {{ number_format($loan->getCalculatedTopUpAmount(), 2) }}
                                        </h4>
                                        <small class="text-muted">Amount to close current loan</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($loan->isEligibleForTopUp())
                            <div class="alert alert-success mt-3 mb-0">
                                <i class="bx bx-check-circle me-2"></i>
                                <strong>Great!</strong> This loan is eligible for top-up. The customer can apply for a new loan
                                amount that must be greater than the current balance of TZS
                                {{ number_format($loan->getCalculatedTopUpAmount(), 2) }}. The current loan will be closed using
                                this balance amount.
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <ul class="nav nav-tabs nav-tabs-style-2 mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active d-flex align-items-center" data-bs-toggle="tab" href="#loan_detail"
                        role="tab">
                        <i class="bx bx-info-circle me-2 font-18"></i>Details
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#schedule" role="tab">
                        <i class="bx bx-calendar me-2 font-18"></i>Schedule
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#guarantors" role="tab">
                        <i class="bx bx-group me-2 font-18"></i>Guarantors
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#documents" role="tab">
                        <i class="bx bx-file me-2 font-18"></i>Documents
                    </a>
                </li>

                <li class="nav-item" role="presentation">
                    <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#collaterals" role="tab">
                        <i class="bx bx-shield me-2 font-18"></i>Collaterals
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#repayments" role="tab">
                        <i class="bx bx-credit-card me-2 font-18"></i>Repayments
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link d-flex align-items-center" data-bs-toggle="tab" href="#approval_history" role="tab">
                        <i class="bx bx-history me-2 font-18"></i>Approval History
                    </a>
                </li>
            </ul>

            <div class="tab-content py-3">
                <div class="tab-pane fade show active" id="loan_detail" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary border-0 py-3">
                            <h6 class="mb-0 text-white fw-bold"><i class="bx bx-info-circle me-2"></i> LOAN INFORMATION</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped" width="50%">
                                    <tbody>
                                        <!-- Customer Information -->
                                        <tr class="table-secondary">
                                            <td colspan="2" class="fw-bold text-dark py-3 ps-4">
                                                <i class="bx bx-user me-2 text-primary"></i>CUSTOMER INFORMATION
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4" style="width: 40%;">Customer Name</td>
                                            <td class="text-dark">{{ $loan->customer->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Phone Number</td>
                                            <td class="text-dark">{{ $loan->customer->phone1 ?? 'N/A' }}</td>
                                        </tr>

                                        <!-- Loan Details -->
                                        <tr class="table-secondary">
                                            <td colspan="2" class="fw-bold text-dark py-3 ps-4">
                                                <i class="bx bx-package me-2 text-primary"></i>LOAN DETAILS
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Product</td>
                                            <td class="text-dark">{{ $loan->product->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Branch</td>
                                            <td class="text-dark">{{ $loan->branch->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Group</td>
                                            <td class="text-dark">{{ $loan->group->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Bank Account</td>
                                            <td class="text-dark">{{ $loan->bankAccount->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Sector</td>
                                            <td class="text-dark">{{ $loan->sector ?? 'N/A' }}</td>
                                        </tr>

                                        <!-- Financial Information -->
                                        <tr class="table-secondary">
                                            <td colspan="2" class="fw-bold text-dark py-3 ps-4">
                                                <i class="bx bx-money me-2 text-primary"></i>FINANCIAL INFORMATION
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Principal Amount</td>
                                            <td class="text-dark fw-bold">TZS {{ number_format($loan->amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Interest Amount</td>
                                            <td class="text-dark">TZS {{ number_format($loan->interest_amount, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Total Repayable</td>
                                            <td class="text-success fw-bold">TZS {{ number_format($loan->amount_total, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Interest Rate</td>
                                            <td class="text-dark">{{ ($loan->interest ?? 'N/A') }}%</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Interest Method</td>
                                            <td class="text-dark">{{ $loan->product->interest_method ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Loan Period</td>
                                            <td class="text-dark">{{ $loan->period }} {{ $loan->getPeriodUnit() }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">{{ $loan->getInstallmentUnit() }}
                                                Installment</td>
                                            <td class="text-dark">TZS
                                                {{ number_format($loan->amount_total / $loan->period, 2) }}
                                            </td>
                                        </tr>

                                        <!-- Payment Information -->
                                        <tr class="table-secondary">
                                            <td colspan="2" class="fw-bold text-dark py-3 ps-4">
                                                <i class="bx bx-credit-card me-2 text-primary"></i>PAYMENT INFORMATION
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Total Repayments</td>
                                            <td class="text-info fw-bold">TZS
                                                {{ number_format($loan->repayments?->sum(function ($r) {
        return ($r->principal + $r->interest); }) ?? 0, 2) }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Principal Paid</td>
                                            <td class="text-success fw-bold">TZS
                                                {{ number_format($loan->total_principal_paid, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Interest Paid</td>
                                            <td class="text-info fw-bold">TZS
                                                {{ number_format($loan->total_interest_paid, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Outstanding Balance</td>
                                            <td class="text-danger fw-bold">TZS
                                                {{ number_format($loan->amount_total - ($loan->repayments?->sum(function ($r) {
        return ($r->principal + $r->interest); }) ?? 0), 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Settle Amount</td>
                                            <td class="text-warning fw-bold">TZS
                                                {{ number_format($loan->total_amount_to_settle, 2) }}
                                                <br><small class="text-muted">Pays current interest + all remaining
                                                    principal</small>
                                            </td>
                                        </tr>

                                        <!-- Important Dates -->
                                        <tr class="table-secondary">
                                            <td colspan="2" class="fw-bold text-dark py-3 ps-4">
                                                <i class="bx bx-calendar me-2 text-primary"></i>IMPORTANT DATES
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Applied On</td>
                                            <td class="text-dark">
                                                {{ \Carbon\Carbon::parse($loan->date_applied)->format('F d, Y') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Disbursed On</td>
                                            <td class="text-dark">
                                                {{ $loan->disbursed_on ? \Carbon\Carbon::parse($loan->disbursed_on)->format('F d, Y') : 'Not yet disbursed' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">First Repayment Date</td>
                                            <td class="text-dark">
                                                {{ $loan->first_repayment_date ? \Carbon\Carbon::parse($loan->first_repayment_date)->format('F d, Y') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Last Repayment Date</td>
                                            <td class="text-dark">
                                                {{ $loan->last_repayment_date ? \Carbon\Carbon::parse($loan->last_repayment_date)->format('F d, Y') : 'N/A' }}
                                            </td>
                                        </tr>

                                        <!-- Status Information -->
                                        <tr class="table-secondary">
                                            <td colspan="2" class="fw-bold text-dark py-3 ps-4">
                                                <i class="bx bx-check-circle me-2 text-primary"></i>STATUS INFORMATION
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Current Status</td>
                                            <td>
                                                @php
                                                    $status = strtolower($loan->status);
                                                    $badgeClass = match ($status) {
                                                        'pending' => 'bg-secondary',
                                                        'checked' => 'bg-info',
                                                        'approved' => 'bg-success',
                                                        'active' => 'bg-primary',
                                                        'disbursed' => 'bg-primary',
                                                        'completed' => 'bg-success',
                                                        'defaulted' => 'bg-danger',
                                                        'rejected' => 'bg-danger',
                                                        'cancelled' => 'bg-dark',
                                                        default => 'bg-secondary',
                                                    };
                                                @endphp
                                                <span
                                                    class="badge {{ $badgeClass }} fs-6">{{ ucfirst($loan->status) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted ps-4">Payment Progress</td>
                                            <td>
                                                @php
                                                    $totalPaid = $loan->repayments?->sum(function ($r) {
                                                        return ($r->principal + $r->interest);
                                                    }) ?? 0;
                                                    $progress = $loan->amount_total > 0 ? min(100, round(($totalPaid / $loan->amount_total) * 100)) : 0;
                                                    $progressBarClass = match (true) {
                                                        $progress === 100 => 'bg-success',
                                                        $progress >= 75 => 'bg-primary',
                                                        $progress >= 50 => 'bg-info',
                                                        $progress >= 25 => 'bg-warning',
                                                        default => 'bg-danger',
                                                    };
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-3" style="width: 200px; height: 8px;">
                                                        <div class="progress-bar {{ $progressBarClass }}" role="progressbar"
                                                            style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}"
                                                            aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="fw-bold">{{ $progress }}%</span>
                                                    @if($progress >= 100)
                                                        <span class="badge bg-success ms-2">Fully Paid</span>
                                                    @elseif($progress == 0)
                                                        <span class="badge bg-danger ms-2">No Repayments</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark ms-2">Partially Paid</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Approval Actions -->
                    @if($loan->status !== 'active')
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bx bx-cog me-2"></i>LOAN APPROVAL ACTIONS</h6>
                        </div>
                        <div class="card-body">
                            @php
                                $approvalRoles = $loan->getApprovalRoles();
                                $nextLevel = $loan->getNextApprovalLevel();
                                $nextAction = $loan->getNextApprovalAction();
                                $nextRoleName = $nextLevel ? $loan->getApprovalLevelName($nextLevel) : null;
                            @endphp

                            @if($nextLevel && $nextAction)
                                <div class="row g-3">
                                    @if(auth()->user() && $loan->canBeApprovedByUser(auth()->user()) && !$loan->hasUserApproved(auth()->user()))
                                        <div class="col-md-6 col-lg-4">
                                            @can('approve loan')
                                                <button type="button"
                                                    class="btn btn-primary w-100 d-flex align-items-center justify-content-center"
                                                    onclick="{{ $nextAction === 'check' ? 'checkLoan' : ($nextAction === 'authorize' ? 'authorizeLoan' : ($nextAction === 'disburse' ? 'disburseLoan' : 'approveLoan')) }}('{{ Hashids::encode($loan->id) }}')">
                                                    <i class="bx bx-check-circle me-2"></i>
                                                    <div class="text-start">
                                                        <div class="fw-bold">{{ ucfirst($nextAction) }} Loan</div>
                                                        <small class="d-block">{{ $nextRoleName }} (Level {{ $nextLevel }})</small>
                                                    </div>
                                                </button>
                                            @endcan
                                        </div>
                                    @endif

                                    @if($loan->canBeRejected() && auth()->user() && $loan->canBeApprovedByUser(auth()->user()) && !$loan->hasUserApproved(auth()->user()))
                                        <div class="col-md-6 col-lg-4">
                                            @can('reject loan')
                                                <button type="button"
                                                    class="btn btn-danger w-100 d-flex align-items-center justify-content-center"
                                                    onclick="rejectLoan('{{ Hashids::encode($loan->id) }}')">
                                                    <i class="bx bx-x-circle me-2"></i>
                                                    <div class="text-start">
                                                        <div class="fw-bold">Reject Loan</div>
                                                        <small class="d-block">Decline Application</small>
                                                    </div>
                                                </button>
                                            @endcan
                                        </div>
                                    @endif
                                </div>

                                @if(!auth()->user() || !$loan->canBeApprovedByUser(auth()->user()))
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        @if(!auth()->user())
                                            Please log in to perform approval actions.
                                        @elseif(!$loan->canBeApprovedByUser(auth()->user()))
                                            You don't have permission to approve this loan. Required role: {{ $nextRoleName }}
                                        @endif
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong>Approval Flow:</strong>
                                        @foreach($approvalRoles as $index => $roleId)
                                            @php
                                                $roleName = $loan->getApprovalLevelName($index + 1);
                                                $isCurrent = ($index + 1) === $nextLevel;
                                                $isCompleted = ($index + 1) < $nextLevel;
                                            @endphp
                                            <span
                                                class="badge {{ $isCurrent ? 'bg-primary' : ($isCompleted ? 'bg-success' : 'bg-secondary') }} me-1">
                                                {{ $roleName }}
                                            </span>
                                            @if($index < count($approvalRoles) - 1)
                                                <i class="bx bx-chevron-right text-muted"></i>
                                            @endif
                                        @endforeach
                                    </small>
                                </div>
                            @elseif($loan->status === 'active')
                                <div class="row g-3">
                                    @can('default loan')
                                        <div class="col-md-6 col-lg-4">
                                            <button type="button"
                                                class="btn btn-dark w-100 d-flex align-items-center justify-content-center"
                                                onclick="defaultLoan('{{ Hashids::encode($loan->id) }}')">
                                                <i class="bx bx-error-circle me-2"></i>
                                                <div class="text-start">
                                                    <div class="fw-bold">Mark as Defaulted</div>
                                                    <small class="d-block">Default Loan</small>
                                                </div>
                                            </button>
                                        </div>
                                    @endcan
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bx bx-info-circle fs-1 text-muted mb-3"></i>
                                    <h6 class="text-muted">No Actions Available</h6>
                                    <p class="text-muted">
                                        @if(empty($approvalRoles))
                                            This loan product does not require approval levels.
                                        @else
                                            This loan status does not require any approval actions.
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="schedule" role="tabpanel">
                    @if($loan->schedule->count())
                        <div class="card radius-10">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bx bx-history me-2"></i>LOAN SCHEDULE LIST</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive w-100" style="overflow-x: auto;">
                                    <table class="table table-bordered nowrap w-100 table-striped">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Due Date</th>
                                                <th>Principal</th>
                                                <th>Interest</th>
                                                <th>Penalty Amount</th>
                                                <th>Fee Amount</th>
                                                <th class="text-end pe-4">Total Due</th>
                                                <th class="text-end pe-4">Paid Amount</th>
                                                <th class="text-end pe-4">Remaining</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($loan->schedule->sortBy('due_date') as $index => $item)

                                                @php
                                                    $totalDue = $item->total_due;
                                                    $paidAmount = $item->paid_amount;
                                                    $remainingAmount = $item->remaining_amount;
                                                    $isFullyPaid = $item->fullPrincipalPaid();
                                                    $paymentPercentage = $item->payment_percentage;
                                                    $completed = $loan->status === 'completed';
                                                    $penaltyPaid = $item->PenaltyPaid();
                                                    // $penaltAmount = $item->penalty_amount;
                                                    // dd($penaltyPaid, $penaltAmount);

                                                @endphp
                                                <tr
                                                    class="{{ $isFullyPaid ? 'table-success' : ($paidAmount > 0 ? 'table-warning' : '') }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td class="ps-4">{{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}
                                                    </td>
                                                    <td>{{ number_format($item->principal, 2) }}</td>
                                                    <td>{{ number_format($item->interest, 2) }}</td>
                                                    <td>{{ number_format($item->penalty_amount, 2) }}</td>
                                                    <td>{{ number_format($item->fee_amount, 2) }}</td>
                                                    <td class="text-end pe-4 fw-bold">{{ number_format($totalDue, 2) }}</td>
                                                    <td class="text-end pe-4 text-success">{{ number_format($paidAmount, 2) }}</td>
                                                    <td class="text-end pe-4 text-danger">{{ number_format($remainingAmount, 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if($isFullyPaid)
                                                            <span class="badge bg-success">Paid</span>
                                                        @elseif($paidAmount > 0)
                                                            <span class="badge bg-warning text-dark">{{ $paymentPercentage }}%</span>
                                                        @else
                                                            <span class="badge bg-danger">Unpaid</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($isFullyPaid || $completed)
                                                            <button type="button" class="btn btn-sm btn-success" disabled>
                                                                <i class="bx bx-check-circle me-1"></i>Paid
                                                            </button>
                                                        @else
                                                            <button type="button" class="btn btn-sm btn-primary"
                                                                onclick="repayScheduleItem('{{ $item->id }}', '{{ number_format($remainingAmount, 2) }}', '{{ \Carbon\Carbon::parse($item->due_date)->format('M d, Y') }}', '{{ number_format($item->principal, 2) }}', '{{ number_format($item->interest, 2) }}', '{{ number_format($item->penalty_amount, 2) }}', '{{ number_format($item->fee_amount, 2) }}')">
                                                                <i class="bx bx-credit-card me-1"></i>Repay
                                                            </button>
                                                        @endif
                                                        @if($item->isPenaltyRemovalAllowed())
                                                            <button type="button" class="btn btn-sm btn-warning ms-1"
                                                                onclick="removePenalty('{{ $item->id }}', '{{ number_format($item->penalty_amount, 2) }}')">
                                                                <i class="bx bx-x-circle me-1"></i>Remove Penalty
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card card-body text-center p-5">
                            <h4 class="text-muted">No repayment schedule available.</h4>
                            <p class="text-secondary">A schedule will be generated once the loan is approved.</p>
                        </div>
                    @endif
                </div>


                <div class="tab-pane fade" id="guarantors" role="tabpanel">

                    @can('add guarantor')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0 text-dark">Guarantors</h5>
                            <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                                data-bs-target="#addGuarantorModal">
                                <i class="bx bx-user-plus me-2 font-18"></i>Add Guarantor
                            </button>
                        </div>
                    @endcan

                    @if($loan->guarantors && $loan->guarantors->count())
                        <div class="card radius-10">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bx bx-history me-2"></i>GUARANTOR LIST</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th class="text-end pe-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($loan->guarantors as $index => $guarantor)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $guarantor->name }}</td>
                                                    <td>{{ $guarantor->phone1 }}</td>
                                                    @can('remove guarantor')
                                                        <td class="text-end pe-4">
                                                            <form
                                                                action="{{ route('loans.removeGuarantor', [$loan->id, $guarantor->id]) }}"
                                                                method="POST" class="d-inline form-delete">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    data-name="{{$guarantor->name}}">Remove</button>
                                                            </form>
                                                        </td>
                                                    @endcan
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card card-body text-center p-5">
                            <h4 class="text-muted">No guarantors assigned to this loan.</h4>
                            <p class="text-secondary">Click the button above to add a guarantor.</p>
                        </div>
                    @endif

                </div>

                <div class="tab-pane fade" id="documents" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 text-dark">Documents</h5>
                        @can('manage loan documents')
                        <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                            data-bs-target="#uploadDocumentModal">
                            <i class="bx bx-plus me-2"></i>Add Document
                        </button>
                        @endcan
                    </div>

                    @if($loan->loanFiles->count())
                        <div class="card radius-10">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bx bx-history me-2"></i>DOCUMENT LIST</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Document Name</th>
                                                <th class="text-end pe-4">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($loan->loanFiles as $index => $doc)
                                                <tr>
                                                    <th scope="row" class="ps-4">{{ $index + 1 }}</th>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bx bx-file me-2 text-primary"></i>
                                                            <span class="fw-medium">{{ $doc->fileType->name }}</span>
                                                        </div>
                                                    </td>
                                                    @can('view loan documents')
                                                        <td class="text-end pe-4">
                                                            <div class="btn-group" role="group">
                                                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                                                    class="btn btn-sm btn-outline-primary" title="View Document">
                                                                    <i class="bx bx-show"></i>
                                                                </a>
                                                                <a href="{{ asset('storage/' . $doc->file_path) }}" download
                                                                    class="btn btn-sm btn-outline-success" title="Download Document">
                                                                    <i class="bx bx-download"></i>
                                                                </a>
                                                                @can('manage loan documents')
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger delete-document-btn"
                                                                        data-document-id="{{ $doc->id }}"
                                                                        data-document-name="{{ $doc->fileType->name }}"
                                                                        title="Delete Document">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                @endcan
                                                            </div>
                                                        </td>
                                                    @endcan
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card card-body text-center p-5">
                            <h4 class="text-muted">No documents uploaded yet.</h4>
                            <p class="text-secondary">Click the button above to add the first document.</p>
                        </div>
                    @endif
                </div>

                @can('manage loan documents')
                <!-- Upload Document Modal -->
                <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="uploadDocumentModalLabel"><i class="bx bx-upload me-2"></i>Upload Loan Documents</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="uploadDocumentForm" action="{{ route('loan-documents.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="loan_id" value="{{ $loan->id }}">
                                <div class="modal-body">
                                    <div id="documentUploads">
                                        <div class="document-upload-row mb-3 p-3 border rounded">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Document Type</label>
                                                    <select class="form-select document-type" name="filetypes[]" required>
                                                        <option value="">-- Select Document Type --</option>
                                                        @foreach($filetypes as $file)
                                                            <option value="{{ $file->id }}">{{ $file->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Choose File</label>
                                                    <div class="input-group">
                                                        <input type="file" class="form-control document-file" name="files[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                                                        <button type="button" class="btn btn-outline-danger remove-document-btn">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" id="addAnotherDocument">
                                            <i class="bx bx-plus me-1"></i>Add Another
                                        </button>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-upload me-1"></i>Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endcan

                <div class="tab-pane fade" id="repayments" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 text-dark">Repayments</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-danger d-flex align-items-center"
                                id="bulkDeleteRepaymentsBtn" disabled>
                                <i class="bx bx-trash me-2 font-18"></i>Bulk Delete
                            </button>
                        </div>
                    </div>

                    @if($loan->repayments && $loan->repayments->count())
                        <div class="card radius-10">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bx bx-history me-2"></i>REPAYMENT LIST</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="text-center" style="width:32px;"><input type="checkbox"
                                                        id="select_all_repayments"></th>
                                                <th>#</th>
                                                <th>Payment Date</th>
                                                <th>Due Date</th>
                                                <th>Principal</th>
                                                <th>Interest</th>
                                                <th>Penalty</th>
                                                <th>Fee</th>
                                                <th class="text-end pe-4">Total Paid</th>
                                                <th>Bank Account</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($loan->repayments->sortByDesc('payment_date') as $index => $repayment)
                                                <tr>
                                                    <td class="text-center"><input type="checkbox" class="repayment-select"
                                                            value="{{ $repayment->id }}"></td>
                                                    <th scope="row" class="ps-4">{{ $index + 1 }}</th>
                                                    <td>{{ \Carbon\Carbon::parse($repayment->payment_date)->format('M d, Y') }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($repayment->due_date)->format('M d, Y') }}</td>
                                                    <td class="text-success">{{ number_format($repayment->principal, 2) }}</td>
                                                    <td class="text-info">{{ number_format($repayment->interest, 2) }}</td>
                                                    <td class="text-danger">{{ number_format($repayment->penalt_amount, 2) }}</td>
                                                    <td class="text-warning">{{ number_format($repayment->fee_amount, 2) }}</td>
                                                    <td class="text-end pe-4 fw-bold">
                                                        {{ number_format($repayment->amount_paid, 2) }}
                                                    </td>
                                                    <td>{{ $repayment->chartAccount->account_name ?? 'N/A' }}</td>
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                                onclick="printReceipt({{ $repayment->id }})" title="Print Receipt">
                                                                Print
                                                            </button>
                                                            <!-- <button type="button" class="btn btn-sm btn-outline-secondary"
                                                                                                                                                                                                                                                                                                                                                                                        onclick="editRepayment({{ $repayment->id }})"
                                                                                                                                                                                                                                                                                                                                                                                        title="Edit Repayment">
                                                                                                                                                                                                                                                                                                                                                                                        Edit
                                                                                                                                                                                                                                                                                                                                                                                    </button> -->
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="deleteRepayment({{ $repayment->id }})"
                                                                title="Delete Repayment">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card card-body text-center p-5">
                            <h4 class="text-muted">No repayments recorded yet.</h4>
                            <p class="text-secondary">Click the button above to add the first repayment.</p>
                        </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="collaterals" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 text-dark">Collaterals</h5>
                        <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal"
                            data-bs-target="#addCollateralModal">
                            <i class="bx bx-plus me-2 font-18"></i>Add Collateral
                        </button>
                    </div>

                    @if($loan->collaterals && $loan->collaterals->count())
                        <div class="row">
                            @foreach($loan->collaterals as $collateral)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 shadow-sm border-0">
                                        <div
                                            class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $collateral->title }}</h6>
                                                <small class="text-muted">{{ ucfirst($collateral->type) }}</small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="viewCollateral({{ $collateral->id }})">
                                                            <i class="bx bx-show me-2"></i>View Details
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="editCollateral({{ $collateral->id }})">
                                                            <i class="bx bx-edit me-2"></i>Edit
                                                        </a></li>
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="changeCollateralStatus({{ $collateral->id }})">
                                                            <i class="bx bx-refresh me-2"></i>Change Status
                                                        </a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><a class="dropdown-item text-danger" href="#"
                                                            onclick="deleteCollateral({{ $collateral->id }})">
                                                            <i class="bx bx-trash me-2"></i>Delete
                                                        </a></li>
                                                </ul>
                                            </div>
                                        </div>

                                        @if($collateral->images && count($collateral->images) > 0)
                                            <div class="card-img-top position-relative" style="height: 200px; overflow: hidden;">
                                                <img src="{{ asset('storage/' . $collateral->images[0]) }}" alt="Collateral Image"
                                                    class="w-100 h-100" style="object-fit: cover;">
                                                @if(count($collateral->images) > 1)
                                                    <div class="position-absolute top-0 end-0 m-2">
                                                        <span class="badge bg-dark">+{{ count($collateral->images) - 1 }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                                style="height: 200px;">
                                                <i class="bx bx-image-alt fs-1 text-muted"></i>
                                            </div>
                                        @endif

                                        <div class="card-body">
                                            <p class="card-text text-muted small mb-2">
                                                {{ Str::limit($collateral->description, 100) }}
                                            </p>

                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <small class="text-muted">Estimated Value</small>
                                                    <p class="fw-bold text-success mb-0">TZS
                                                        {{ number_format($collateral->estimated_value, 2) }}
                                                    </p>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Status</small>
                                                    <p class="mb-0">
                                                        <span class="badge {{ $collateral->getStatusBadgeClass() }}">
                                                            {{ ucfirst($collateral->status) }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            @if($collateral->condition)
                                                <div class="row mb-2">
                                                    <div class="col-12">
                                                        <small class="text-muted">Condition</small>
                                                        <p class="mb-0">
                                                            <span class="badge {{ $collateral->getConditionBadgeClass() }}">
                                                                {{ ucfirst($collateral->condition) }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($collateral->location)
                                                <div class="mb-2">
                                                    <small class="text-muted d-block">Location</small>
                                                    <small class="text-dark">{{ $collateral->location }}</small>
                                                </div>
                                            @endif

                                            @if($collateral->documents && count($collateral->documents) > 0)
                                                <div class="mb-2">
                                                    <small class="text-muted d-block">Documents</small>
                                                    <small class="text-primary">
                                                        <i class="bx bx-file me-1"></i>{{ count($collateral->documents) }} file(s)
                                                    </small>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="card-footer bg-transparent border-0">
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-sm btn-outline-primary w-100"
                                                        onclick="viewCollateral({{ $collateral->id }})">
                                                        <i class="bx bx-show me-1"></i>View
                                                    </button>
                                                </div>
                                                <div class="col-6">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                                        onclick="changeCollateralStatus({{ $collateral->id }})">
                                                        <i class="bx bx-refresh me-1"></i>Status
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="card card-body text-center p-5">
                            <i class="bx bx-shield fs-1 text-muted mb-3"></i>
                            <h4 class="text-muted">No collaterals assigned to this loan</h4>
                            <p class="text-secondary">Collaterals provide security for the loan. Click the button above to add
                                the first collateral.</p>
                        </div>
                    @endif
                </div>

                <div class="tab-pane fade" id="approval_history" role="tabpanel">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bx bx-history me-2"></i>APPROVAL HISTORY</h6>
                        </div>
                        <div class="card-body">
                            @if($loan->approvals && $loan->approvals->count())
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Action</th>
                                                <th>Level</th>
                                                <th>User</th>
                                                <th>Comments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($loan->approvals as $history)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($history->approved_at)->format('d-m-Y H:i') }}</td>
                                                    <td>{{ ucfirst($history->action ?? 'N/A') }}</td>
                                                    <td>
                                                        <span class="badge bg-info">Level {{ $history->approval_level }}</span>
                                                    </td>
                                                    <td>{{ $history->user->name ?? 'System' }}</td>
                                                    <td>{{ $history->comments ?? 'No comments' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bx bx-history fs-1 text-muted mb-3"></i>
                                    <h6 class="text-muted">No approval history available</h6>
                                    <p class="text-muted">Approval history will be recorded as the loan progresses through the
                                        approval process.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addGuarantorModal" tabindex="-1" aria-labelledby="addGuarantorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="addGuarantorForm" action="{{ route('loans.addGuarantor', $loan->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addGuarantorModalLabel">Add Guarantor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="loan_id" value="{{ $loan->id }}">
                        <div class="mb-3">
                            <label for="guarantor_id" class="form-label">Select Guarantor</label>
                            <select class="form-select" name="guarantor_id" id="guarantor_id" required>
                                <option value="">-- Choose Guarantor --</option>
                                @foreach($guarantorCustomers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone1 }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="relation" class="form-label">Relation to Borrower</label>
                            <input type="text" class="form-control" name="relation" id="relation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Guarantor</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modern Document Upload Modal -->

    <!-- Add Repayment Modal -->
    <div class="modal fade" id="addRepaymentModal" tabindex="-1" aria-labelledby="addRepaymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="addRepaymentForm" action="#" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addRepaymentModalLabel">Add Repayment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" id="payment_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_type" class="form-label">Payment Type</label>
                        <select class="form-select" name="payment_type" id="payment_type" required>
                            <option value="regular">Regular Payment</option>
                            <option value="early">Early Payment</option>
                            <option value="late">Late Payment</option>
                            <option value="partial">Partial Payment</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="comments" class="form-label">Comments</label>
                        <textarea class="form-control" name="comments" id="comments" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Repayment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Collateral Modal -->
    <div class="modal fade" id="addCollateralModal" tabindex="-1" aria-labelledby="addCollateralModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="addCollateralForm" action="{{ route('loan-collaterals.store') }}" method="POST"
                enctype="multipart/form-data" class="modal-content">
                @csrf
                <input type="hidden" name="loan_id" value="{{ $loan->id }}">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addCollateralModalLabel">
                        <i class="bx bx-plus-circle me-2"></i>Add Collateral
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="collateral_type" class="form-label">Collateral Type</label>
                                <select class="form-select" name="type" id="collateral_type" required>
                                    <option value="">-- Select Type --</option>
                                    @foreach(\App\Models\LoanCollateral::getTypeOptions() as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title/Name</label>
                                <input type="text" class="form-control" name="title" id="title" required
                                    placeholder="e.g., Toyota Corolla 2020">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="description" rows="3" required
                                    placeholder="Detailed description of the collateral"></textarea>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="col-12 mt-3">
                            <h6 class="text-primary mb-3"><i class="bx bx-money me-2"></i>Financial Information</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estimated_value" class="form-label">Estimated Value (TZS)</label>
                                <input type="number" step="0.01" class="form-control" name="estimated_value"
                                    id="estimated_value" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="appraised_value" class="form-label">Appraised Value (TZS) <small
                                        class="text-muted">(Optional)</small></label>
                                <input type="number" step="0.01" class="form-control" name="appraised_value"
                                    id="appraised_value">
                            </div>
                        </div>

                        <!-- Additional Details -->
                        <div class="col-12 mt-3">
                            <h6 class="text-primary mb-3"><i class="bx bx-detail me-2"></i>Additional Details</h6>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="condition" class="form-label">Condition</label>
                                <select class="form-select" name="condition" id="condition">
                                    <option value="">-- Select Condition --</option>
                                    @foreach(\App\Models\LoanCollateral::getConditionOptions() as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" class="form-control" name="serial_number" id="serial_number"
                                    placeholder="Serial/Model number">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="registration_number" class="form-label">Registration Number</label>
                                <input type="text" class="form-control" name="registration_number" id="registration_number"
                                    placeholder="License/Registration">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" name="location" id="location"
                                    placeholder="Where is the collateral located?">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="valuation_date" class="form-label">Valuation Date</label>
                                <input type="date" class="form-control" name="valuation_date" id="valuation_date">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="valuator_name" class="form-label">Valuator Name</label>
                                <input type="text" class="form-control" name="valuator_name" id="valuator_name"
                                    placeholder="Name of the person who valued the collateral">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" name="notes" id="notes" rows="2"
                                    placeholder="Any additional information"></textarea>
                            </div>
                        </div>

                        <!-- File Uploads -->
                        <div class="col-12 mt-3">
                            <h6 class="text-primary mb-3"><i class="bx bx-upload me-2"></i>Images & Documents</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="images" class="form-label">Images</label>
                                <input type="file" class="form-control" name="images[]" id="images" multiple
                                    accept="image/*">
                                <small class="text-muted">Select multiple images (JPEG, PNG). Max 2MB each.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="documents" class="form-label">Documents</label>
                                <input type="file" class="form-control" name="documents[]" id="documents" multiple
                                    accept=".pdf,.doc,.docx,.jpg,.png">
                                <small class="text-muted">Select documents (PDF, DOC, DOCX, Images). Max 5MB each.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check me-1"></i>Add Collateral
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Collateral Modal -->
    <div class="modal fade" id="viewCollateralModal" tabindex="-1" aria-labelledby="viewCollateralModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="viewCollateralModalLabel">
                        <i class="bx bx-show me-2"></i>Collateral Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="collateralDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="editCollateralFromView()">
                        <i class="bx bx-edit me-1"></i>Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Collateral Modal -->
    <div class="modal fade" id="editCollateralModal" tabindex="-1" aria-labelledby="editCollateralModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="editCollateralForm" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" id="editCollateralModalLabel">
                        <i class="bx bx-edit me-2"></i>Edit Collateral
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editCollateralContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-secondary">
                        <i class="bx bx-check me-1"></i>Update Collateral
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Status Modal -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="changeStatusModalLabel">
                        <i class="bx bx-refresh me-2"></i>Change Collateral Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changeStatusForm">
                        <input type="hidden" id="status_collateral_id" name="collateral_id">
                        <div class="mb-3">
                            <label for="new_status" class="form-label">New Status</label>
                            <select class="form-select" name="status" id="new_status" required>
                                @foreach(\App\Models\LoanCollateral::getStatusOptions() as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status_reason" class="form-label">Reason for Change</label>
                            <textarea class="form-control" name="reason" id="status_reason" rows="3"
                                placeholder="Explain why the status is being changed"></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Current Status:</strong> <span id="current_status_display"></span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-warning" onclick="updateCollateralStatus()">
                        <i class="bx bx-check me-1"></i>Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="approvalForm" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="approvalMessage"></p>
                    <div class="mb-3" id="disburse_bank_wrapper" style="display:none;">
                        <label for="approval_bank_account_id" class="form-label">Select Bank Account <span
                                class="text-danger">*</span></label>
                        <select class="form-select" name="bank_account_id" id="approval_bank_account_id">
                            <option value="">-- Select Bank Account --</option>
                            @foreach($bankAccounts ?? [] as $bankAccount)
                                <option value="{{ $bankAccount->id }}">{{ $bankAccount->account_number }} -
                                    {{ $bankAccount->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">This bank account will be used for the disbursement entry.</div>
                    </div>
                    <div class="mb-3">
                        <label for="comments" class="form-label">Comments (Optional)</label>
                        <textarea class="form-control" name="comments" id="comments" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Repay Schedule Modal -->
    <div class="modal fade" id="repayScheduleModal" tabindex="-1" aria-labelledby="repayScheduleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('repayments.store') }}"  method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="loan_id" value="{{ $loan->id }}">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="repayScheduleModalLabel">
                        <i class="bx bx-credit-card me-2"></i>Repay Schedule Item
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="schedule_id" id="schedule_id">

                    <!-- Schedule Details Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Schedule Details</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Due Date</label>
                                <p id="modal_due_date" class="fw-bold text-dark mb-0"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Total Installment</label>
                                <p id="modal_total_installment" class="fw-bold text-success mb-0"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Breakdown Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="bx bx-calculator me-2"></i>Amount Breakdown</h6>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Principal</label>
                                <p id="modal_principal" class="fw-bold text-dark mb-0"></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Interest</label>
                                <p id="modal_interest" class="fw-bold text-dark mb-0"></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Penalty</label>
                                <p id="modal_penalty" class="fw-bold text-danger mb-0"></p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Fee</label>
                                <p id="modal_fee" class="fw-bold text-warning mb-0"></p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Payment Details Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="bx bx-credit-card me-2"></i>Payment Details</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Payment Date</label>
                                <input type="date" class="form-control" name="payment_date" id="payment_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_amount" class="form-label">Amount</label>
                                <input type="number" step="0.01" class="form-control" name="amount" id="payment_amount"
                                    required>
                                {{-- <small class="text-muted">
                                    <strong>Settle Amount:</strong> TZS
                                    {{ number_format($loan->total_amount_to_settle, 2) }}
                                    (pays current interest + all remaining principal)
                                </small> --}}
                            </div>
                        </div>

                        <!-- Payment Source Selection -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="payment_source" class="form-label">Payment Source</label>
                                <select class="form-select" name="payment_source" id="payment_source" required>
                                    <option value="">-- Select Payment Source --</option>
                                    <option value="bank">Receive from Bank</option>
                                    <option value="cash_deposit">Receive from Cash Deposit</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bank Account Field -->
                        <div class="col-md-12" id="bank_account_section" style="display: none;">
                            <div class="mb-3">
                                <label for="bank_account_id" class="form-label">Bank Account</label>
                                <select class="form-select" name="bank_account_id" id="bank_account_id">
                                    <option value="">-- Select Bank Account --</option>
                                    @foreach($bankAccounts ?? [] as $bankAccount)
                                        <option value="{{ $bankAccount->id }}">{{ $bankAccount->name }} -
                                            {{ $bankAccount->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Cash Deposit Account Field -->
                        <div class="col-md-12" id="cash_deposit_section" style="display: none;">
                            <div class="mb-3">
                                <label for="cash_deposit_id" class="form-label">Cash Deposit Account</label>
                                <select class="form-select" name="cash_deposit_id" id="cash_deposit_id">
                                    <option value="">-- Select Cash Deposit Account --</option>
                                    @php
                                        $cashDeposits = \App\Models\CashCollateral::with(['customer', 'type'])
                                            ->where('amount', '>', 0)
                                            ->get();
                                    @endphp
                                    @foreach($cashDeposits as $deposit)
                                        <option value="{{ $deposit->id }}" data-balance="{{ $deposit->amount }}">
                                            {{ $deposit->customer->name }} - {{ $deposit->type->name }} (Balance: TSHS
                                            {{ number_format($deposit->amount, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="deposit_balance_info" style="display: none;">
                                    Available Balance: <span id="selected_balance" class="text-success fw-bold"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check me-1"></i>Add Repayment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Repayment Modal -->
    <div class="modal fade" id="editRepaymentModal" tabindex="-1" aria-labelledby="editRepaymentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="editRepaymentForm" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" id="editRepaymentModalLabel">
                        <i class="bx bx-edit me-2"></i>Edit Repayment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_payment_date" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" id="edit_payment_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="edit_amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_bank_account_id" class="form-label">Bank Account</label>
                        <select class="form-select" name="bank_account_id" id="edit_bank_account_id" required>
                            <option value="">-- Select Bank Account --</option>
                            @foreach($bankAccounts ?? [] as $bankAccount)
                                <option value="{{ $bankAccount->id }}">{{ $bankAccount->name }} -
                                    {{ $bankAccount->account_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-secondary">
                        <i class="bx bx-check me-1"></i>Update Repayment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Settle Loan Modal -->
    <div class="modal fade" id="settleLoanModal" tabindex="-1" aria-labelledby="settleLoanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('repayments.settle') }}" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="loan_id" value="{{ $loan->id }}">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="settleLoanModalLabel">
                        <i class="bx bx-check-circle me-2"></i>Settle Loan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Settlement Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-warning mb-3"><i class="bx bx-info-circle me-2"></i>Settlement Information</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Settle Amount</label>
                                <p class="fw-bold text-warning mb-0" id="settle_amount_display">
                                    TZS {{ number_format($loan->total_amount_to_settle, 2) }}
                                </p>
                                <small class="text-muted">Pays current interest + all remaining principal</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Customer</label>
                                <p class="fw-bold text-dark mb-0">{{ $loan->customer->name }}</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Payment Details Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="bx bx-credit-card me-2"></i>Payment Details</h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="settle_payment_date" class="form-label">Payment Date</label>
                                <input type="date" class="form-control" name="payment_date" id="settle_payment_date"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="settle_amount" class="form-label">Amount</label>
                                <input type="number" step="0.01" class="form-control" name="amount" id="settle_amount"
                                    value="{{ $loan->total_amount_to_settle }}" readonly>
                                <small class="text-muted">This amount is automatically calculated for settlement</small>
                            </div>
                        </div>

                        <!-- Payment Source Selection -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="settle_payment_source" class="form-label">Payment Source</label>
                                <select class="form-select" name="payment_source" id="settle_payment_source" required>
                                    <option value="">-- Select Payment Source --</option>
                                    <option value="bank">Receive from Bank</option>
                                    <option value="cash_deposit">Receive from Cash Deposit</option>
                                </select>
                            </div>
                        </div>

                        <!-- Bank Account Field -->
                        <div class="col-md-12" id="settle_bank_account_section" style="display: none;">
                            <div class="mb-3">
                                <label for="settle_bank_account_id" class="form-label">Bank Account</label>
                                <select class="form-select" name="bank_account_id" id="settle_bank_account_id">
                                    <option value="">-- Select Bank Account --</option>
                                    @foreach($bankAccounts ?? [] as $bankAccount)
                                        <option value="{{ $bankAccount->id }}">{{ $bankAccount->name }} -
                                            {{ $bankAccount->account_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Cash Deposit Account Field -->
                        <div class="col-md-12" id="settle_cash_deposit_section" style="display: none;">
                            <div class="mb-3">
                                <label for="settle_cash_deposit_id" class="form-label">Cash Deposit Account</label>
                                <select class="form-select" name="cash_deposit_id" id="settle_cash_deposit_id">
                                    <option value="">-- Select Cash Deposit Account --</option>
                                    @php
                                        $cashDeposits = \App\Models\CashCollateral::with(['customer', 'type'])
                                            ->where('amount', '>', 0)
                                            ->get();
                                    @endphp
                                    @foreach($cashDeposits as $deposit)
                                        <option value="{{ $deposit->id }}" data-balance="{{ $deposit->amount }}">
                                            {{ $deposit->customer->name }} - {{ $deposit->type->name }} (Balance: TSHS
                                            {{ number_format($deposit->amount, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="settle_deposit_balance_info" style="display: none;">
                                    Available Balance: <span id="settle_selected_balance" class="text-success fw-bold"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-check-circle me-1"></i>Settle Loan
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection


@push('scripts')
    <script>
        // Toast notification function
        function showToast(title, message, type = 'info') {
            const toastClass = type === 'success' ? 'bg-success' :
                type === 'error' ? 'bg-danger' :
                    type === 'warning' ? 'bg-warning' : 'bg-info';

            const toastHtml = `
                                                                                                                        <div class="toast align-items-center text-white ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                                                                                                                            <div class="d-flex">
                                                                                                                                <div class="toast-body">
                                                                                                                                    <strong>${title}</strong><br>
                                                                                                                                    ${message}
                                                                                                                                </div>
                                                                                                                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    `;

            // Create toast container if it doesn't exist
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }

            // Add toast to container
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);

            // Get the last added toast and show it
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 3000
            });
            toast.show();

            // Remove toast element after it's hidden
            toastElement.addEventListener('hidden.bs.toast', function () {
                toastElement.remove();
            });
        }

        $(document).ready(function () {
            $('#loansTableDetail').DataTable({
                responsive: true,
                order: [
                    [1, 'asc'] // Sort by due date column (index 1) in ascending order
                ],
                pageLength: 10,
                language: {
                    search: "",
                    searchPlaceholder: "Search loans..."
                },
                columnDefs: [{
                    targets: -1,
                    responsivePriority: 1,
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [0, 1, 2],
                    responsivePriority: 2
                }
                ]
            });

            // Handle collateral form submission
            $('#collateralForm').on('submit', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Feature Not Implemented',
                    text: 'Collateral management functionality will be implemented soon.',
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            });

            // Handle guarantor form submission
            $('#addGuarantorForm').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Adding...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#addGuarantorModal').modal('hide');
                        showToast('Success!', 'Guarantor added successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    },
                    error: function (xhr) {
                        let errorMessage = 'Failed to add guarantor.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                        showToast('Error!', errorMessage, 'error');
                    },
                    complete: function () {
                        // Re-enable submit button
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });




            // Handle add repayment form submission
            $('#addRepaymentForm').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Processing...');

                const newRow = `
                                                                                        <div class="document-upload-row mb-3 p-3 border rounded">
                                                                                            <div class="row g-3">
                                                                                                <div class="col-md-6">
                                                                                                    <label class="form-label">Document Type</label>
                                                                                                    <select class="form-select document-type" name="filetypes[]" required>
                                                                                                        <option value="">-- Select Document Type --</option>
                                                                                                        @foreach($filetypes as $file)
                                                                                                            <option value="{{ $file->id }}">{{ $file->name }}</option>
                                                                                                        @endforeach
                                                                                                    </select>
                                                                                                </div>
                                                                                                <div class="col-md-6">
                                                                                                    <label class="form-label">Choose File</label>
                                                                                                    <div class="input-group">
                                                                                                        <input type="file" class="form-control document-file" name="files[]"
                                                                                                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                                                                                                        <button type="button" class="btn btn-outline-danger remove-document-btn">
                                                                                                            <i class="bx bx-trash"></i>
                                                                                                        </button>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    `;

                console.log('Generated new row HTML:', newRow);
                console.log('Target container exists:', $('#documentUploads').length > 0);
                console.log('Target container HTML before append:', $('#documentUploads').html());

                try {
                    $('#documentUploads').append(newRow);
                    console.log('✅ Successfully appended new row');
                    console.log('New document row added, total rows:', documentRowCount);

                    // Update remove buttons visibility
                    updateRemoveButtons();
                    console.log('✅ Updated remove buttons visibility');

                    console.log('Target container HTML after append:', $('#documentUploads').html());
                } catch (error) {
                    console.error('❌ Error appending new row:', error);
                }
            });

            // Fallback: Document delegation in case direct binding fails
            $(document).on('click', '#addAnotherDocument', function (e) {
                e.preventDefault();
                console.log('=== FALLBACK: ADD ANOTHER DOCUMENT CLICKED ===');
                console.log('Fallback handler triggered');

                // Only proceed if this is the first time (avoid double execution)
                if (!$(this).data('fallback-handled')) {
                    $(this).data('fallback-handled', true);
                    console.log('Executing fallback handler');

                    documentRowCount++;
                    console.log('Fallback: Incremented documentRowCount to:', documentRowCount);

                    const newRow = `
                                                                                    <div class="document-upload-row mb-3 p-3 border rounded">
                                                                                        <div class="row g-3">
                                                                                            <div class="col-md-6">
                                                                                                <label class="form-label">Document Type</label>
                                                                                                <select class="form-select document-type" name="filetypes[]" required>
                                                                                                    <option value="">-- Select Document Type --</option>
                                                                                                    @foreach($filetypes as $file)
                                                                                                        <option value="{{ $file->id }}">{{ $file->name }}</option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                            <div class="col-md-6">
                                                                                                <label class="form-label">Choose File</label>
                                                                                                <div class="input-group">
                                                                                                    <input type="file" class="form-control document-file" name="files[]"
                                                                                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                                                                                                    <button type="button" class="btn btn-outline-danger remove-document-btn">
                                                                                                        <i class="bx bx-trash"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                `;

                    console.log('Fallback: Generated new row HTML');
                    console.log('Fallback: Target container exists:', $('#documentUploads').length > 0);

                    try {
                        $('#documentUploads').append(newRow);
                        console.log('✅ Fallback: Successfully appended new row');
                        updateRemoveButtons();
                        console.log('✅ Fallback: Updated remove buttons visibility');
                    } catch (error) {
                        console.error('❌ Fallback: Error appending new row:', error);
                    }
                }
            });

            // Remove document row
            $(document).on('click', '.remove-document-btn', function () {
                console.log('Remove document clicked');
                const row = $(this).closest('.document-upload-row');
                row.fadeOut(300, function () {
                    $(this).remove();
                    documentRowCount--;
                    console.log('Document row removed, remaining rows:', documentRowCount);

                    // Show/hide remove buttons based on row count
                    updateRemoveButtons();
                });
            });

            // Update remove buttons visibility
            function updateRemoveButtons() {
                const rows = $('.document-upload-row');
                rows.each(function (index) {
                    const removeBtn = $(this).find('.remove-document-btn');
                    if (rows.length > 1) {
                        removeBtn.show();
                    } else {
                        removeBtn.hide();
                    }
                });
            }

            // Initialize remove buttons visibility
            updateRemoveButtons();

            // Handle delete document button click
            $(document).on('click', '.delete-document-btn', function () {
                const documentId = $(this).data('document-id');
                const documentName = $(this).data('document-name');

                console.log('Delete document clicked', { documentId, documentName });

                Swal.fire({
                    title: 'Delete Document?',
                    html: `Are you sure you want to delete <strong>"${documentName}"</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="bx bx-trash me-1"></i>Yes, delete it!',
                    cancelButtonText: '<i class="bx bx-x me-1"></i>Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('User confirmed deletion');

                        // Show loading state
                        const deleteBtn = $(this);
                        const originalHtml = deleteBtn.html();
                        deleteBtn.html('<i class="bx bx-loader-alt bx-spin"></i>').prop('disabled', true);

                        $.ajax({
                            url: `/loan-documents/${documentId}`,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                console.log('Delete success:', response);
                                showToast('Success!', 'Document deleted successfully!', 'success');

                                // Remove the table row with animation
                                deleteBtn.closest('tr').fadeOut(300, function () {
                                    $(this).remove();
                                });
                            },
                            error: function (xhr) {
                                console.error('Delete error:', xhr);
                                let errorMessage = 'Failed to delete document.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                showToast('Error!', errorMessage, 'error');

                                // Restore button state
                                deleteBtn.html(originalHtml).prop('disabled', false);
                            }
                        });
                    }
                });
            });

            // Handle document upload form submission
            $('#uploadDocumentForm').on('submit', function (e) {
                e.preventDefault();
                console.log('Upload form submitted');

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();

                // Validate form
                let isValid = true;
                $('.document-type').each(function () {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                $('.document-file').each(function () {
                    if (!$(this)[0].files.length) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    showToast('Error!', 'Please fill in all required fields', 'error');
                    return;
                }

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Uploading...');

                // Create FormData for file uploads
                const formData = new FormData(this);

                // Log form data for debugging
                console.log('Form data entries:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        console.log('Upload success:', response);
                        $('#uploadDocumentModal').modal('hide');
                        showToast('Success!', 'Documents uploaded successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    },
                    error: function (xhr) {
                        console.error('Upload error:', xhr);
                        let errorMessage = 'Failed to upload documents.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                        showToast('Error!', errorMessage, 'error');
                    },
                    complete: function () {
                        // Re-enable submit button
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Handle add repayment form submission
            $('#addRepaymentForm').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Processing...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#addRepaymentModal').modal('hide');
                        showToast('Success!', 'Repayment added successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    },
                    error: function (xhr) {
                        let errorMessage = 'Failed to add repayment.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                        showToast('Error!', errorMessage, 'error');
                    },
                    complete: function () {
                        // Re-enable submit button
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Handle approval form submission
            $('#approvalForm').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Processing...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        $('#approvalModal').modal('hide');
                        showToast('Success!', 'Action completed successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    },
                    error: function (xhr) {
                        let errorMessage = 'Failed to process action.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                        showToast('Error!', errorMessage, 'error');
                    },
                    complete: function () {
                        // Re-enable submit button
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Handle all delete forms with class form-delete
            $('.form-delete').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();
                const itemName = submitBtn.data('name') || 'item';

                Swal.fire({
                    title: `Remove ${itemName}?`,
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Disable submit button and show loading
                        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Removing...');

                        $.ajax({
                            url: form.attr('action'),
                            method: 'DELETE',
                            data: form.serialize(),
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                showToast('Success!', `${itemName} removed successfully!`, 'success');
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            },
                            error: function (xhr) {
                                let errorMessage = `Failed to remove ${itemName}.`;
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                showToast('Error!', errorMessage, 'error');
                            },
                            complete: function () {
                                // Re-enable submit button
                                submitBtn.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            });

            // Handle repayment schedule form submission
            $('#repayScheduleModal form').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.html();

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Processing...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        // Close modal
                        $('#repayScheduleModal').modal('hide');

                        // Show success toast
                        showToast('Success!', 'Repayment recorded successfully!', 'success');

                        // Reload the page to show updated data
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    },
                    error: function (xhr) {
                        let errorMessage = 'An error occurred while processing the repayment.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            // Try to extract error message from response
                            const match = xhr.responseText.match(/<title[^>]*>([^<]+)<\/title>/);
                            if (match) {
                                errorMessage = match[1];
                            }
                        }

                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    },
                    complete: function () {
                        // Re-enable submit button
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });

        function repayScheduleItem(scheduleId, amount, dueDate, principal, interest, penalty, fee) {
            // Set modal values
            document.getElementById('schedule_id').value = scheduleId;
            document.getElementById('modal_due_date').textContent = dueDate;
            document.getElementById('modal_total_installment').textContent = 'TZS ' + amount;
            document.getElementById('modal_principal').textContent = 'TZS ' + principal;
            document.getElementById('modal_interest').textContent = 'TZS ' + interest;
            document.getElementById('modal_penalty').textContent = 'TZS ' + penalty;
            document.getElementById('modal_fee').textContent = 'TZS ' + fee;
            document.getElementById('payment_amount').value = amount.replace(/[^\d.]/g, ''); // Remove TZS and commas
            document.getElementById('payment_date').value = new Date().toISOString().split('T')[0]; // Set today's date

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('repayScheduleModal'));
            modal.show();
        }

        // Payment source change handler
        $(document).ready(function () {
            $('#payment_source').change(function () {
                const selectedSource = $(this).val();

                if (selectedSource === 'bank') {
                    $('#bank_account_section').show();
                    $('#cash_deposit_section').hide();
                    $('#bank_account_id').prop('required', true);
                    $('#cash_deposit_id').prop('required', false);
                    $('#deposit_balance_info').hide();
                } else if (selectedSource === 'cash_deposit') {
                    $('#bank_account_section').hide();
                    $('#cash_deposit_section').show();
                    $('#bank_account_id').prop('required', false);
                    $('#cash_deposit_id').prop('required', true);
                    $('#deposit_balance_info').show();
                } else {
                    $('#bank_account_section').hide();
                    $('#cash_deposit_section').hide();
                    $('#bank_account_id').prop('required', false);
                    $('#cash_deposit_id').prop('required', false);
                    $('#deposit_balance_info').hide();
                }
            });

            // Cash deposit account change handler
            $('#cash_deposit_id').change(function () {
                const selectedOption = $(this).find('option:selected');
                const balance = selectedOption.data('balance');

                if (balance !== undefined) {
                    $('#selected_balance').text('TSHS ' + parseFloat(balance).toLocaleString('en-US', { minimumFractionDigits: 2 }));
                    $('#deposit_balance_info').show();
                } else {
                    $('#deposit_balance_info').hide();
                }
            });

            // Amount validation for cash deposit
            $('#payment_amount').on('input', function () {
                const paymentSource = $('#payment_source').val();
                const amount = parseFloat($(this).val()) || 0;

                if (paymentSource === 'cash_deposit') {
                    const selectedOption = $('#cash_deposit_id').find('option:selected');
                    const balance = parseFloat(selectedOption.data('balance')) || 0;

                    if (amount > balance) {
                        $(this).addClass('is-invalid');
                        // Show error message
                        if (!$(this).next('.invalid-feedback').length) {
                            $(this).after('<div class="invalid-feedback">Amount cannot exceed available balance</div>');
                        }
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).next('.invalid-feedback').remove();
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });

            // Form validation before submission
            $('#repayScheduleModal form').on('submit', function (e) {
                const paymentSource = $('#payment_source').val();

                if (!paymentSource) {
                    e.preventDefault();
                    alert('Please select a payment source');
                    return false;
                }

                if (paymentSource === 'cash_deposit') {
                    const amount = parseFloat($('#payment_amount').val()) || 0;
                    const selectedOption = $('#cash_deposit_id').find('option:selected');
                    const balance = parseFloat(selectedOption.data('balance')) || 0;

                    if (amount > balance) {
                        e.preventDefault();
                        alert('Payment amount cannot exceed available cash deposit balance');
                        return false;
                    }

                    if (!$('#cash_deposit_id').val()) {
                        e.preventDefault();
                        alert('Please select a cash deposit account');
                        return false;
                    }
                } else if (paymentSource === 'bank') {
                    if (!$('#bank_account_id').val()) {
                        e.preventDefault();
                        alert('Please select a bank account');
                        return false;
                    }
                }
            });
        });

        function removePenalty(scheduleId, penaltyAmount) {
            Swal.fire({
                title: 'Remove Penalty',
                html: `
                <div class="text-start">
                    <p><strong>Penalty Amount:</strong> TZS ${penaltyAmount}</p>
                    <div class="mb-3">
                        <label for="penalty_amount_input" class="form-label">Penalty Amount to Remove</label>
                        <input type="number" class="form-control" id="penalty_amount_input" step="0.01" min="0" />
                        <small class="text-muted">Enter an amount up to the current penalty to remove all or part.</small>
                    </div>
                    <p class="text-muted">This will remove the penalty from this schedule item.</p>
                    <div class="mb-3">
                        <label for="penalty_reason" class="form-label">Reason for Removal (Optional)</label>
                        <textarea class="form-control" id="penalty_reason" rows="3" placeholder="Enter reason for penalty removal..."></textarea>
                    </div>
                </div>
            `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Remove Penalty',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                didOpen: (popup) => {
                    // Prefill the input with the current penalty amount and set constraints
                    const maxPenalty = parseFloat(String(penaltyAmount).replace(/[^\d.]/g, '')) || 0;
                    const amountInput = popup.querySelector('#penalty_amount_input');
                    if (amountInput) {
                        amountInput.value = maxPenalty.toFixed(2);
                        amountInput.setAttribute('max', maxPenalty.toFixed(2));
                        amountInput.setAttribute('min', '0');
                        amountInput.setAttribute('step', '0.01');
                    }
                },
                preConfirm: () => {
                    // Ensure penaltyAmount is a plain number
                    const maxPenalty = parseFloat(String(penaltyAmount).replace(/[^\d.]/g, '')) || 0;
                    const amountInput = document.getElementById('penalty_amount_input');
                    // Initialize default value on first render if empty
                    if (amountInput && !amountInput.value) {
                        amountInput.value = maxPenalty.toFixed(2);
                    }
                    const enteredAmount = parseFloat(String(amountInput.value).replace(/[^\d.]/g, '')) || 0;

                    if (enteredAmount < 0) {
                        Swal.showValidationMessage('Amount cannot be negative');
                        return false;
                    }
                    if (enteredAmount > maxPenalty) {
                        Swal.showValidationMessage(`Amount cannot exceed current penalty (TZS ${maxPenalty.toFixed(2)})`);
                        return false;
                    }

                    return {
                        reason: document.getElementById('penalty_reason').value,
                        amount: enteredAmount
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to remove penalty
                    $.ajax({
                        url: `/repayments/remove-penalty/${scheduleId}`,
                        method: 'POST',
                        data: {
                            amount: result.value.amount,
                            loan_id: $('#loan_id').val() || window.loanId || '',
                            schedule_id: scheduleId,
                            reason: result.value.reason,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            showToast('Success!', 'Penalty removed successfully!', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        },
                        error: function (xhr) {
                            let errorMessage = 'Failed to remove penalty.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }

        function disburseLoan(loanId) {
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            const message = document.getElementById('approvalMessage');
            const form = document.getElementById('approvalForm');
            const bankWrapper = document.getElementById('disburse_bank_wrapper');
            const bankSelect = document.getElementById('approval_bank_account_id');

            message.textContent = 'Are you sure you want to disburse this loan? This will mark the loan as disbursed and activate the repayment schedule.';
            form.action = `/loans/${loanId}/disburse`;

            // Show bank selection and require it
            if (bankWrapper) bankWrapper.style.display = '';
            if (bankSelect) bankSelect.setAttribute('required', 'required');

            modal.show();
        }

        function approveLoan(loanId) {
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            const message = document.getElementById('approvalMessage');
            const form = document.getElementById('approvalForm');
            const bankWrapper = document.getElementById('disburse_bank_wrapper');
            const bankSelect = document.getElementById('approval_bank_account_id');

            message.textContent = 'Are you sure you want to approve this loan? This will change the loan status to approved.';
            form.action = `/loans/${loanId}/approve`;

            // Hide bank selection for non-disburse actions
            if (bankWrapper) bankWrapper.style.display = 'none';
            if (bankSelect) bankSelect.removeAttribute('required');

            modal.show();
        }

        function checkLoan(loanId) {
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            const message = document.getElementById('approvalMessage');
            const form = document.getElementById('approvalForm');
            const bankWrapper = document.getElementById('disburse_bank_wrapper');
            const bankSelect = document.getElementById('approval_bank_account_id');

            message.textContent = 'Are you sure you want to check this loan? This will mark the loan as checked for first level approval.';
            form.action = `/loans/${loanId}/check`;

            if (bankWrapper) bankWrapper.style.display = 'none';
            if (bankSelect) bankSelect.removeAttribute('required');

            modal.show();
        }

        function authorizeLoan(loanId) {
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            const message = document.getElementById('approvalMessage');
            const form = document.getElementById('approvalForm');
            const bankWrapper = document.getElementById('disburse_bank_wrapper');
            const bankSelect = document.getElementById('approval_bank_account_id');

            message.textContent = 'Are you sure you want to authorize this loan? This will mark the loan as authorized for final approval.';
            form.action = `/loans/${loanId}/authorize`;

            if (bankWrapper) bankWrapper.style.display = 'none';
            if (bankSelect) bankSelect.removeAttribute('required');

            modal.show();
        }

        function rejectLoan(loanId) {
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            const message = document.getElementById('approvalMessage');
            const form = document.getElementById('approvalForm');

            message.textContent = 'Are you sure you want to reject this loan? This action cannot be undone.';
            form.action = `/loans/${loanId}/reject`;

            modal.show();
        }

        function approveApplication(loanId) {
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            const message = document.getElementById('approvalMessage');
            const form = document.getElementById('approvalForm');

            message.textContent = 'Are you sure you want to approve this loan application? This will convert it to an active loan.';
            form.action = `/loans/application/${loanId}/approve`;

            modal.show();
        }

        function defaultLoan(loanId) {
            const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
            const message = document.getElementById('approvalMessage');
            const form = document.getElementById('approvalForm');

            message.textContent = 'Are you sure you want to mark this loan as defaulted? This will change the loan status to defaulted.';
            form.action = `/loans/${loanId}/default`;

            modal.show();
        }

        function deleteLoan(loanId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/loans/${loanId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Repayment Management Functions
        function editRepayment(repaymentId) {
            // Fetch repayment data
            $.ajax({
                url: `/repayments/${repaymentId}/edit`,
                method: 'GET',
                success: function (response) {
                    if (response.success) {
                        const repayment = response.repayment;

                        // Set form action
                        $('#editRepaymentForm').attr('action', `/repayments/${repaymentId}`);

                        // Populate form fields
                        $('#edit_payment_date').val(repayment.payment_date);
                        $('#edit_amount').val(repayment.cash_deposit);
                        $('#edit_bank_account_id').val(repayment.bank_account_id);

                        // Show modal
                        $('#editRepaymentModal').modal('show');
                    }
                },
                error: function (xhr) {
                    showToast('Error!', 'Failed to load repayment data', 'error');
                }
            });
        }

        function deleteRepayment(repaymentId) {
            Swal.fire({
                title: 'Delete Repayment?',
                text: "This will also delete associated receipts and GL transactions. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/repayments/${repaymentId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                showToast('Success!', response.message, 'success');
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            } else {
                                showToast('Error!', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Failed to delete repayment.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            showToast('Error!', errorMessage, 'error');
                        }
                    });
                }
            });
        }

        function printReceipt(repaymentId) {
            // Show loading
            Swal.fire({
                title: 'Generating Receipt...',
                text: 'Please wait while we prepare your receipt for printing.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/repayments/${repaymentId}/print`,
                method: 'GET',
                success: function (response) {
                    if (response.success) {
                        Swal.close();

                        // Generate thermal printer receipt
                        const receiptData = response.receipt_data;
                        printThermalReceipt(receiptData);

                        showToast('Success!', 'Receipt generated successfully!', 'success');
                    } else {
                        Swal.close();
                        showToast('Error!', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    Swal.close();
                    let errorMessage = 'Failed to generate receipt.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showToast('Error!', errorMessage, 'error');
                }
            });
        }

        function printThermalReceipt(receiptData) {
            // Create a new window for thermal printer (narrow width)
            const printWindow = window.open('', '_blank', 'width=320,height=600');

            // Set the document title to customer name for printing
            const customerName = receiptData.customer_name.replace(/[^a-zA-Z0-9\s]/g, '').replace(/\s+/g, '_');
            const fileName = `Receipt_${customerName}_${receiptData.date}`;

            const receiptHtml = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${fileName}</title>
                    <style>
                        @page {
                            size: 80mm 200mm;
                            margin: 0;
                            padding: 0;
                        }

                        @media print {
                            body {
                                font-family: 'Courier New', monospace;
                                font-size: 10px;
                                margin: 0;
                                padding: 5px;
                                width: 280px;
                                max-width: 280px;
                                min-width: 280px;
                                page-break-after: avoid;
                                page-break-before: avoid;
                            }
                            .header { text-align: center; margin-bottom: 8px; }
                            .title { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
                            .subtitle { font-size: 10px; margin-bottom: 8px; }
                            .divider { border-top: 1px dashed #000; margin: 8px 0; }
                            .row { display: flex; justify-content: space-between; margin: 2px 0; }
                            .label { font-weight: bold; }
                            .value { text-align: right; }
                            .total { font-weight: bold; font-size: 12px; }
                            .footer { text-align: center; margin-top: 15px; font-size: 8px; }
                            .center { text-align: center; }
                            .bold { font-weight: bold; }

                            /* Force thermal printer format */
                            html, body {
                                width: 280px !important;
                                max-width: 280px !important;
                                min-width: 280px !important;
                            }
                        }

                        body {
                            font-family: 'Courier New', monospace;
                            font-size: 10px;
                            margin: 0;
                            padding: 5px;
                            width: 280px;
                            max-width: 280px;
                            min-width: 280px;
                        }
                        .header { text-align: center; margin-bottom: 8px; }
                        .title { font-size: 14px; font-weight: bold; margin-bottom: 3px; }
                        .subtitle { font-size: 10px; margin-bottom: 8px; }
                        .divider { border-top: 1px dashed #000; margin: 8px 0; }
                        .row { display: flex; justify-content: space-between; margin: 2px 0; }
                        .label { font-weight: bold; }
                        .value { text-align: right; }
                        .total { font-weight: bold; font-size: 12px; }
                        .footer { text-align: center; margin-top: 15px; font-size: 8px; }
                        .center { text-align: center; }
                        .bold { font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="title">SMARTFINANCE</div>
                        <div class="subtitle">Loan Repayment Receipt</div>
                    </div>

                    <div class="divider"></div>

                    <div class="row">
                        <span class="label">Customer:</span>
                        <span class="value">${receiptData.customer_name}</span>
                    </div>
                    <div class="row">
                        <span class="label">Loan No:</span>
                        <span class="value">${receiptData.loan_number}</span>
                    </div>

                    <div class="divider"></div>

                    <div class="row">
                        <span class="label">Receipt No:</span>
                        <span class="value">${receiptData.receipt_number}</span>
                    </div>
                    <div class="row">
                        <span class="label">Date:</span>
                        <span class="value">${receiptData.date}</span>
                    </div>
                    <div class="row">
                        <span class="label">Time:</span>
                        <span class="value">${new Date().toLocaleTimeString()}</span>
                    </div>
                    <div class="row">
                        <span class="label">Bank Account:</span>
                        <span class="value">${receiptData.bank_account}</span>
                    </div>
                    <div class="row">
                        <span class="label">Schedule No:</span>
                        <span class="value">${receiptData.schedule_number}</span>
                    </div>
                    <div class="row">
                        <span class="label">Due Date:</span>
                        <span class="value">${receiptData.due_date}</span>
                    </div>

                    <div class="divider"></div>

                    <div class="center bold">PAYMENT BREAKDOWN</div>

                    <div class="row">
                        <span class="label">Principal:</span>
                        <span class="value">TZS ${receiptData.payment_breakdown.principal.toLocaleString()}</span>
                    </div>
                    <div class="row">
                        <span class="label">Interest:</span>
                        <span class="value">TZS ${receiptData.payment_breakdown.interest.toLocaleString()}</span>
                    </div>
                    <div class="row">
                        <span class="label">Penalty:</span>
                        <span class="value">TZS ${receiptData.payment_breakdown.penalty.toLocaleString()}</span>
                    </div>
                    <div class="row">
                        <span class="label">Fee:</span>
                        <span class="value">TZS ${receiptData.payment_breakdown.fee.toLocaleString()}</span>
                    </div>

                    <div class="divider"></div>

                    <div class="row total">
                        <span class="label">TOTAL PAID:</span>
                        <span class="value">TZS ${receiptData.amount_paid.toLocaleString()}</span>
                    </div>

                    <div class="divider"></div>

                    <div class="center bold">REMAINING SCHEDULE INFO</div>

                    <div class="row">
                        <span class="label">Remaining on Schedule:</span>
                        <span class="value">TZS ${receiptData.remain_schedule.toLocaleString()}</span>
                    </div>
                    <div class="row">
                        <span class="label">Remaining Schedules:</span>
                        <span class="value">${receiptData.remaining_schedules_count}</span>
                    </div>
                    <div class="row">
                        <span class="label">Total Remaining:</span>
                        <span class="value">TZS ${receiptData.remaining_schedules_amount.toLocaleString()}</span>
                    </div>

                    <div class="divider"></div>

                    <div class="row">
                        <span class="label">Received By:</span>
                        <span class="value">${receiptData.received_by}</span>
                    </div>
                    <div class="row">
                        <span class="label">Branch:</span>
                        <span class="value">${receiptData.branch}</span>
                    </div>

                    <div class="divider"></div>

                    <div class="footer">
                        <div class="bold">Thank you for your payment!</div>
                        <div>Keep this receipt for your records</div>
                        <div style="margin-top: 5px;">--- End of Receipt ---</div>
                    </div>
                </body>
                </html>
            `;

            printWindow.document.write(receiptHtml);
            printWindow.document.close();

            // Print after a short delay
            setTimeout(() => {
                // Set print options for thermal printer
                const printOptions = {
                    silent: false,
                    printBackground: false,
                    color: false,
                    margin: {
                        marginType: 'none',
                        top: 0,
                        bottom: 0,
                        left: 0,
                        right: 0
                    },
                    landscape: false,
                    pagesPerSheet: 1,
                    collate: false,
                    copies: 1,
                    header: '',
                    footer: ''
                };

                // Try to use print options if available (Electron/Chrome)
                if (printWindow.print) {
                    printWindow.print();
                } else {
                    // Fallback for regular browsers
                    printWindow.document.execCommand('print', false, null);
                }

                printWindow.close();
            }, 500);
        }

        // Handle edit repayment form submission
        $('#editRepaymentForm').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();

            // Disable submit button and show loading
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Updating...');

            $.ajax({
                url: form.attr('action'),
                method: 'PUT',
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    // Close modal
                    $('#editRepaymentModal').modal('hide');

                    if (response.success) {
                        showToast('Success!', response.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast('Error!', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    let errorMessage = 'Failed to update repayment.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showToast('Error!', errorMessage, 'error');
                },
                complete: function () {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Collateral Management Functions
        function viewCollateral(collateralId) {
            // Show loading
            $('#collateralDetailsContent').html('<div class="text-center py-4"><i class="bx bx-loader-alt bx-spin fs-1 text-primary"></i><br>Loading...</div>');
            $('#viewCollateralModal').modal('show');

            $.ajax({
                url: `/loan-collaterals/${collateralId}`,
                method: 'GET',
                success: function (response) {
                    if (response.success) {
                        const collateral = response.collateral;
                        const images = response.images;
                        const documents = response.documents;

                        let imagesHtml = '';
                        if (images && images.length > 0) {
                            imagesHtml = '<div class="row">';
                            images.forEach((image, index) => {
                                imagesHtml += `
                                                                                                                                            <div class="col-md-3 mb-3">
                                                                                                                                                <img src="${image}" class="img-fluid rounded shadow-sm" style="height: 150px; object-fit: cover; width: 100%;"
                                                                                                                                                     onclick="openImageModal('${image}')" role="button">
                                                                                                                                            </div>
                                                                                                                                        `;
                            });
                            imagesHtml += '</div>';
                        } else {
                            imagesHtml = '<p class="text-muted">No images uploaded</p>';
                        }

                        let documentsHtml = '';
                        if (documents && documents.length > 0) {
                            documentsHtml = '<div class="list-group">';
                            documents.forEach(doc => {
                                documentsHtml += `
                                                                                                                                            <a href="${doc.url}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                                                                                                                <span><i class="bx bx-file me-2"></i>${doc.name}</span>
                                                                                                                                                <i class="bx bx-download"></i>
                                                                                                                                            </a>
                                                                                                                                        `;
                            });
                            documentsHtml += '</div>';
                        } else {
                            documentsHtml = '<p class="text-muted">No documents uploaded</p>';
                        }

                        const statusBadge = getStatusBadge(collateral.status);
                        const conditionBadge = collateral.condition ? getConditionBadge(collateral.condition) : '<span class="text-muted">Not specified</span>';

                        const content = `
                                                                                                                                    <div class="row">
                                                                                                                                        <div class="col-md-8">
                                                                                                                                            <div class="row mb-4">
                                                                                                                                                <div class="col-12">
                                                                                                                                                    <h6 class="text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                                                                                                                                                </div>
                                                                                                                                                <div class="col-md-6">
                                                                                                                                                    <strong>Title:</strong> ${collateral.title}<br>
                                                                                                                                                    <strong>Type:</strong> ${collateral.type.charAt(0).toUpperCase() + collateral.type.slice(1)}<br>
                                                                                                                                                    <strong>Status:</strong> ${statusBadge}<br>
                                                                                                                                                    <strong>Condition:</strong> ${conditionBadge}
                                                                                                                                                </div>
                                                                                                                                                <div class="col-md-6">
                                                                                                                                                    <strong>Estimated Value:</strong> TZS ${parseFloat(collateral.estimated_value).toLocaleString()}<br>
                                                                                                                                                    ${collateral.appraised_value ? `<strong>Appraised Value:</strong> TZS ${parseFloat(collateral.appraised_value).toLocaleString()}<br>` : ''}
                                                                                                                                                    ${collateral.location ? `<strong>Location:</strong> ${collateral.location}<br>` : ''}
                                                                                                                                                    ${collateral.serial_number ? `<strong>Serial Number:</strong> ${collateral.serial_number}` : ''}
                                                                                                                                                </div>
                                                                                                                                            </div>

                                                                                                                                            <div class="mb-4">
                                                                                                                                                <h6 class="text-primary mb-3"><i class="bx bx-detail me-2"></i>Description</h6>
                                                                                                                                                <p>${collateral.description}</p>
                                                                                                                                            </div>

                                                                                                                                            ${collateral.notes ? `
                                                                                                                                            <div class="mb-4">
                                                                                                                                                <h6 class="text-primary mb-3"><i class="bx bx-note me-2"></i>Additional Notes</h6>
                                                                                                                                                <p>${collateral.notes}</p>
                                                                                                                                            </div>` : ''}

                                                                                                                                            <div class="mb-4">
                                                                                                                                                <h6 class="text-primary mb-3"><i class="bx bx-file me-2"></i>Documents</h6>
                                                                                                                                                ${documentsHtml}
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-4">
                                                                                                                                            <h6 class="text-primary mb-3"><i class="bx bx-image me-2"></i>Images</h6>
                                                                                                                                            ${imagesHtml}
                                                                                                                                        </div>
                                                                                                                                    </div>

                                                                                                                                    ${collateral.status_changed_at ? `
                                                                                                                                    <div class="alert alert-info">
                                                                                                                                        <h6><i class="bx bx-history me-2"></i>Status History</h6>
                                                                                                                                        <small>
                                                                                                                                            <strong>Last Changed:</strong> ${new Date(collateral.status_changed_at).toLocaleDateString()}<br>
                                                                                                                                            <strong>Changed By:</strong> ${collateral.status_changed_by || 'System'}<br>
                                                                                                                                            ${collateral.status_change_reason ? `<strong>Reason:</strong> ${collateral.status_change_reason}` : ''}
                                                                                                                                        </small>
                                                                                                                                    </div>` : ''}
                                                                                                                                `;

                        $('#collateralDetailsContent').html(content);
                        window.currentCollateralId = collateralId; // Store for edit function
                    }
                },
                error: function (xhr) {
                    $('#collateralDetailsContent').html('<div class="text-center py-4 text-danger"><i class="bx bx-error fs-1"></i><br>Error loading collateral details</div>');
                }
            });
        }

        function editCollateral(collateralId) {
            // Close view modal if open
            $('#viewCollateralModal').modal('hide');

            // Show loading in edit modal
            $('#editCollateralContent').html('<div class="text-center py-4"><i class="bx bx-loader-alt bx-spin fs-1 text-primary"></i><br>Loading...</div>');
            $('#editCollateralModal').modal('show');

            $.ajax({
                url: `/loan-collaterals/${collateralId}`,
                method: 'GET',
                success: function (response) {
                    if (response.success) {
                        const collateral = response.collateral;
                        const images = response.images;
                        const documents = response.documents;

                        // Set form action
                        $('#editCollateralForm').attr('action', `/loan-collaterals/${collateralId}`);

                        let existingImagesHtml = '';
                        if (images && images.length > 0) {
                            existingImagesHtml = '<div class="mb-3"><label class="form-label">Current Images</label><div class="row">';
                            images.forEach((image, index) => {
                                const imagePath = collateral.images[index];
                                existingImagesHtml += `
                                                                                                                                            <div class="col-md-3 mb-2">
                                                                                                                                                <div class="position-relative">
                                                                                                                                                    <img src="${image}" class="img-fluid rounded" style="height: 100px; object-fit: cover; width: 100%;">
                                                                                                                                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                                                                                                                                            onclick="removeFile('${imagePath}', 'image', ${collateralId})">
                                                                                                                                                        <i class="bx bx-x"></i>
                                                                                                                                                    </button>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                        `;
                            });
                            existingImagesHtml += '</div></div>';
                        }

                        let existingDocumentsHtml = '';
                        if (documents && documents.length > 0) {
                            existingDocumentsHtml = '<div class="mb-3"><label class="form-label">Current Documents</label><div class="list-group">';
                            documents.forEach((doc, index) => {
                                const documentPath = collateral.documents[index];
                                existingDocumentsHtml += `
                                                                                                                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                                                                                                                <span><i class="bx bx-file me-2"></i>${doc.name}</span>
                                                                                                                                                <div>
                                                                                                                                                    <a href="${doc.url}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                                                                                                                                        <i class="bx bx-download"></i>
                                                                                                                                                    </a>
                                                                                                                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                                                                                                                            onclick="removeFile('${documentPath}', 'document', ${collateralId})">
                                                                                                                                                        <i class="bx bx-trash"></i>
                                                                                                                                                    </button>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                        `;
                            });
                            existingDocumentsHtml += '</div></div>';
                        }

                        const editContent = `
                                                                                                                                    <div class="row">
                                                                                                                                        <div class="col-12">
                                                                                                                                            <h6 class="text-secondary mb-3"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-6">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_type" class="form-label">Collateral Type</label>
                                                                                                                                                <select class="form-select" name="type" id="edit_type" required>
                                                                                                                                                    <option value="property" ${collateral.type === 'property' ? 'selected' : ''}>Property</option>
                                                                                                                                                    <option value="vehicle" ${collateral.type === 'vehicle' ? 'selected' : ''}>Vehicle</option>
                                                                                                                                                    <option value="equipment" ${collateral.type === 'equipment' ? 'selected' : ''}>Equipment</option>
                                                                                                                                                    <option value="cash" ${collateral.type === 'cash' ? 'selected' : ''}>Cash</option>
                                                                                                                                                    <option value="jewelry" ${collateral.type === 'jewelry' ? 'selected' : ''}>Jewelry</option>
                                                                                                                                                    <option value="electronics" ${collateral.type === 'electronics' ? 'selected' : ''}>Electronics</option>
                                                                                                                                                    <option value="other" ${collateral.type === 'other' ? 'selected' : ''}>Other</option>
                                                                                                                                                </select>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-6">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_title" class="form-label">Title/Name</label>
                                                                                                                                                <input type="text" class="form-control" name="title" id="edit_title" value="${collateral.title}" required>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-12">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_description" class="form-label">Description</label>
                                                                                                                                                <textarea class="form-control" name="description" id="edit_description" rows="3" required>${collateral.description}</textarea>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-6">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_estimated_value" class="form-label">Estimated Value (TZS)</label>
                                                                                                                                                <input type="number" step="0.01" class="form-control" name="estimated_value" id="edit_estimated_value" value="${collateral.estimated_value}" required>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-6">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_appraised_value" class="form-label">Appraised Value (TZS)</label>
                                                                                                                                                <input type="number" step="0.01" class="form-control" name="appraised_value" id="edit_appraised_value" value="${collateral.appraised_value || ''}">
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-4">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_condition" class="form-label">Condition</label>
                                                                                                                                                <select class="form-select" name="condition" id="edit_condition">
                                                                                                                                                    <option value="">-- Select Condition --</option>
                                                                                                                                                    <option value="excellent" ${collateral.condition === 'excellent' ? 'selected' : ''}>Excellent</option>
                                                                                                                                                    <option value="good" ${collateral.condition === 'good' ? 'selected' : ''}>Good</option>
                                                                                                                                                    <option value="fair" ${collateral.condition === 'fair' ? 'selected' : ''}>Fair</option>
                                                                                                                                                    <option value="poor" ${collateral.condition === 'poor' ? 'selected' : ''}>Poor</option>
                                                                                                                                                </select>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-4">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_serial_number" class="form-label">Serial Number</label>
                                                                                                                                                <input type="text" class="form-control" name="serial_number" id="edit_serial_number" value="${collateral.serial_number || ''}">
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-4">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_registration_number" class="form-label">Registration Number</label>
                                                                                                                                                <input type="text" class="form-control" name="registration_number" id="edit_registration_number" value="${collateral.registration_number || ''}">
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-8">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_location" class="form-label">Location</label>
                                                                                                                                                <input type="text" class="form-control" name="location" id="edit_location" value="${collateral.location || ''}">
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-4">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_valuation_date" class="form-label">Valuation Date</label>
                                                                                                                                                <input type="date" class="form-control" name="valuation_date" id="edit_valuation_date" value="${collateral.valuation_date || ''}">
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-12">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_valuator_name" class="form-label">Valuator Name</label>
                                                                                                                                                <input type="text" class="form-control" name="valuator_name" id="edit_valuator_name" value="${collateral.valuator_name || ''}">
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-12">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_notes" class="form-label">Additional Notes</label>
                                                                                                                                                <textarea class="form-control" name="notes" id="edit_notes" rows="2">${collateral.notes || ''}</textarea>
                                                                                                                                            </div>
                                                                                                                                        </div>

                                                                                                                                        ${existingImagesHtml}
                                                                                                                                        ${existingDocumentsHtml}

                                                                                                                                        <div class="col-12 mt-3">
                                                                                                                                            <h6 class="text-secondary mb-3"><i class="bx bx-upload me-2"></i>Add New Files</h6>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-6">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_new_images" class="form-label">Add New Images</label>
                                                                                                                                                <input type="file" class="form-control" name="new_images[]" id="edit_new_images" multiple accept="image/*">
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                        <div class="col-md-6">
                                                                                                                                            <div class="mb-3">
                                                                                                                                                <label for="edit_new_documents" class="form-label">Add New Documents</label>
                                                                                                                                                <input type="file" class="form-control" name="new_documents[]" id="edit_new_documents" multiple accept=".pdf,.doc,.docx,.jpg,.png">
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                `;

                        $('#editCollateralContent').html(editContent);
                    }
                },
                error: function (xhr) {
                    $('#editCollateralContent').html('<div class="text-center py-4 text-danger"><i class="bx bx-error fs-1"></i><br>Error loading collateral for editing</div>');
                }
            });
        }

        function editCollateralFromView() {
            if (window.currentCollateralId) {
                editCollateral(window.currentCollateralId);
            }
        }

        function changeCollateralStatus(collateralId) {
            // Get current collateral data first
            $.ajax({
                url: `/loan-collaterals/${collateralId}`,
                method: 'GET',
                success: function (response) {
                    if (response.success) {
                        const collateral = response.collateral;
                        $('#status_collateral_id').val(collateralId);
                        $('#current_status_display').text(collateral.status.charAt(0).toUpperCase() + collateral.status.slice(1));
                        $('#new_status').val(collateral.status);
                        $('#changeStatusModal').modal('show');
                    }
                }
            });
        }

        function updateCollateralStatus() {
            const collateralId = $('#status_collateral_id').val();
            const newStatus = $('#new_status').val();
            const reason = $('#status_reason').val();

            if (!newStatus) {
                alert('Please select a status');
                return;
            }

            // Get the update button and add loading state
            const updateBtn = $('#changeStatusModal').find('button[onclick="updateCollateralStatus()"]');
            const originalText = updateBtn.html();
            updateBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Updating...');

            $.ajax({
                url: `/loan-collaterals/${collateralId}/status`,
                method: 'PATCH',
                data: {
                    status: newStatus,
                    reason: reason,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        $('#changeStatusModal').modal('hide');
                        showToast('Success!', response.message, 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast('Error!', response.message, 'error');
                    }
                },
                error: function (xhr) {
                    let errorMessage = 'Failed to update status.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showToast('Error!', errorMessage, 'error');
                },
                complete: function () {
                    // Re-enable update button
                    updateBtn.prop('disabled', false).html(originalText);
                }
            });
        }

        function deleteCollateral(collateralId) {
            Swal.fire({
                title: 'Delete Collateral?',
                text: "This will permanently delete the collateral and all associated files. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/loan-collaterals/${collateralId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                showToast('Success!', response.message, 'success');
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            } else {
                                showToast('Error!', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Failed to delete collateral.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            showToast('Error!', errorMessage, 'error');
                        }
                    });
                }
            });
        }

        function removeFile(filePath, fileType, collateralId) {
            Swal.fire({
                title: `Remove ${fileType}?`,
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/loan-collaterals/${collateralId}/remove-file`,
                        method: 'DELETE',
                        data: {
                            file_path: filePath,
                            file_type: fileType,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            if (response.success) {
                                showToast('Success!', response.message, 'success');
                                // Refresh the edit modal
                                editCollateral(collateralId);
                            } else {
                                showToast('Error!', response.message, 'error');
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = `Failed to remove ${fileType}.`;
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            showToast('Error!', errorMessage, 'error');
                        }
                    });
                }
            });
        }

        function openImageModal(imageUrl) {
            Swal.fire({
                imageUrl: imageUrl,
                imageAlt: 'Collateral Image',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    image: 'img-fluid'
                }
            });
        }

        function getStatusBadge(status) {
            const statusClasses = {
                'active': 'bg-success',
                'sold': 'bg-primary',
                'released': 'bg-info',
                'foreclosed': 'bg-warning',
                'damaged': 'bg-danger',
                'lost': 'bg-dark'
            };

            const className = statusClasses[status] || 'bg-secondary';
            return `<span class="badge ${className}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
        }

        function getConditionBadge(condition) {
            const conditionClasses = {
                'excellent': 'bg-success',
                'good': 'bg-primary',
                'fair': 'bg-warning',
                'poor': 'bg-danger'
            };

            const className = conditionClasses[condition] || 'bg-secondary';
            return `<span class="badge ${className}">${condition.charAt(0).toUpperCase() + condition.slice(1)}</span>`;
        }

        // Handle add collateral form submission
        $('#addCollateralForm').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();

            // Disable submit button and show loading
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Adding...');

            // Create FormData for file uploads
            const formData = new FormData(this);

            $.ajax({
                url: "{{ route('loan-collaterals.store') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('#addCollateralModal').modal('hide');
                    showToast('Success!', 'Collateral added successfully!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function (xhr) {
                    let errorMessage = 'Failed to add collateral.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join(', ');
                    }
                    showToast('Error!', errorMessage, 'error');
                },
                complete: function () {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Handle edit collateral form submission
        $('#editCollateralForm').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.html();

            // Disable submit button and show loading
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Updating...');

            // Create FormData for file uploads
            const formData = new FormData(this);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    $('#editCollateralModal').modal('hide');
                    showToast('Success!', 'Collateral updated successfully!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                },
                error: function (xhr) {
                    let errorMessage = 'Failed to update collateral.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join(', ');
                    }
                    showToast('Error!', errorMessage, 'error');
                },
                complete: function () {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Top-Up Modal Functions
        function showTopUpModal() {
            const loan = @json($loan);
            const currentBalance = @json($loan->getCalculatedTopUpAmount());

            Swal.fire({
                title: 'Apply for Top-Up Loan',
                html: `
                                                                                                                                    <div class="text-start">
                                                                                                                                                                                        <div class="alert alert-info">
                                                                                                                        <i class="bx bx-info-circle me-2"></i>
                                                                                                                        <strong>Customer:</strong> ${loan.customer.name}
                                                                                                                    </div>

                                                                                                                                        <div class="mb-3">
                                                                                                                                            <label for="topup_amount" class="form-label">New Loan Amount (TZS)</label>
                                                                                                                                            <input type="number" class="form-control" id="topup_amount"
                                                                                                                                                    placeholder="Enter amount greater than current balance" min="${currentBalance + 1}" step="1000" required>
                                                                                                                                            <small class="text-muted">Must be greater than current balance (TZS ${parseFloat(currentBalance).toLocaleString()})</small>
                                                                                                                                        </div>

                                                                                                                                        <div class="mb-3">
                                                                                                                                            <label for="topup_purpose" class="form-label">Purpose of Top-Up</label>
                                                                                                                                            <textarea class="form-control" id="topup_purpose" rows="3"
                                                                                                                                                        placeholder="Please describe the purpose of this top-up loan..."></textarea>
                                                                                                                                        </div>

                                                                                                                                        <div class="mb-3">
                                                                                                                                            <label for="topup_type" class="form-label">Top-Up Type</label>
                                                                                                                                            <select class="form-control" id="topup_type" required>
                                                                                                                                                <option value="restructure">Restructure (Replace old loan with new larger loan)</option>
                                                                                                                                                <option value="additional">Additional (Create separate new loan alongside old loan)</option>
                                                                                                                                            </select>
                                                                                                                                            <small class="text-muted">Choose how you want to handle the top-up</small>
                                                                                                                                        </div>

                                                                                                                                        <div class="mb-3">
                                                                                                                                            <label for="topup_period" class="form-label">Additional Period</label>
                                                                                                                                            <input type="number" class="form-control" id="topup_period"
                                                                                                                                                    value="12" min="1" max="60" required>
                                                                                                                                            <small class="text-muted">How many additional periods do you need?</small>
                                                                                                                                        </div>


                                                                                                                                    </div>
                                                                                                                                `,
                showCancelButton: true,
                confirmButtonText: 'Apply for Top-Up',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                width: '600px',
                preConfirm: () => {
                    const amount = parseFloat(document.getElementById('topup_amount').value);
                    const purpose = document.getElementById('topup_purpose').value;
                    const period = parseInt(document.getElementById('topup_period').value);
                    const topupType = document.getElementById('topup_type').value;

                    if (!amount || amount <= 0) {
                        Swal.showValidationMessage('Please enter a valid amount');
                        return false;
                    }

                    if (amount <= currentBalance) {
                        Swal.showValidationMessage('New loan amount must be greater than current balance');
                        return false;
                    }

                    if (!purpose.trim()) {
                        Swal.showValidationMessage('Please describe the purpose of the top-up');
                        return false;
                    }

                    if (!period || period < 1 || period > 60) {
                        Swal.showValidationMessage('Please enter a valid period (1-60 months)');
                        return false;
                    }

                    if (!topupType) {
                        Swal.showValidationMessage('Please select a top-up type');
                        return false;
                    }

                    return { amount, purpose, period, topup_type: topupType };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Processing Top-Up Application...',
                        text: 'Please wait while we process your top-up application.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit top-up application
                    submitTopUpApplication(result.value);
                }
            });

            // Add real-time calculation updates
            setTimeout(() => {
                const amountInput = document.getElementById('topup_amount');
                const typeSelect = document.getElementById('topup_type');

                function updateCalculations() {
                    const newAmount = parseFloat(amountInput.value) || 0;
                    const topupType = typeSelect.value;

                    let customerReceives;
                    if (topupType === 'restructure') {
                        customerReceives = Math.max(0, newAmount - currentBalance);
                    } else {
                        customerReceives = newAmount; // Customer receives full amount in additional
                    }

                    // Update displays
                    const newLoanDisplay = document.getElementById('new_loan_amount_display');
                    const customerReceivesDisplay = document.getElementById('customer_receives_display');

                    if (newLoanDisplay) {
                        newLoanDisplay.textContent = `TZS ${newAmount.toLocaleString()}`;
                    }
                    if (customerReceivesDisplay) {
                        customerReceivesDisplay.textContent = `TZS ${customerReceives.toLocaleString()}`;
                    }
                }

                if (amountInput) {
                    amountInput.addEventListener('input', updateCalculations);
                }
                if (typeSelect) {
                    typeSelect.addEventListener('change', updateCalculations);
                }
            }, 100);
        }

        function submitTopUpApplication(data) {
            const currentBalance = @json($loan->getCalculatedTopUpAmount());
            let customerReceives;
            if (data.topup_type === 'restructure') {
                customerReceives = data.amount - currentBalance;
            } else {
                customerReceives = data.amount; // Customer receives full amount in additional
            }

            $.ajax({
                url: `/loans/${@json($loan->encodedId)}/top-up`,
                method: 'POST',
                data: {
                    new_loan_amount: data.amount,
                    current_balance: currentBalance,
                    customer_receives: customerReceives,
                    purpose: data.purpose,
                    period: data.period,
                    topup_type: data.topup_type,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    Swal.close();

                    if (response.success) {
                        Swal.fire({
                            title: 'Top-Up Loan Created!',
                            text: response.message || 'Your top-up loan has been created successfully.',
                            icon: 'success',
                            confirmButtonText: 'View New Loan'
                        }).then(() => {
                            // Redirect to the new loan
                            if (response.new_loan_encoded_id) {
                                window.location.href = `/loans/${response.new_loan_encoded_id}`;
                            } else {
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Application Failed',
                            text: response.message || 'Failed to submit top-up application.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function (xhr) {
                    Swal.close();

                    let errorMessage = 'Failed to submit top-up application.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join(', ');
                    }

                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        (function () {
            function getCsrfToken() {
                var m = document.querySelector('meta[name="csrf-token"]');
                return m ? m.getAttribute('content') : '';
            }

            function updateBulkControls() {
                var checkboxes = Array.from(document.querySelectorAll('.repayment-select'));
                var anyChecked = checkboxes.some(function (cb) { return cb.checked; });
                var btn = document.getElementById('bulkDeleteRepaymentsBtn');
                if (btn) btn.disabled = !anyChecked;
                var allChecked = checkboxes.length > 0 && checkboxes.every(function (cb) { return cb.checked; });
                var master = document.getElementById('select_all_repayments');
                if (master) master.checked = allChecked;
            }

            function bindRepaymentCheckboxEvents() {
                document.querySelectorAll('.repayment-select').forEach(function (cb) {
                    cb.addEventListener('change', updateBulkControls);
                });
            }

            function bulkDeleteRepayments() {
                var ids = Array.from(document.querySelectorAll('.repayment-select:checked')).map(function (cb) { return parseInt(cb.value); });
                if (ids.length === 0) return;

                if (typeof Swal === 'undefined') {
                    // Fallback if SweetAlert is not available
                    if (!confirm('Are you sure you want to delete the selected repayments?')) return;

                    fetch('/repayments/bulk-delete', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({ ids: ids })
                    }).then(function (r) { return r.json(); }).then(function (resp) {
                        if (resp && resp.success) {
                            window.location.reload();
                        } else {
                            alert(resp && resp.message ? resp.message : 'Failed to delete repayments.');
                        }
                    }).catch(function () { alert('Failed to delete repayments.'); });
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete the selected repayments?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    fetch('/repayments/bulk-delete', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify({ ids: ids })
                    }).then(function (r) { return r.json(); }).then(function (resp) {
                        if (resp && resp.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Repayments deleted successfully.',
                                icon: 'success',
                                timer: 1200,
                                showConfirmButton: false
                            }).then(function () { window.location.reload(); });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: (resp && resp.message) ? resp.message : 'Failed to delete repayments.',
                                icon: 'error'
                            });
                        }
                    }).catch(function () {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to delete repayments.',
                            icon: 'error'
                        });
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                var master = document.getElementById('select_all_repayments');
                if (master) {
                    master.addEventListener('change', function () {
                        var checked = master.checked;
                        document.querySelectorAll('.repayment-select').forEach(function (cb) { cb.checked = checked; });
                        updateBulkControls();
                    });
                }
                var btn = document.getElementById('bulkDeleteRepaymentsBtn');
                if (btn) { btn.addEventListener('click', bulkDeleteRepayments); }
                bindRepaymentCheckboxEvents();
                updateBulkControls();
            });


        })();

        // Settle Loan Modal Functions
        function showSettleLoanModal() {
            const modal = new bootstrap.Modal(document.getElementById('settleLoanModal'));
            modal.show();
        }

        // Handle payment source selection for settle loan modal
        document.addEventListener('DOMContentLoaded', function() {
            const settlePaymentSource = document.getElementById('settle_payment_source');
            const settleBankSection = document.getElementById('settle_bank_account_section');
            const settleCashDepositSection = document.getElementById('settle_cash_deposit_section');
            const settleCashDepositSelect = document.getElementById('settle_cash_deposit_id');
            const settleDepositBalanceInfo = document.getElementById('settle_deposit_balance_info');
            const settleSelectedBalance = document.getElementById('settle_selected_balance');

            if (settlePaymentSource) {
                settlePaymentSource.addEventListener('change', function() {
                    if (this.value === 'bank') {
                        settleBankSection.style.display = 'block';
                        settleCashDepositSection.style.display = 'none';
                        settleDepositBalanceInfo.style.display = 'none';
                    } else if (this.value === 'cash_deposit') {
                        settleBankSection.style.display = 'none';
                        settleCashDepositSection.style.display = 'block';
                    } else {
                        settleBankSection.style.display = 'none';
                        settleCashDepositSection.style.display = 'none';
                        settleDepositBalanceInfo.style.display = 'none';
                    }
                });
            }

            // Handle cash deposit selection for settle loan
            if (settleCashDepositSelect) {
                settleCashDepositSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.dataset.balance) {
                        const balance = parseFloat(selectedOption.dataset.balance);
                        const settleAmount = parseFloat(document.getElementById('settle_amount').value);

                        settleSelectedBalance.textContent = 'TSHS ' + balance.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        settleDepositBalanceInfo.style.display = 'block';

                        if (balance < settleAmount) {
                            settleSelectedBalance.classList.remove('text-success');
                            settleSelectedBalance.classList.add('text-danger');
                        } else {
                            settleSelectedBalance.classList.remove('text-danger');
                            settleSelectedBalance.classList.add('text-success');
                        }
                    } else {
                        settleDepositBalanceInfo.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endpush
