@extends('layouts.main')

@section('title', 'Customer Activity Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Customer Reports', 'url' => route('reports.customers'), 'icon' => 'bx bx-group'],
                ['label' => 'Customer Activity Report', 'url' => '#', 'icon' => 'bx bx-user-check']
            ]" />
            <h6 class="mb-0 text-uppercase">CUSTOMER ACTIVITY REPORT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Customer Activity Report</h4>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bx bx-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(isset($errors) && $errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-2"></i>
                                    Please fix the following errors:
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <!-- Filter Form -->
                            <form method="GET" action="{{ route('reports.customers.activity') }}" class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="{{ $startDate }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="{{ $endDate }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="branch_id" class="form-label">Branch</label>
                                        <select class="form-select" id="branch_id" name="branch_id">
                                            <option value="all">All Branches</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="customer_id" class="form-label">Customer</label>
                                        <select class="form-select" id="customer_id" name="customer_id">
                                            <option value="all">All Customers</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" {{ $customerId == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} ({{ $customer->customerNo }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="activity_type" class="form-label">Activity Type</label>
                                        <select class="form-select" id="activity_type" name="activity_type">
                                            <option value="all" {{ $activityType == 'all' ? 'selected' : '' }}>All Activities</option>
                                            <option value="loans" {{ $activityType == 'loans' ? 'selected' : '' }}>Loan Applications</option>
                                            <option value="repayments" {{ $activityType == 'repayments' ? 'selected' : '' }}>Loan Repayments</option>
                                            <option value="collaterals" {{ $activityType == 'collaterals' ? 'selected' : '' }}>Collateral Deposits</option>
                                            <option value="transactions" {{ $activityType == 'transactions' ? 'selected' : '' }}>GL Transactions</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="transaction_type" class="form-label">Transaction Type</label>
                                        <select class="form-select" id="transaction_type" name="transaction_type">
                                            <option value="all" {{ $transactionType == 'all' ? 'selected' : '' }}>All Types</option>
                                            <option value="loan_application" {{ $transactionType == 'loan_application' ? 'selected' : '' }}>Loan Application</option>
                                            <option value="loan_repayment" {{ $transactionType == 'loan_repayment' ? 'selected' : '' }}>Loan Repayment</option>
                                            <option value="collateral_deposit" {{ $transactionType == 'collateral_deposit' ? 'selected' : '' }}>Collateral Deposit</option>
                                            <option value="receipt" {{ $transactionType == 'receipt' ? 'selected' : '' }}>Receipt</option>
                                            <option value="payment" {{ $transactionType == 'payment' ? 'selected' : '' }}>Payment</option>
                                            <option value="journal" {{ $transactionType == 'journal' ? 'selected' : '' }}>Journal Entry</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bx bx-search me-1"></i> Generate Report
                                        </button>
                                        <a href="{{ route('reports.customers.activity.export-pdf', request()->query()) }}" 
                                           class="btn btn-danger me-2" target="_blank">
                                            <i class="bx bx-file-pdf me-1"></i> Export PDF
                                        </a>
                                        <a href="{{ route('reports.customers.activity.export', request()->query()) }}" 
                                           class="btn btn-success">
                                            <i class="bx bx-file me-1"></i> Export Excel
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <!-- Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-md-2">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">Total Activities</h6>
                                            <h4 class="text-primary">{{ number_format($activityData['summary']['total_activities']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-success">Loan Applications</h6>
                                            <h4 class="text-success">{{ number_format($activityData['summary']['loan_applications']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info">Loan Repayments</h6>
                                            <h4 class="text-info">{{ number_format($activityData['summary']['loan_repayments']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-warning">Collateral Deposits</h6>
                                            <h4 class="text-warning">{{ number_format($activityData['summary']['collateral_deposits']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-secondary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-secondary">GL Transactions</h6>
                                            <h4 class="text-secondary">{{ number_format($activityData['summary']['gl_transactions']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-danger">Total Amount</h6>
                                            <h4 class="text-danger">{{ number_format($activityData['summary']['total_amount'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Summary -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">Unique Customers</h6>
                                            <h4 class="text-primary">{{ number_format($activityData['summary']['unique_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info">Unique Branches</h6>
                                            <h4 class="text-info">{{ number_format($activityData['summary']['unique_branches']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Date & Time</th>
                                            <th>Customer No</th>
                                            <th>Customer Name</th>
                                            <th>Branch</th>
                                            <th>Activity Type</th>
                                            <th>Transaction Type</th>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                            <th>Status</th>
                                            <th>Reference ID</th>
                                            <th>Created By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($activityData['data'] as $index => $activity)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i:s') }}</td>
                                                <td>{{ $activity['customer_no'] }}</td>
                                                <td>{{ $activity['customer_name'] }}</td>
                                                <td>{{ $activity['branch_name'] }}</td>
                                                <td>
                                                    <span class="badge bg-{{ 
                                                        $activity['activity_type'] === 'Loan Application' ? 'success' : 
                                                        ($activity['activity_type'] === 'Loan Repayment' ? 'info' : 
                                                        ($activity['activity_type'] === 'Collateral Deposit' ? 'warning' : 'secondary')) 
                                                    }}">
                                                        {{ $activity['activity_type'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        {{ ucfirst(str_replace('_', ' ', $activity['transaction_type'])) }}
                                                    </span>
                                                </td>
                                                <td>{{ Str::limit($activity['description'], 50) }}</td>
                                                <td class="text-end">{{ number_format($activity['amount'], 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ 
                                                        $activity['status'] === 'active' || $activity['status'] === 'completed' ? 'success' : 
                                                        ($activity['status'] === 'pending' ? 'warning' : 
                                                        ($activity['status'] === 'credit' ? 'info' : 'danger')) 
                                                    }}">
                                                        {{ ucfirst($activity['status']) }}
                                                    </span>
                                                </td>
                                                <td>{{ $activity['reference_id'] }}</td>
                                                <td>{{ $activity['created_by'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center text-muted py-4">
                                                    <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                                                    No customer activities found for the selected criteria.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
