@extends('layouts.main')

@section('title', 'Subscriptions')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Subscriptions', 'url' => '#', 'icon' => 'bx bx-credit-card']
        ]" />
            <h6 class="mb-0 text-uppercase">SUBSCRIPTIONS MANAGEMENT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">All Subscriptions</h4>
                                <a href="{{ route('subscriptions.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Create New Subscription
                                </a>
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

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Company</th>
                                            <th>Plan Name</th>
                                            <th>Amount</th>
                                            <th>Billing Cycle</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                            <th>Payment Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($subscriptions as $subscription)
                                            <tr>
                                                <td>{{ $subscription->company->name }}</td>
                                                <td>{{ $subscription->plan_name }}</td>
                                                <td>{{ number_format($subscription->amount, 2) }} {{ $subscription->currency }}
                                                </td>
                                                <td>{{ ucfirst($subscription->billing_cycle) }}</td>
                                                <td>{{ $subscription->start_date->format('M d, Y') }}</td>
                                                <td>{{ $subscription->end_date->format('M d, Y') }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $subscription->getStatusBadgeClass() }}" style="color: black;">
                                                        {{ ucfirst($subscription->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $subscription->getPaymentStatusBadgeClass() }}" style="color: black;">
                                                        {{ ucfirst($subscription->payment_status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('subscriptions.show', $subscription) }}"
                                                            class="btn btn-sm btn-info">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <a href="{{ route('subscriptions.edit', $subscription) }}"
                                                            class="btn btn-sm btn-warning">
                                                            <i class="bx bx-edit"></i>
                                                        </a>
                                                        @if($subscription->payment_status !== 'paid')
                                                            <button type="button" class="btn btn-sm btn-success"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#markPaidModal{{ $subscription->id }}">
                                                                <i class="bx bx-check"></i>
                                                            </button>
                                                        @endif
                                                        @if(in_array($subscription->status, ['pending', 'cancelled']))
                                                            <form action="{{ route('subscriptions.destroy', $subscription) }}"
                                                                method="POST" class="d-inline"
                                                                onsubmit="return confirm('Are you sure you want to delete this subscription?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Mark as Paid Modal -->
                                            <div class="modal fade" id="markPaidModal{{ $subscription->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Mark as Paid</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('subscriptions.mark-paid', $subscription) }}"
                                                            method="POST">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="payment_method{{ $subscription->id }}"
                                                                        class="form-label">Payment Method</label>
                                                                    <select class="form-select"
                                                                        id="payment_method{{ $subscription->id }}"
                                                                        name="payment_method" required>
                                                                        <option value="">Select Payment Method</option>
                                                                        <option value="Bank Transfer">Bank Transfer</option>
                                                                        <option value="Credit Card">Credit Card</option>
                                                                        <option value="Mobile Money">Mobile Money</option>
                                                                        <option value="Cash">Cash</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="transaction_id{{ $subscription->id }}"
                                                                        class="form-label">Transaction ID</label>
                                                                    <input type="text" class="form-control"
                                                                        id="transaction_id{{ $subscription->id }}"
                                                                        name="transaction_id" placeholder="Optional">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="payment_notes{{ $subscription->id }}"
                                                                        class="form-label">Payment Notes</label>
                                                                    <textarea class="form-control"
                                                                        id="payment_notes{{ $subscription->id }}"
                                                                        name="payment_notes" rows="3"
                                                                        placeholder="Optional notes about the payment"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-success">Mark as
                                                                    Paid</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No subscriptions found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($subscriptions->hasPages())
                                <div class="d-flex justify-content-center">
                                    {{ $subscriptions->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
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