@props(['loan'])

@php
    $user = auth()->user();
    $canCheck = $loan->status === 'applied' && $loan->canBeApprovedByUser($user);
    $canApprove = $loan->status === 'checked' && $loan->canBeApprovedByUser($user);
    $canAuthorize = $loan->status === 'approved' && $loan->canBeApprovedByUser($user);
    $canDisburse = $loan->status === 'authorized' && $user->hasRole('accountant');
    $canReject = $loan->canBeRejected() && $loan->canBeApprovedByUser($user);
    $canDefault = $loan->status === 'active';
@endphp

<div class="loan-approval-actions">
    @if($canCheck)
        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
            data-bs-target="#checkLoanModal{{ $loan->id }}">
            <i class="fas fa-check"></i> Check
        </button>
    @endif

    @if($canApprove)
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
            data-bs-target="#approveLoanModal{{ $loan->id }}">
            <i class="fas fa-thumbs-up"></i> Approve
        </button>
    @endif

    @if($canAuthorize)
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
            data-bs-target="#authorizeLoanModal{{ $loan->id }}">
            <i class="fas fa-key"></i> Authorize
        </button>
    @endif

    @if($canDisburse)
        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
            data-bs-target="#disburseLoanModal{{ $loan->id }}">
            <i class="fas fa-money-bill"></i> Disburse
        </button>
    @endif

    @if($canReject)
        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
            data-bs-target="#rejectLoanModal{{ $loan->id }}">
            <i class="fas fa-times"></i> Reject
        </button>
    @endif

    @if($canDefault)
        <button type="button" class="btn btn-dark btn-sm" data-bs-toggle="modal"
            data-bs-target="#defaultLoanModal{{ $loan->id }}">
            <i class="fas fa-exclamation-triangle"></i> Mark Defaulted
        </button>
    @endif

    @if(!$canCheck && !$canApprove && !$canAuthorize && !$canDisburse && !$canReject && !$canDefault)
        <span class="text-muted">No actions available</span>
    @endif
</div>

<!-- Check Loan Modal -->
@if($canCheck)
    <div class="modal fade" id="checkLoanModal{{ $loan->id }}" tabindex="-1"
        aria-labelledby="checkLoanModalLabel{{ $loan->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('loans.check', Hashids::encode($loan->id)) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="checkLoanModalLabel{{ $loan->id }}">Check Loan Application</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to check this loan application?</p>
                        <div class="mb-3">
                            <label for="comments" class="form-label">Comments (Optional)</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3"
                                placeholder="Add any comments..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Check Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Approve Loan Modal -->
@if($canApprove)
    <div class="modal fade" id="approveLoanModal{{ $loan->id }}" tabindex="-1"
        aria-labelledby="approveLoanModalLabel{{ $loan->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('loans.approve', Hashids::encode($loan->id)) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveLoanModalLabel{{ $loan->id }}">Approve Loan Application</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to approve this loan application?</p>
                        <div class="mb-3">
                            <label for="comments" class="form-label">Comments (Optional)</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3"
                                placeholder="Add any comments..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Approve Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Authorize Loan Modal -->
@if($canAuthorize)
    <div class="modal fade" id="authorizeLoanModal{{ $loan->id }}" tabindex="-1"
        aria-labelledby="authorizeLoanModalLabel{{ $loan->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('loans.authorize', Hashids::encode($loan->id)) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="authorizeLoanModalLabel{{ $loan->id }}">Authorize Loan Application</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to authorize this loan application?</p>
                        <div class="mb-3">
                            <label for="comments" class="form-label">Comments (Optional)</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3"
                                placeholder="Add any comments..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Authorize Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Disburse Loan Modal -->
@if($canDisburse)
    <div class="modal fade" id="disburseLoanModal{{ $loan->id }}" tabindex="-1"
        aria-labelledby="disburseLoanModalLabel{{ $loan->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('loans.disburse', Hashids::encode($loan->id)) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="disburseLoanModalLabel{{ $loan->id }}">Disburse Loan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to disburse this loan?</p>
                        <div class="mb-3">
                            <label for="comments" class="form-label">Comments (Optional)</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3"
                                placeholder="Add any comments..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Disburse Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Reject Loan Modal -->
@if($canReject)
    <div class="modal fade" id="rejectLoanModal{{ $loan->id }}" tabindex="-1"
        aria-labelledby="rejectLoanModalLabel{{ $loan->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('loans.reject', Hashids::encode($loan->id)) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectLoanModalLabel{{ $loan->id }}">Reject Loan Application</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to reject this loan application?</p>
                        <div class="mb-3">
                            <label for="comments" class="form-label">Rejection Reason <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="comments" name="comments" rows="3"
                                placeholder="Please provide a reason for rejection..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Default Loan Modal -->
@if($canDefault)
    <div class="modal fade" id="defaultLoanModal{{ $loan->id }}" tabindex="-1"
        aria-labelledby="defaultLoanModalLabel{{ $loan->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('loans.default', Hashids::encode($loan->id)) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="defaultLoanModalLabel{{ $loan->id }}">Mark Loan as Defaulted</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to mark this loan as defaulted?</p>
                        <div class="mb-3">
                            <label for="comments" class="form-label">Default Reason <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="comments" name="comments" rows="3"
                                placeholder="Please provide a reason for default..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark">Mark as Defaulted</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif