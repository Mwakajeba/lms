@extends('layouts.main')

@section('title', 'Customer Risk Assessment Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Customer Reports', 'url' => route('reports.customers'), 'icon' => 'bx bx-group'],
                ['label' => 'Customer Risk Assessment Report', 'url' => '#', 'icon' => 'bx bx-shield-alt-2']
            ]" />
            <h6 class="mb-0 text-uppercase">CUSTOMER RISK ASSESSMENT REPORT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Customer Risk Assessment Report</h4>

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
                            <form method="GET" action="{{ route('reports.customers.risk-assessment') }}" class="mb-4">
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
                                        <label for="risk_level" class="form-label">Risk Level</label>
                                        <select class="form-select" id="risk_level" name="risk_level">
                                            <option value="all" {{ $riskLevel == 'all' ? 'selected' : '' }}>All Risk Levels</option>
                                            <option value="low" {{ $riskLevel == 'low' ? 'selected' : '' }}>Low Risk</option>
                                            <option value="medium" {{ $riskLevel == 'medium' ? 'selected' : '' }}>Medium Risk</option>
                                            <option value="high" {{ $riskLevel == 'high' ? 'selected' : '' }}>High Risk</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="assessment_type" class="form-label">Assessment Type</label>
                                        <select class="form-select" id="assessment_type" name="assessment_type">
                                            <option value="all" {{ $assessmentType == 'all' ? 'selected' : '' }}>All Types</option>
                                            <option value="low_risk" {{ $assessmentType == 'low_risk' ? 'selected' : '' }}>Low Risk (80-100)</option>
                                            <option value="medium_risk" {{ $assessmentType == 'medium_risk' ? 'selected' : '' }}>Medium Risk (40-79)</option>
                                            <option value="high_risk" {{ $assessmentType == 'high_risk' ? 'selected' : '' }}>High Risk (0-39)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bx bx-search me-1"></i> Generate Report
                                        </button>
                                        <a href="{{ route('reports.customers.risk-assessment.export-pdf', request()->query()) }}" 
                                           class="btn btn-danger me-2" target="_blank">
                                            <i class="bx bx-file-pdf me-1"></i> Export PDF
                                        </a>
                                        <a href="{{ route('reports.customers.risk-assessment.export', request()->query()) }}" 
                                           class="btn btn-success">
                                            <i class="bx bx-file me-1"></i> Export Excel
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <!-- Risk Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-md-2">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">Total Customers</h6>
                                            <h4 class="text-primary">{{ number_format($riskData['summary']['total_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-success">Low Risk</h6>
                                            <h4 class="text-success">{{ number_format($riskData['summary']['low_risk_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-warning">Medium Risk</h6>
                                            <h4 class="text-warning">{{ number_format($riskData['summary']['medium_risk_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-danger">High Risk</h6>
                                            <h4 class="text-danger">{{ number_format($riskData['summary']['high_risk_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info">Avg Risk Score</h6>
                                            <h4 class="text-info">{{ number_format($riskData['summary']['average_risk_score'], 1) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-secondary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-secondary">With Overdue</h6>
                                            <h4 class="text-secondary">{{ number_format($riskData['summary']['customers_with_overdue']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Risk Summary -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">Total Loan Amount</h6>
                                            <h4 class="text-primary">{{ number_format($riskData['summary']['total_loan_amount'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-danger">Outstanding Amount</h6>
                                            <h4 class="text-danger">{{ number_format($riskData['summary']['total_outstanding_amount'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-success">Total Collateral</h6>
                                            <h4 class="text-success">{{ number_format($riskData['summary']['total_collateral_value'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-warning">Avg Repayment Rate</h6>
                                            <h4 class="text-warning">{{ number_format($riskData['summary']['average_repayment_rate'], 1) }}%</h4>
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
                                            <th>Customer No</th>
                                            <th>Customer Name</th>
                                            <th>Branch</th>
                                            <th>Region</th>
                                            <th>Age</th>
                                            <th>Category</th>
                                            <th class="text-center">Total Loans</th>
                                            <th class="text-end">Loan Amount</th>
                                            <th class="text-end">Outstanding</th>
                                            <th class="text-end">Collateral</th>
                                            <th class="text-center">Repayment Rate (%)</th>
                                            <th class="text-center">Avg Days Overdue</th>
                                            <th class="text-center">Risk Score</th>
                                            <th>Risk Level</th>
                                            <th class="text-center">Overdue</th>
                                            <th>Risk Factors</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($riskData['data'] as $index => $customer)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $customer['customer_no'] }}</td>
                                                <td>{{ $customer['customer_name'] }}</td>
                                                <td>{{ $customer['branch_name'] }}</td>
                                                <td>{{ $customer['region_name'] }}</td>
                                                <td class="text-center">{{ $customer['age'] ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $customer['category'] == 'group' ? 'success' : 'info' }}">
                                                        {{ ucfirst($customer['category']) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">{{ $customer['total_loans'] }}</td>
                                                <td class="text-end">{{ number_format($customer['total_loan_amount'], 2) }}</td>
                                                <td class="text-end">
                                                    <span class="text-{{ $customer['outstanding_amount'] > 0 ? 'danger' : 'success' }}">
                                                        {{ number_format($customer['outstanding_amount'], 2) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-{{ $customer['has_collateral'] ? 'success' : 'warning' }}">
                                                        {{ number_format($customer['total_collateral'], 2) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ 
                                                        $customer['repayment_rate'] >= 80 ? 'success' : 
                                                        ($customer['repayment_rate'] >= 60 ? 'warning' : 'danger') 
                                                    }}">
                                                        {{ number_format($customer['repayment_rate'], 1) }}%
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ 
                                                        $customer['average_days_overdue'] <= 7 ? 'success' : 
                                                        ($customer['average_days_overdue'] <= 30 ? 'warning' : 'danger') 
                                                    }}">
                                                        {{ number_format($customer['average_days_overdue'], 0) }} days
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ 
                                                        $customer['risk_score'] >= 80 ? 'success' : 
                                                        ($customer['risk_score'] >= 50 ? 'warning' : 'danger') 
                                                    }}">
                                                        {{ number_format($customer['risk_score'], 1) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ 
                                                        $customer['risk_level'] === 'low' ? 'success' : 
                                                        ($customer['risk_level'] === 'medium' ? 'warning' : 'danger') 
                                                    }}">
                                                        {{ ucfirst($customer['risk_level']) }} Risk
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $customer['has_overdue'] ? 'danger' : 'success' }}">
                                                        {{ $customer['has_overdue'] ? 'Yes' : 'No' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if(count($customer['risk_factors']) > 0)
                                                        <small>
                                                            @foreach($customer['risk_factors'] as $factor)
                                                                <span class="badge bg-danger me-1">{{ $factor }}</span>
                                                            @endforeach
                                                        </small>
                                                    @else
                                                        <span class="badge bg-success">No Risk Factors</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="18" class="text-center text-muted py-4">
                                                    <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                                                    No customer risk assessment data found for the selected criteria.
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
