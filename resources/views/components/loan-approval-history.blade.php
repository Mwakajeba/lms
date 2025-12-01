@props(['loan'])

<div class="loan-approval-history">
    <h6 class="mb-3">Approval History</h6>

    @if($loan->approvals->count() > 0)
        <div class="timeline">
            @foreach($loan->approvals->sortBy('approval_level') as $approval)
                <div class="timeline-item">
                    <div class="timeline-marker 
                                        @if($approval->action === 'approved') bg-success
                                        @elseif($approval->action === 'rejected') bg-danger
                                        @else bg-info
                                        @endif">
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    Level {{ $approval->approval_level }} -
                                    @if($approval->action === 'approved')
                                        <span class="text-success">Approved</span>
                                    @elseif($approval->action === 'rejected')
                                        <span class="text-danger">Rejected</span>
                                    @else
                                        <span class="text-info">Checked</span>
                                    @endif
                                </h6>
                                <p class="mb-1">
                                    <strong>By:</strong> {{ $approval->user->name }}
                                    <span class="text-muted">({{ $approval->role_name }})</span>
                                </p>
                                <p class="mb-1">
                                    <strong>Date:</strong> {{ $approval->approved_at->format('M d, Y H:i') }}
                                </p>
                                @if($approval->comments)
                                    <p class="mb-0">
                                        <strong>Comments:</strong> {{ $approval->comments }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-muted py-3">
            <i class="fas fa-clock fa-2x mb-2"></i>
            <p>No approval history yet</p>
        </div>
    @endif

    <!-- Current Status -->
    <div class="mt-4">
        <h6>Current Status</h6>
        <div class="d-flex align-items-center">
            <span class="badge 
                @if($loan->status === 'applied') bg-warning
                @elseif($loan->status === 'checked') bg-info
                @elseif($loan->status === 'approved') bg-primary
                @elseif($loan->status === 'authorized') bg-success
                @elseif($loan->status === 'active') bg-success
                @elseif($loan->status === 'rejected') bg-danger
                @elseif($loan->status === 'defaulted') bg-dark
                @else bg-secondary
                @endif fs-6">
                {{ ucfirst($loan->status) }}
            </span>

            @if($loan->product && $loan->product->has_approval_levels)
                <span class="ms-2 text-muted">
                    ({{ $loan->getCurrentApprovalLevel() }}/{{ count($loan->getRequiredApprovalLevels()) }} levels
                    completed)
                </span>
            @endif
        </div>
    </div>

    <!-- Next Required Action -->
    @if($loan->product && $loan->product->has_approval_levels && $loan->status !== 'active' && $loan->status !== 'rejected' && $loan->status !== 'defaulted')
        <div class="mt-3">
            <h6>Next Required Action</h6>
            @php
                $nextLevel = $loan->getNextApprovalLevel();
                $approvalLevels = $loan->getRequiredApprovalLevels();
                $requiredRoleId = $nextLevel ? $approvalLevels[$nextLevel - 1] : null;
                $requiredRole = $requiredRoleId ? \App\Models\Role::find($requiredRoleId) : null;
            @endphp

            @if($nextLevel && $requiredRole)
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Level {{ $nextLevel }}:</strong>
                    {{ ucwords(str_replace('-', ' ', $requiredRole->name)) }} needs to
                    @if($nextLevel === 1) check
                    @elseif($nextLevel === 2) approve
                    @elseif($nextLevel === 3) authorize
                    @else take action on
                    @endif
                    this loan application.
                </div>
            @elseif($loan->status === 'authorized')
                <div class="alert alert-warning">
                    <i class="fas fa-money-bill"></i>
                    <strong>Ready for Disbursement:</strong>
                    An accountant needs to disburse this loan.
                </div>
            @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Fully Approved:</strong>
                    All approval levels have been completed.
                </div>
            @endif
        </div>
    @endif
</div>

<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #e9ecef;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }
</style>