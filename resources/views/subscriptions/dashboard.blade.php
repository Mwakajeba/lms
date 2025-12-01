@extends('layouts.main')

@section('title', 'Subscription Dashboard')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Subscription Dashboard', 'url' => '#', 'icon' => 'bx bx-credit-card']
        ]" />

            <h6 class="mb-0 text-uppercase">SUBSCRIPTION DASHBOARD</h6>
            <hr />

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

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="bx bx-error me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-3 border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Total</p>
                                    <h4 class="my-1 text-info">{{ $stats['total_subscriptions'] }}</h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-scooter text-white ms-auto">
                                    <i class="bx bx-credit-card"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-3 border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Active</p>
                                    <h4 class="my-1 text-success">{{ $stats['active_subscriptions'] }}</h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto">
                                    <i class="bx bx-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-3 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Expiring Soon</p>
                                    <h4 class="my-1 text-warning">{{ $stats['expiring_soon'] }}</h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto">
                                    <i class="bx bx-time"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-3 border-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Expired</p>
                                    <h4 class="my-1 text-danger">{{ $stats['expired'] }}</h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-orange text-white ms-auto">
                                    <i class="bx bx-x-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="card radius-10 border-start border-0 border-3 border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Pending Payment</p>
                                    <h4 class="my-1 text-primary">{{ $stats['pending_payments'] }}</h4>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-moonlit text-white ms-auto">
                                    <i class="bx bx-dollar-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($expiring_subscriptions->count() > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0 text-white">
                                    <i class="bx bx-time me-2"></i>Subscriptions Expiring Soon
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Company</th>
                                                <th>Plan</th>
                                                <th>End Date</th>
                                                <th>Days Left</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($expiring_subscriptions as $subscription)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-light rounded">
                                                                <i class="bx bx-building font-size-18"></i>
                                                            </div>
                                                            <div class="ms-2">
                                                                <h6 class="mb-0">{{ $subscription->company->name }}</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary">{{ $subscription->plan_name }}</span>
                                                    </td>
                                                    <td>{{ $subscription->end_date->format('M d, Y') }}</td>
                                                    <td>
                                                        <span class="badge bg-warning text-dark">
                                                            {{ $subscription->daysUntilExpiry() }} days
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <a href="{{ route('subscriptions.show', $subscription) }}"
                                                                class="btn btn-sm btn-outline-primary">
                                                                <i class="bx bx-show me-1"></i>View
                                                            </a>
                                                            <a href="{{ route('subscriptions.edit', $subscription) }}"
                                                                class="btn btn-sm btn-outline-secondary">
                                                                <i class="bx bx-edit me-1"></i>Edit
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($recent_subscriptions->count() > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary">
                                <h5 class="mb-0 text-white">
                                    <i class="bx bx-history me-2"></i>Recent Subscriptions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Company</th>
                                                <th>Plan</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Payment Status</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recent_subscriptions as $subscription)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-light rounded">
                                                                <i class="bx bx-building font-size-18"></i>
                                                            </div>
                                                            <div class="ms-2">
                                                                <h6 class="mb-0">{{ $subscription->company->name }}</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $subscription->plan_name }}</span>
                                                    </td>
                                                    <td>
                                                        <strong>{{ number_format($subscription->amount, 2) }}
                                                            {{ $subscription->currency }}</strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $subscription->getStatusBadgeClass() }}">
                                                            {{ ucfirst($subscription->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $subscription->getPaymentStatusBadgeClass() }}">
                                                            {{ ucfirst($subscription->payment_status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $subscription->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <a href="{{ route('subscriptions.show', $subscription) }}"
                                                                class="btn btn-sm btn-outline-primary">
                                                                <i class="bx bx-show me-1"></i>View
                                                            </a>
                                                            <a href="{{ route('subscriptions.edit', $subscription) }}"
                                                                class="btn btn-sm btn-outline-secondary">
                                                                <i class="bx bx-edit me-1"></i>Edit
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success">
                            <h5 class="mb-0 text-white">
                                <i class="bx bx-zap me-2"></i>Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-3">
                                <a href="{{ route('subscriptions.index') }}" class="btn btn-info btn-lg">
                                    <i class="bx bx-list-ul me-2"></i>View All Subscriptions
                                </a>
                                <a href="{{ route('subscriptions.create') }}" class="btn btn-success btn-lg">
                                    <i class="bx bx-plus-circle me-2"></i>Create New Subscription
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection