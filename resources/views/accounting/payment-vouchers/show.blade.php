@extends('layouts.main')

@section('title', 'Payment Voucher Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Payment Vouchers', 'url' => route('accounting.payment-vouchers.index'), 'icon' => 'bx bx-receipt'],
                ['label' => 'Payment Voucher #' . $paymentVoucher->reference, 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h6 class="mb-0 text-uppercase">PAYMENT VOUCHER DETAILS</h6>
                <p class="text-muted mb-0">View payment voucher information</p>
            </div>
            <div>
                <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-2"></i>Back to Payment Vouchers
                </a>
                <a href="{{ route('accounting.payment-vouchers.export-pdf', $paymentVoucher->hash_id) }}" class="btn btn-outline-danger">
                    <i class="bx bx-file me-1"></i>Export PDF
                </a>
            </div>
        </div>
        <hr />

        <!-- Approval Status Notice -->
        @if($paymentVoucher->reference_type === 'manual' && $paymentVoucher->isFullyApproved())
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-lock font-size-24 me-3"></i>
                <div>
                    <h6 class="alert-heading mb-1">Payment Voucher Locked</h6>
                    <p class="mb-0">This payment voucher has been approved and is now locked for editing. You can view details and download attachments, but modifications are not allowed to maintain data integrity.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @elseif($paymentVoucher->reference_type === 'manual' && $paymentVoucher->requiresApproval() && !$paymentVoucher->isFullyApproved())
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-time font-size-24 me-3"></i>
                <div>
                    <h6 class="alert-heading mb-1">Approval Required</h6>
                    <p class="mb-0">This payment voucher requires approval before it can be processed. The approval workflow is currently in progress.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Prominent Header Card -->
        <div class="card radius-10 bg-gradient-danger text-white mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div
                        class="avatar-lg bg-white text-danger rounded-circle me-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-receipt font-size-32"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1">Payment Voucher #{{ $paymentVoucher->reference }}</h3>
                        <p class="mb-0 opacity-75">{{ $paymentVoucher->description ?: 'No description provided' }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        {!! $paymentVoucher->status_badge !!}
                        <span class="badge bg-light text-dark">
                            <i class="bx bx-calendar me-1"></i>
                            {{ $paymentVoucher->formatted_date }}
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bx bx-dollar me-1"></i>
                            {{ $paymentVoucher->formatted_amount }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Main Information -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date</label>
                                <p class="form-control-plaintext">{{ $paymentVoucher->formatted_date }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Reference</label>
                                <p class="form-control-plaintext">{{ $paymentVoucher->reference }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Bank Account</label>
                                <p class="form-control-plaintext">{{ $paymentVoucher->bankAccount->name ?? 'N/A' }} -
                                    {{ $paymentVoucher->bankAccount->account_number ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Payee Type</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-{{ $paymentVoucher->payee_type == 'customer' ? 'primary' : ($paymentVoucher->payee_type == 'supplier' ? 'success' : 'warning') }}">
                                        {{ ucfirst($paymentVoucher->payee_type ?? 'N/A') }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Payee</label>
                                <p class="form-control-plaintext">
                                    @if($paymentVoucher->payee_type == 'customer' && $paymentVoucher->customer)
                                    {{ $paymentVoucher->customer->name ?? 'N/A' }}
                                    ({{ $paymentVoucher->customer->customerNo ?? 'N/A' }})
                                    @elseif($paymentVoucher->payee_type == 'supplier' && $paymentVoucher->supplier)
                                    {{ $paymentVoucher->supplier->name ?? 'N/A' }}
                                    @elseif($paymentVoucher->payee_type == 'other')
                                    {{ $paymentVoucher->payee_name ?? 'N/A' }}
                                    @else
                                    <span class="text-muted">No payee selected</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <p class="form-control-plaintext">
                                    {{ $paymentVoucher->description ?: 'No description provided' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval Status -->
                @if($paymentVoucher->requiresApproval())
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bx bx-check-shield me-2"></i>Approval Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Current Status</label>
                                <div class="mt-2">
                                    {!! $paymentVoucher->approval_status_badge !!}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Required Approval Level</label>
                                <p class="form-control-plaintext">
                                    Level {{ $paymentVoucher->getRequiredApprovalLevel() }}
                                </p>
                            </div>
                        </div>

                        @if($paymentVoucher->approvals->count() > 0)
                        <div class="mt-3">
                            <h6 class="mb-3">Approval History</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Level</th>
                                            <th>Approver</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentVoucher->approvals->sortBy('approval_level') as $approval)
                                        <tr>
                                            <td>Level {{ $approval->approval_level }}</td>
                                            <td>
                                                @if($approval->approver_type === 'role')
                                                <span class="badge bg-info">{{ $approval->approver_name }}</span>
                                                @else
                                                {{ $approval->approver_name }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($approval->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                                @elseif($approval->status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                                @elseif($approval->status === 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                                @elseif($approval->status === 'escalated')
                                                <span class="badge bg-secondary">Escalated</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($approval->approved_at)
                                                {{ $approval->approved_at->format('M d, Y H:i') }}
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $approval->comments ?: '-' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Line Items -->
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Line Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="35%">Account</th>
                                        <th width="35%">Description</th>
                                        <th width="30%" class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($paymentVoucher->paymentItems as $item)
                                    <tr>
                                        <td>{{ $item->chartAccount->account_name ?? 'N/A' }}
                                            ({{ $item->chartAccount->account_code ?? 'N/A' }})</td>
                                        <td>{{ $item->description ?: 'No description' }}</td>
                                        <td class="text-end">{{ $item->formatted_amount }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No line items found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th></th>
                                        <th class="text-end fw-bold">
                                            {{ number_format($paymentVoucher->total_amount, 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- GL Transactions -->
                @if($paymentVoucher->glTransactions->count() > 0)
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bx bx-book me-2"></i>General Ledger Entries</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40%">Account</th>
                                        <th width="30%" class="text-end">Debit</th>
                                        <th width="30%" class="text-end">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                    $totalDebitGL = 0;
                                    $totalCreditGL = 0;
                                    @endphp
                                    @foreach($paymentVoucher->glTransactions as $glTransaction)
                                    <tr>
                                        <td>{{ $glTransaction->chartAccount->account_name ?? 'N/A' }}
                                            ({{ $glTransaction->chartAccount->account_code ?? 'N/A' }})</td>
                                        <td class="text-end">
                                            @if($glTransaction->nature === 'debit')
                                            @php $totalDebitGL += $glTransaction->amount; @endphp
                                            {{ number_format($glTransaction->amount, 2) }}
                                            @else
                                            -
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($glTransaction->nature === 'credit')
                                            @php $totalCreditGL += $glTransaction->amount; @endphp
                                            {{ number_format($glTransaction->amount, 2) }}
                                            @else
                                            -
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-end fw-bold">{{ number_format($totalDebitGL, 2) }}</th>
                                        <th class="text-end fw-bold">{{ number_format($totalCreditGL, 2) }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column - Sidebar Information -->
            <div class="col-lg-4">
                <!-- Organization Information -->
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bx bx-building me-2"></i>Organization</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bx bx-building me-2"></i>Company
                            </label>
                            <p class="form-control-plaintext">
                                @if($paymentVoucher->customer && $paymentVoucher->customer->company)
                                {{ $paymentVoucher->customer->company->name ?? 'N/A' }}
                                @else
                                <span class="text-muted">No company information</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bx bx-map-pin me-2"></i>Branch
                            </label>
                            <p class="form-control-plaintext">{{ $paymentVoucher->branch->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Audit Information -->
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bx bx-history me-2"></i>Audit Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bx bx-user me-2"></i>Created By
                            </label>
                            <p class="form-control-plaintext">{{ $paymentVoucher->user->name ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bx bx-calendar me-2"></i>Created Date
                            </label>
                            <p class="form-control-plaintext">
                                {{ $paymentVoucher->created_at ? $paymentVoucher->created_at->format('M d, Y H:i A') : 'N/A' }}
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bx bx-time me-2"></i>Last Updated
                            </label>
                            <p class="form-control-plaintext">
                                {{ $paymentVoucher->updated_at ? $paymentVoucher->updated_at->format('M d, Y H:i A') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card radius-10">
                    <div class="card-header bg-light text-white">
                        <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            @if($paymentVoucher->reference_type === 'manual')
                            @can('edit payment voucher')
                            <a href="{{ route('accounting.payment-vouchers.edit', $paymentVoucher->hash_id) }}"
                                class="btn btn-primary">
                                <i class="bx bx-edit me-1"></i>Edit
                            </a>
                            @endcan
                            @can('view payment vouchers')
                            <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                            @endcan
                            @if($paymentVoucher->attachment)
                            <a href="{{ route('accounting.payment-vouchers.download-attachment', $paymentVoucher->hash_id) }}"
                                class="btn btn-info">
                                <i class="bx bx-download me-1"></i>Download Attachment
                            </a>
                            @endif

                            <!-- Approval Buttons -->
                            @if($paymentVoucher->requiresApproval() && !$paymentVoucher->isFullyApproved() && !$paymentVoucher->isRejected())
                            @php
                            $currentApproval = $paymentVoucher->currentApproval();
                            $settings = \App\Models\PaymentVoucherApprovalSetting::where('company_id', auth()->user()->company_id)->first();
                            @endphp
                            @if($currentApproval && $settings && $settings->canUserApproveAtLevel(auth()->user(), $currentApproval->approval_level))
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approvalModal">
                                <i class="bx bx-check-shield me-1"></i>Approve/Reject
                            </button>
                            @endif
                            @endif

                            @can('delete payment voucher')
                            <button type="button" class="btn btn-outline-danger" onclick="deletePaymentVoucher()">
                                <i class="bx bx-trash me-1"></i>Delete
                            </button>
                            @endcan
                            <a href="{{ route('accounting.payment-vouchers.export-pdf', $paymentVoucher->hash_id) }}" class="btn btn-outline-danger">
                                <i class="bx bx-file me-1"></i>Export PDF
                            </a>
                            @else
                            <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                            @if($paymentVoucher->attachment)
                            <a href="{{ route('accounting.payment-vouchers.download-attachment', $paymentVoucher->hash_id) }}"
                                class="btn btn-info">
                                <i class="bx bx-download me-1"></i>Download Attachment
                            </a>
                            @endif
                            <button type="button" class="btn btn-outline-secondary" title="Edit/Delete locked: Source is {{ ucfirst($paymentVoucher->reference_type) }} transaction" disabled>
                                <i class="bx bx-lock"></i> Locked
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Attachment Section -->
                @if($paymentVoucher->attachment)
                <div class="card radius-10 mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bx bx-paperclip me-2"></i>Attachment</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $paymentVoucher->attachment_name }}</h6>
                                <p class="text-muted mb-0">
                                    <i class="bx bx-file-pdf me-1"></i>
                                    PDF document uploaded with this payment voucher
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('accounting.payment-vouchers.download-attachment', $paymentVoucher->hash_id) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="bx bx-download me-1"></i>Download
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAttachment()">
                                    <i class="bx bx-trash me-1"></i>Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="card radius-10 mt-4">
                    <div class="card-header bg-light text-white">
                        <h5 class="mb-0"><i class="bx bx-paperclip me-2"></i>Attachment</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="py-4">
                            <i class="bx bx-file-pdf font-size-48 text-muted mb-3"></i>
                            <h6 class="text-muted">No PDF attachment uploaded</h6>
                            <p class="text-muted mb-0">You can add a PDF attachment when editing this payment voucher.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
@if($paymentVoucher->requiresApproval() && !$paymentVoucher->isFullyApproved() && !$paymentVoucher->isRejected())
@php
$currentApproval = $paymentVoucher->currentApproval();
$settings = \App\Models\PaymentVoucherApprovalSetting::where('company_id', auth()->user()->company_id)->first();
@endphp
@if($currentApproval && $settings && $settings->canUserApproveAtLevel(auth()->user(), $currentApproval->approval_level))
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approvalModalLabel">
                    <i class="bx bx-check-shield me-2"></i>Payment Voucher Approval
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Payment Summary -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Payment Details</h6>
                        <p class="mb-1"><strong>Reference:</strong> {{ $paymentVoucher->reference }}</p>
                        <p class="mb-1"><strong>Amount:</strong> <span class="text-primary fw-bold">{{ $paymentVoucher->formatted_amount }}</span></p>
                        <p class="mb-1"><strong>Date:</strong> {{ $paymentVoucher->formatted_date }}</p>
                        <p class="mb-1"><strong>Payee:</strong> {{ $paymentVoucher->payee_display_name }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Approval Information</h6>
                        <p class="mb-1"><strong>Level:</strong> <span class="badge bg-warning">Level {{ $currentApproval->approval_level }}</span></p>
                        <p class="mb-1"><strong>Approver:</strong> {{ $currentApproval->approver_name }}</p>
                        <p class="mb-1"><strong>Type:</strong>
                            <span class="badge bg-{{ $currentApproval->approver_type === 'role' ? 'info' : 'success' }}">
                                {{ ucfirst($currentApproval->approver_type) }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Line Items Summary -->
                <div class="mb-4">
                    <h6 class="fw-bold">Line Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Account</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentVoucher->paymentItems as $item)
                                <tr>
                                    <td>{{ $item->chartAccount->account_name ?? 'N/A' }}</td>
                                    <td>{{ $item->description ?: 'No description' }}</td>
                                    <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No line items found</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th class="text-end fw-bold">{{ $paymentVoucher->formatted_amount }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Approval Actions -->
                <div class="row">
                    <div class="col-md-6">
                        <!-- Approve Form -->
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="bx bx-check-circle me-2"></i>Approve</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="approve_comments" class="form-label">Comments (Optional)</label>
                                    <textarea class="form-control" id="approve_comments" rows="3" placeholder="Add any comments for approval..."></textarea>
                                </div>
                                <button type="button" class="btn btn-success w-100" onclick="confirmApproval('approve')">
                                    <i class="bx bx-check-circle me-2"></i>Approve Payment Voucher
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Reject Form -->
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0"><i class="bx bx-x-circle me-2"></i>Reject</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="reject_comments" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="reject_comments" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                                </div>
                                <button type="button" class="btn btn-danger w-100" onclick="confirmApproval('reject')">
                                    <i class="bx bx-x-circle me-2"></i>Reject Payment Voucher
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Approval History -->
                @if($paymentVoucher->approvals->count() > 0)
                <div class="mt-4">
                    <h6 class="fw-bold">Approval History</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Level</th>
                                    <th>Approver</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentVoucher->approvals->sortBy('approval_level') as $approval)
                                <tr>
                                    <td>Level {{ $approval->approval_level }}</td>
                                    <td>
                                        @if($approval->approver_type === 'role')
                                        <span class="badge bg-info">{{ $approval->approver_name }}</span>
                                        @else
                                        {{ $approval->approver_name }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($approval->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                        @elseif($approval->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                        @elseif($approval->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                        @elseif($approval->status === 'escalated')
                                        <span class="badge bg-secondary">Escalated</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($approval->approved_at)
                                        {{ $approval->approved_at->format('M d, Y H:i') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $approval->comments ?: '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endif
@endsection

@push('scripts')
<script>
    function deletePaymentVoucher() {
        Swal.fire({
            title: 'Delete Payment Voucher',
            text: 'Are you sure you want to delete this payment voucher? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('<form>', {
                    'method': 'POST',
                    'action': '{{ route("accounting.payment-vouchers.destroy", $paymentVoucher->hash_id) }}'
                });

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': '{{ csrf_token() }}'
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_method',
                    'value': 'DELETE'
                }));

                $('body').append(form);
                form.submit();
            }
        });
    }

    function deleteAttachment() {
        Swal.fire({
            title: 'Remove Attachment',
            text: 'Are you sure you want to remove this attachment? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form to submit DELETE request
                const form = $('<form>', {
                    'method': 'POST',
                    'action': '{{ route("accounting.payment-vouchers.remove-attachment", $paymentVoucher->hash_id) }}'
                });

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': '{{ csrf_token() }}'
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_method',
                    'value': 'DELETE'
                }));

                $('body').append(form);
                form.submit();
            }
        });
    }

    // Approval confirmation function
    function confirmApproval(action) {
        const actionText = action === 'approve' ? 'approve' : 'reject';
        const actionColor = action === 'approve' ? '#28a745' : '#dc3545';
        const actionIcon = action === 'approve' ? 'success' : 'warning';

        // Get comments from the appropriate textarea
        const commentsField = action === 'approve' ? 'approve_comments' : 'reject_comments';
        const comments = document.getElementById(commentsField).value.trim();

        // Validate rejection reason is required
        if (action === 'reject' && !comments) {
            Swal.fire({
                title: 'Rejection Reason Required',
                text: 'Please provide a reason for rejection before proceeding.',
                icon: 'warning',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'OK'
            });
            return;
        }

        return Swal.fire({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} Payment Voucher`,
            text: `Are you sure you want to ${action} this payment voucher? This action cannot be undone.`,
            icon: actionIcon,
            showCancelButton: true,
            confirmButtonColor: actionColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${action} it!`,
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Processing...',
                    text: `Please wait while we ${action} the payment voucher.`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Prepare data for AJAX request
                const data = {
                    _token: '{{ csrf_token() }}',
                    comments: comments
                };

                // Make AJAX request
                const url = action === 'approve' ?
                    '{{ route("accounting.payment-vouchers.approve", $paymentVoucher->hash_id) }}' :
                    '{{ route("accounting.payment-vouchers.reject", $paymentVoucher->hash_id) }}';

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message || `Payment voucher ${action}d successfully.`,
                                icon: 'success',
                                confirmButtonColor: '#28a745',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reload the page to show updated status
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || `Failed to ${action} payment voucher.`,
                                icon: 'error',
                                confirmButtonColor: '#dc3545',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'OK'
                        });
                    });
            }
        });
    }

    // Handle form submission success/error
    @if(session('success'))
    Swal.fire({
        title: 'Success!',
        text: '{{ session("success") }}',
        icon: 'success',
        confirmButtonColor: '#28a745'
    }).then(() => {
        // Reload the page to show updated status
        window.location.reload();
    });
    @endif

    @if(session('error'))
    Swal.fire({
        title: 'Error!',
        text: '{{ session("error") }}',
        icon: 'error',
        confirmButtonColor: '#dc3545'
    });
    @endif
</script>
@endpush

@push('styles')
<style>
    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    }

    .font-size-32 {
        font-size: 2rem;
    }
</style>
@endpush