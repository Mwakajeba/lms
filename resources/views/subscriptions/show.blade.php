@extends('layouts.main')

@section('title', 'Subscription Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Subscriptions', 'url' => route('subscriptions.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
            <h6 class="mb-0 text-uppercase">SUBSCRIPTION DETAILS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">Subscription Details</h4>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('subscriptions.edit', $subscription) }}" class="btn btn-warning">
                                        <i class="bx bx-edit me-1"></i> Edit
                                    </a>
                                    <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back
                                    </a>
                                </div>
                            </div>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bx bx-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-2"></i>
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Basic Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-sm-4"><strong>Company:</strong></div>
                                                <div class="col-sm-8">{{ $subscription->company->name }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4"><strong>Plan Name:</strong></div>
                                                <div class="col-sm-8">{{ $subscription->plan_name }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4"><strong>Description:</strong></div>
                                                <div class="col-sm-8">
                                                    {{ $subscription->plan_description ?: 'No description' }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4"><strong>Amount:</strong></div>
                                                <div class="col-sm-8">{{ number_format($subscription->amount, 2) }}
                                                    {{ $subscription->currency }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4"><strong>Billing Cycle:</strong></div>
                                                <div class="col-sm-8">{{ ucfirst($subscription->billing_cycle) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Dates & Status</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-sm-4"><strong>Start Date:</strong></div>
                                                <div class="col-sm-8">{{ $subscription->start_date->format('M d, Y') }}
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4"><strong>End Date:</strong></div>
                                                <div class="col-sm-8">{{ $subscription->end_date->format('M d, Y') }}</div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4"><strong>Status:</strong></div>
                                                <div class="col-sm-8">
                                                    <span class="badge badge-{{ $subscription->getStatusBadgeClass() }}">
                                                        {{ ucfirst($subscription->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4"><strong>Payment Status:</strong></div>
                                                <div class="col-sm-8">
                                                    <span
                                                        class="badge badge-{{ $subscription->getPaymentStatusBadgeClass() }}">
                                                        {{ ucfirst($subscription->payment_status) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-sm-4"><strong>Auto Renewal:</strong></div>
                                                <div class="col-sm-8">
                                                    <span
                                                        class="badge badge-{{ $subscription->auto_renew ? 'success' : 'secondary' }}">
                                                        {{ $subscription->auto_renew ? 'Yes' : 'No' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($subscription->payment_status === 'paid' && $subscription->payment_date)
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card border">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Payment Information</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row mb-3">
                                                    <div class="col-sm-3"><strong>Payment Method:</strong></div>
                                                    <div class="col-sm-3">{{ $subscription->payment_method ?: 'Not specified' }}
                                                    </div>
                                                    <div class="col-sm-3"><strong>Transaction ID:</strong></div>
                                                    <div class="col-sm-3">{{ $subscription->transaction_id ?: 'Not specified' }}
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-3"><strong>Payment Date:</strong></div>
                                                    <div class="col-sm-9">
                                                        {{ $subscription->payment_date->format('M d, Y H:i') }}</div>
                                                </div>
                                                @if($subscription->payment_notes)
                                                    <div class="row mb-3">
                                                        <div class="col-sm-3"><strong>Payment Notes:</strong></div>
                                                        <div class="col-sm-9">{{ $subscription->payment_notes }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Actions</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="btn-group" role="group">
                                                @if($subscription->payment_status !== 'paid')
                                                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                        data-bs-target="#markPaidModal">
                                                        <i class="bx bx-check me-1"></i> Mark as Paid
                                                    </button>
                                                @endif

                                                @if($subscription->status !== 'cancelled')
                                                    <form action="{{ route('subscriptions.cancel', $subscription) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to cancel this subscription?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-warning">
                                                            <i class="bx bx-x me-1"></i> Cancel
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($subscription->status === 'expired')
                                                    <form action="{{ route('subscriptions.renew', $subscription) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to renew this subscription?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-info">
                                                            <i class="bx bx-refresh me-1"></i> Renew
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(in_array($subscription->status, ['pending', 'cancelled']))
                                                    <form action="{{ route('subscriptions.destroy', $subscription) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this subscription?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="bx bx-trash me-1"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mark as Paid Modal -->
    <div class="modal fade" id="markPaidModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark as Paid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('subscriptions.mark-paid', $subscription) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id"
                                placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label for="payment_notes" class="form-label">Payment Notes</label>
                            <textarea class="form-control" id="payment_notes" name="payment_notes" rows="3"
                                placeholder="Optional notes about the payment"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Mark as Paid</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <footer class="page-footer">
        <p class="mb-0">Copyright © {{ date('Y') }}. All right reserved. -- By SAFCO FINTECH</p>
    </footer>
@endsection