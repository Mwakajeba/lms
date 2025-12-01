@extends('layouts.main')

@section('title', 'Payment Voucher Approval')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Payment Vouchers', 'url' => route('accounting.payment-vouchers.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Payment Voucher #' . $paymentVoucher->reference, 'url' => route('accounting.payment-vouchers.show', $paymentVoucher->hash_id), 'icon' => 'bx bx-show'],
            ['label' => 'Approval', 'url' => '#', 'icon' => 'bx bx-check-shield']
        ]" />
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h6 class="mb-0 text-uppercase">PAYMENT VOUCHER APPROVAL</h6>
                <p class="text-muted mb-0">Review and approve/reject payment voucher</p>
            </div>
            <div>
                <a href="{{ route('accounting.payment-vouchers.show', $paymentVoucher->hash_id) }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-2"></i>Back to Payment Voucher
                </a>
            </div>
        </div>
        <hr />

        <!-- Payment Voucher Summary -->
        <div class="card radius-10 bg-gradient-primary text-white mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-lg bg-white text-primary rounded-circle me-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-receipt font-size-32"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="mb-1">Payment Voucher #{{ $paymentVoucher->reference }}</h3>
                        <p class="mb-0 opacity-75">{{ $paymentVoucher->description ?: 'No description provided' }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-light text-dark">
                            <i class="bx bx-calendar me-1"></i>
                            {{ $paymentVoucher->formatted_date }}
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bx bx-dollar me-1"></i>
                            {{ $paymentVoucher->formatted_amount }}
                        </span>
                        <span class="badge bg-warning">
                            <i class="bx bx-time me-1"></i>
                            Level {{ $currentApproval->approval_level }} Approval Required
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Payment Details -->
            <div class="col-lg-8">
                <!-- Payment Information -->
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date</label>
                                <p class="form-control-plaintext">{{ $paymentVoucher->formatted_date }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Amount</label>
                                <p class="form-control-plaintext fw-bold text-primary">{{ $paymentVoucher->formatted_amount }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Bank Account</label>
                                <p class="form-control-plaintext">{{ $paymentVoucher->bankAccount->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Payee</label>
                                <p class="form-control-plaintext">{{ $paymentVoucher->payee_display_name }}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <p class="form-control-plaintext">{{ $paymentVoucher->description ?: 'No description provided' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

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
                                        <th width="40%">Account</th>
                                        <th width="40%">Description</th>
                                        <th width="20%" class="text-end">Amount</th>
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
                                        <th>Total</th>
                                        <th></th>
                                        <th class="text-end fw-bold">{{ $paymentVoucher->formatted_amount }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Approval Actions -->
            <div class="col-lg-4">
                <!-- Approval Information -->
                <div class="card radius-10 mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bx bx-check-shield me-2"></i>Approval Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Level</label>
                            <p class="form-control-plaintext">Level {{ $currentApproval->approval_level }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Approver Type</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $currentApproval->approver_type === 'role' ? 'info' : 'success' }}">
                                    {{ ucfirst($currentApproval->approver_type) }}
                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Approver</label>
                            <p class="form-control-plaintext">{{ $currentApproval->approver_name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-warning">Pending Approval</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Approval Actions -->
                <div class="card radius-10">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bx bx-check-circle me-2"></i>Approval Actions</h5>
                    </div>
                    <div class="card-body">
                        <!-- Approve Form -->
                        <form action="{{ route('accounting.payment-vouchers.approve', $paymentVoucher->hash_id) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="mb-3">
                                <label for="approve_comments" class="form-label">Comments (Optional)</label>
                                <textarea class="form-control" id="approve_comments" name="comments" rows="3" placeholder="Add any comments for approval..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to approve this payment voucher?')">
                                <i class="bx bx-check-circle me-2"></i>Approve Payment Voucher
                            </button>
                        </form>

                        <hr>

                        <!-- Reject Form -->
                        <form action="{{ route('accounting.payment-vouchers.reject', $paymentVoucher->hash_id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="reject_comments" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="reject_comments" name="comments" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to reject this payment voucher?')">
                                <i class="bx bx-x-circle me-2"></i>Reject Payment Voucher
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Approval History -->
                @if($paymentVoucher->approvals->count() > 0)
                    <div class="card radius-10 mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bx bx-history me-2"></i>Approval History</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                @foreach($paymentVoucher->approvals->sortBy('approval_level') as $approval)
                                    <div class="timeline-item">
                                        <div class="timeline-marker {{ $approval->status === 'approved' ? 'bg-success' : ($approval->status === 'rejected' ? 'bg-danger' : 'bg-warning') }}"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Level {{ $approval->approval_level }} - {{ ucfirst($approval->status) }}</h6>
                                            <p class="mb-1 text-muted">{{ $approval->approver_name }}</p>
                                            @if($approval->approved_at)
                                                <small class="text-muted">{{ $approval->approved_at->format('M d, Y H:i') }}</small>
                                            @endif
                                            @if($approval->comments)
                                                <p class="mb-0 mt-1"><small>{{ $approval->comments }}</small></p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .font-size-32 {
        font-size: 2rem;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -35px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #dee2e6;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        border-left: 3px solid #007bff;
    }
</style>
@endpush 