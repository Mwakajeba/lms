@extends('layouts.main')

@section('title', 'Pending Payment Voucher Approvals')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Payment Vouchers', 'url' => route('accounting.payment-vouchers.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Pending Approvals', 'url' => '#', 'icon' => 'bx bx-check-shield']
        ]" />
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h6 class="mb-0 text-uppercase">PENDING PAYMENT VOUCHER APPROVALS</h6>
                <p class="text-muted mb-0">Review and approve/reject pending payment vouchers</p>
            </div>
            <div>
                <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-2"></i>Back to Payment Vouchers
                </a>
            </div>
        </div>
        <hr />

        @if($pendingApprovals->count() > 0)
            <div class="row">
                @foreach($pendingApprovals as $approval)
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card radius-10 h-100">
                            <div class="card-header bg-warning text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="bx bx-check-shield me-2"></i>
                                        Level {{ $approval->approval_level }} Approval Required
                                    </h6>
                                    <span class="badge bg-light text-dark">
                                        {{ $approval->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="card-title mb-1">
                                        Payment Voucher #{{ $approval->payment->reference }}
                                    </h6>
                                    <p class="text-muted mb-2">
                                        {{ Str::limit($approval->payment->description, 100) ?: 'No description' }}
                                    </p>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Amount</small>
                                        <p class="mb-0 fw-bold text-primary">
                                            {{ number_format($approval->payment->amount, 2) }}
                                        </p>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Date</small>
                                        <p class="mb-0">{{ $approval->payment->formatted_date }}</p>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Payee</small>
                                    <p class="mb-0">{{ $approval->payment->payee_display_name }}</p>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Bank Account</small>
                                    <p class="mb-0">{{ $approval->payment->bankAccount->name ?? 'N/A' }}</p>
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Created By</small>
                                    <p class="mb-0">{{ $approval->payment->user->name ?? 'N/A' }}</p>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#approvalModal{{ $approval->id }}">
                                        <i class="bx bx-check-circle me-1"></i>Review & Approve
                                    </button>
                                    <a href="{{ route('accounting.payment-vouchers.show', $approval->payment->hash_id) }}" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="bx bx-show me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $pendingApprovals->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="bx bx-check-shield font-size-48 text-muted"></i>
                </div>
                <h5 class="text-muted">No Pending Approvals</h5>
                <p class="text-muted mb-4">You don't have any payment vouchers waiting for your approval.</p>
                <a href="{{ route('accounting.payment-vouchers.index') }}" class="btn btn-primary">
                    <i class="bx bx-arrow-back me-2"></i>Back to Payment Vouchers
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Approval Modals for each pending approval -->
@foreach($pendingApprovals as $approval)
    <div class="modal fade" id="approvalModal{{ $approval->id }}" tabindex="-1" aria-labelledby="approvalModalLabel{{ $approval->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="approvalModalLabel{{ $approval->id }}">
                        <i class="bx bx-check-shield me-2"></i>Payment Voucher Approval
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Payment Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold">Payment Details</h6>
                            <p class="mb-1"><strong>Reference:</strong> {{ $approval->payment->reference }}</p>
                            <p class="mb-1"><strong>Amount:</strong> <span class="text-primary fw-bold">{{ number_format($approval->payment->amount, 2) }}</span></p>
                            <p class="mb-1"><strong>Date:</strong> {{ $approval->payment->formatted_date }}</p>
                            <p class="mb-1"><strong>Payee:</strong> {{ $approval->payment->payee_display_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Approval Information</h6>
                            <p class="mb-1"><strong>Level:</strong> <span class="badge bg-warning">Level {{ $approval->approval_level }}</span></p>
                            <p class="mb-1"><strong>Approver:</strong> {{ $approval->approver_name }}</p>
                            <p class="mb-1"><strong>Type:</strong> 
                                <span class="badge bg-{{ $approval->approver_type === 'role' ? 'info' : 'success' }}">
                                    {{ ucfirst($approval->approver_type) }}
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
                                    @forelse($approval->payment->paymentItems as $item)
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
                                        <th class="text-end fw-bold">{{ number_format($approval->payment->amount, 2) }}</th>
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
                                        <label for="approve_comments_{{ $approval->id }}" class="form-label">Comments (Optional)</label>
                                        <textarea class="form-control" id="approve_comments_{{ $approval->id }}" rows="3" placeholder="Add any comments for approval..."></textarea>
                                    </div>
                                    <button type="button" class="btn btn-success w-100" onclick="confirmApproval('approve', {{ $approval->id }})">
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
                                        <label for="reject_comments_{{ $approval->id }}" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="reject_comments_{{ $approval->id }}" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                                    </div>
                                    <button type="button" class="btn btn-danger w-100" onclick="confirmApproval('reject', {{ $approval->id }})">
                                        <i class="bx bx-x-circle me-2"></i>Reject Payment Voucher
                                        </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

@push('styles')
<style>
    .font-size-48 {
        font-size: 3rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Approval confirmation function
    function confirmApproval(action, approvalId) {
        const actionText = action === 'approve' ? 'approve' : 'reject';
        const actionColor = action === 'approve' ? '#28a745' : '#dc3545';
        const actionIcon = action === 'approve' ? 'success' : 'warning';
        
        // Get comments from the appropriate textarea
        const commentsField = action === 'approve' ? `approve_comments_${approvalId}` : `reject_comments_${approvalId}`;
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
                const url = action === 'approve' 
                    ? '{{ route("accounting.payment-vouchers.approve", ":hash_id") }}'.replace(':hash_id', '{{ $approval->payment->hash_id }}')
                    : '{{ route("accounting.payment-vouchers.reject", ":hash_id") }}'.replace(':hash_id', '{{ $approval->payment->hash_id }}');
                
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