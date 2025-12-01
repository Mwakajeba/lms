@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Loan Application Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
                ['label' => 'Loan Applications', 'url' => route('loans.application.index'), 'icon' => 'bx bx-file-plus'],
                ['label' => 'Application Details', 'url' => '#', 'icon' => 'bx bx-show'],    
            ]" />
            
            <!-- Header with Status and Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Loan Application #{{ $loanApplication->id }}</h4>
                    <p class="text-muted mb-0">{{ $loanApplication->customer->name ?? 'Unknown Customer' }}</p>
                </div>
                @can('edit loan')
                <div class="d-flex gap-2">
                    <a href="{{ route('loans.application.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back
                    </a>
                    @if($loanApplication->status === 'pending')
                        <a href="{{ route('loans.application.edit', Hashids::encode($loanApplication->id)) }}" class="btn btn-warning">
                            <i class="bx bx-edit me-1"></i> Edit
                        </a>
                    @endif
                </div>
                @endcan
            </div>

            <!-- Status Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg me-3">
                                            <i class="bx bx-user-circle fs-1 text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $loanApplication->customer->name ?? 'Unknown Customer' }}</h6>
                                            <small class="text-muted">{{ $loanApplication->customer->phone ?? 'No phone' }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center">
                                    <h5 class="mb-1 text-primary">TZS {{ number_format($loanApplication->amount, 2) }}</h5>
                                    <small class="text-muted">Principal Amount</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <h5 class="mb-1 text-warning">{{ $loanApplication->interest ?? 'N/A' }}%</h5>
                                    <small class="text-muted">Interest Rate</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="mb-1">
                                        @switch($loanApplication->status)
                                            @case('pending')
                                                <span class="badge bg-warning fs-6">PENDING</span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-success fs-6">APPROVED</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger fs-6">REJECTED</span>
                                                @break
                                            @case('active')
                                                <span class="badge bg-primary fs-6">ACTIVE</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary fs-6">{{ strtoupper($loanApplication->status) }}</span>
                                        @endswitch
                                    </div>
                                    <small class="text-muted">Application Status</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <ul class="nav nav-tabs card-header-tabs" id="loanTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
                                <i class="bx bx-info-circle me-1"></i> Loan Details
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                                <i class="bx bx-file me-1"></i> Documents
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="guarantors-tab" data-bs-toggle="tab" data-bs-target="#guarantors" type="button" role="tab">
                                <i class="bx bx-user-check me-1"></i> Guarantors
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="collaterals-tab" data-bs-toggle="tab" data-bs-target="#collaterals" type="button" role="tab">
                                <i class="bx bx-shield me-1"></i> Collaterals
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">
                                <i class="bx bx-calendar me-1"></i> Repayment Schedule
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="loanTabsContent">
                        <!-- Loan Details Tab -->
                        <div class="tab-pane fade show active" id="details" role="tabpanel">
                            <!-- LOAN DETAILS Table -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>LOAN DETAILS</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold bg-light" style="width: 30%;">Loan Status</td>
                                                    <td>
                                                        @switch($loanApplication->status)
                                                            @case('applied')
                                                                <span class="badge bg-warning">APPLIED</span>
                                                                @break
                                                            @case('checked')
                                                                <span class="badge bg-info">CHECKED</span>
                                                                @break
                                                            @case('approved')
                                                                <span class="badge bg-primary">APPROVED</span>
                                                                @break
                                                            @case('authorized')
                                                                <span class="badge bg-success">AUTHORIZED</span>
                                                                @break
                                                            @case('active')
                                                                <span class="badge bg-success">ACTIVE</span>
                                                                @break
                                                            @case('rejected')
                                                                <span class="badge bg-danger">REJECTED</span>
                                                                @break
                                                            @case('defaulted')
                                                                <span class="badge bg-dark">DEFAULTED</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ strtoupper($loanApplication->status) }}</span>
                                                        @endswitch
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Loan Type</td>
                                                    <td>{{ $loanApplication->product->name ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Principal Amount</td>
                                                    <td class="fw-bold text-primary">TZS {{ number_format($loanApplication->amount, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Interest Amount</td>
                                                    <td>TZS {{ number_format($loanApplication->interest_amount ?? 0, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Interest Rate</td>
                                                    <td class="fw-bold text-warning">{{ $loanApplication->interest ?? 'N/A' }}%</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Contract Requirement</td>
                                                    <td><span class="badge bg-danger">CONTRACT REQUIRED</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Contract Status</td>
                                                    <td><span class="badge bg-warning">CONTRACT NOT UPLOADED</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Guarantors Required</td>
                                                    <td><span class="badge bg-info">2 REQUIRED</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Guarantors Uploaded</td>
                                                    <td><span class="badge bg-warning">0 UPLOADED (MISSING 2)</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Loan Term</td>
                                                    <td>{{ $loanApplication->period }} {{ $loanApplication->getPeriodUnit() }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Disbursement Date</td>
                                                    <td>{{ $loanApplication->disbursed_on ? \Carbon\Carbon::parse($loanApplication->disbursed_on)->format('d-m-Y') : 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">First Repayment Date</td>
                                                    <td>{{ $loanApplication->first_repayment_date ? \Carbon\Carbon::parse($loanApplication->first_repayment_date)->format('d-m-Y') : 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Last Repayment Date</td>
                                                    <td>{{ $loanApplication->last_repayment_date ? \Carbon\Carbon::parse($loanApplication->last_repayment_date)->format('d-m-Y') : 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Principal & Interest</td>
                                                    <td class="fw-bold">TZS {{ number_format($loanApplication->amount_total ?? 0, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Repayment Installment</td>
                                                    <td class="fw-bold">TZS {{ number_format(($loanApplication->amount_total ?? 0) / ($loanApplication->period ?? 1), 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Total Repayments</td>
                                                    <td class="fw-bold text-success">TZS 0.00</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Outstanding Balance</td>
                                                    <td class="fw-bold text-success">TZS 0.00</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold bg-light">Disbursement Amount</td>
                                                    <td class="fw-bold text-danger">TZS {{ number_format($loanApplication->amount, 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        
                            <!-- COLLATERAL BALANCE Table -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bx bx-shield me-2"></i>COLLATERAL BALANCE</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Collateral Type</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($loanApplication->customer && $loanApplication->customer->getCashCollateralBalanceAttribute())
                                                    <tr>
                                                        <td>1</td>
                                                        <td>Cash</td>
                                                        <td class="fw-bold">{{ number_format($loanApplication->customer->getCashCollateralBalanceAttribute(), 2) }}</td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">
                                                            <i class="bx bx-shield-x fs-1 mb-3"></i>
                                                            <h6>No Collateral Balance</h6>
                                                            <p>Customer has no collateral balance recorded.</p>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Loan Application Actions -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bx bx-cog me-2"></i>LOAN APPROVAL ACTIONS</h6>
                                </div>
                                <div class="card-body">
                                    <x-loan-approval-actions :loan="$loanApplication" />
                                </div>
                            </div>

                            <!-- Approval History -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bx bx-history me-2"></i>APPROVAL HISTORY</h6>
                                </div>
                                <div class="card-body">
                                    <x-loan-approval-history :loan="$loanApplication" />
                                </div>
                            </div>
                        </div>

                        <!-- Documents Tab -->
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            @can('manage loan documents')
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Loan Documents</h5>
                                <button class="btn btn-primary" onclick="addDocument()">
                                    <i class="bx bx-plus me-1"></i> Add Document
                                </button>
                            </div>
                            @endcan
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="text-center py-5">
                                        <i class="bx bx-file-plus fs-1 text-muted mb-3"></i>
                                        <h6 class="text-muted">No documents uploaded yet</h6>
                                        <p class="text-muted">Upload loan documents such as contracts, ID copies, and other required paperwork.</p>
                                        <button class="btn btn-primary" onclick="addDocument()">
                                            <i class="bx bx-upload me-1"></i> Upload First Document
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Guarantors Tab -->
                        <div class="tab-pane fade" id="guarantors" role="tabpanel">
                            @can('add addGuarantor')
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Loan Guarantors</h5>
                                <button class="btn btn-primary" onclick="addGuarantor()">
                                    <i class="bx bx-plus me-1"></i> Add Guarantor
                                </button>
                            </div>
                            @endcan
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="text-center py-5">
                                        <i class="bx bx-user-plus fs-1 text-muted mb-3"></i>
                                        <h6 class="text-muted">No guarantors added yet</h6>
                                        <p class="text-muted">Add guarantors for this loan application to meet the loan requirements.</p>
                                        <button class="btn btn-primary" onclick="addGuarantor()">
                                            <i class="bx bx-user-plus me-1"></i> Add First Guarantor
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Collaterals Tab -->
                        <div class="tab-pane fade" id="collaterals" role="tabpanel">
                            @can('manage loan collateral')
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Loan Collaterals</h5>
                                <button class="btn btn-primary" onclick="addCollateral()">
                                    <i class="bx bx-plus me-1"></i> Add Collateral
                                </button>
                            </div>
                            @endcan
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="text-center py-5">
                                        <i class="bx bx-shield-plus fs-1 text-muted mb-3"></i>
                                        <h6 class="text-muted">No collaterals added yet</h6>
                                        <p class="text-muted">Add collaterals for this loan application to secure the loan.</p>
                                        <button class="btn btn-primary" onclick="addCollateral()">
                                            <i class="bx bx-shield-plus me-1"></i> Add First Collateral
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Repayment Schedule Tab -->
                        <div class="tab-pane fade" id="schedule" role="tabpanel">
                            @can('generate loan schedule')
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Repayment Schedule</h5>
                                <button class="btn btn-outline-primary" onclick="generateSchedule()">
                                    <i class="bx bx-refresh me-1"></i> Generate Schedule
                                </button>
                            </div>
                            @endcan
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="text-center py-5">
                                        <i class="bx bx-calendar-plus fs-1 text-muted mb-3"></i>
                                        <h6 class="text-muted">No repayment schedule generated yet</h6>
                                        <p class="text-muted">Generate the repayment schedule for this loan application.</p>
                                        <button class="btn btn-primary" onclick="generateSchedule()">
                                            <i class="bx bx-calendar-plus me-1"></i> Generate Schedule
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approvalModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="approvalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="approvalForm" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        padding: 0.75rem 1.5rem;
    }
    
    .nav-tabs .nav-link.active {
        color: #007bff;
        background: none;
        border-bottom: 3px solid #007bff;
    }
    
    .nav-tabs .nav-link:hover {
        border: none;
        color: #007bff;
    }
    
    .card {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
        font-weight: 600;
    }
    
    .avatar {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
    }
    
    .avatar-lg {
        width: 4rem;
        height: 4rem;
    }
    
    .badge {
        font-size: 0.75em;
        font-weight: 500;
    }
    
    .form-label {
        font-weight: 500;
    }
    
    .fw-bold {
        font-weight: 600 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function approveApplication(applicationId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');
        
        message.textContent = 'Are you sure you want to approve this loan application? This will create an active loan.';
        form.action = `/loans/${applicationId}/approve`;
        
        modal.show();
    }

    function rejectApplication(applicationId) {
        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        const message = document.getElementById('approvalMessage');
        const form = document.getElementById('approvalForm');
        
        message.textContent = 'Are you sure you want to reject this loan application?';
        form.action = `/loans/${applicationId}/reject`;
        
        modal.show();
    }

    function deleteApplication(applicationId) {
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
                form.action = `/loans/application/${applicationId}`;
                
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

    function addDocument() {
        Swal.fire({
            title: 'Add Document',
            text: 'Document upload functionality will be implemented here.',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    }

    function addGuarantor() {
        Swal.fire({
            title: 'Add Guarantor',
            text: 'Guarantor management functionality will be implemented here.',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    }

    function addCollateral() {
        Swal.fire({
            title: 'Add Collateral',
            text: 'Collateral management functionality will be implemented here.',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    }

    function generateSchedule() {
        Swal.fire({
            title: 'Generate Schedule',
            text: 'Repayment schedule generation functionality will be implemented here.',
            icon: 'info',
            confirmButtonText: 'OK'
        });
    }
</script>
@endpush 