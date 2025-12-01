@extends('layouts.main')

@section('title', 'Loan Performance Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
            ['label' => 'Performance Report', 'url' => '#', 'icon' => 'bx bx-trending-up']
        ]" />
        <h6 class="mb-0 text-uppercase">LOAN PERFORMANCE REPORT</h6>
        <hr />

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bx bx-trending-up me-2"></i>Loan Performance Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.loans.reports.performance') }}">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label for="from_date" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="from_date" name="from_date" value="{{ $fromDate }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="to_date" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="to_date" name="to_date" value="{{ $toDate }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select class="form-select" id="branch_id" name="branch_id">
                                @if(($branches->count() ?? 0) > 1)
                                    <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All My Branches</option>
                                @endif
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="loan_officer_id" class="form-label">Loan Officer</label>
                            <select class="form-select select2-single" id="loan_officer_id" name="loan_officer_id">
                                <option value="">All Loan Officers</option>
                                @foreach($loanOfficers as $officer)
                                    <option value="{{ $officer->id }}" {{ $loanOfficerId == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="group_id" class="form-label">Group</label>
                            <select class="form-select select2-single" id="group_id" name="group_id">
                                <option value="">All Groups</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ $groupId == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($showData)
        <!-- Summary Cards Row 1 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($performanceData['summary']['total_loans']) }}</h3>
                                <p class="mb-0">Total Loans</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-credit-card bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                    <h3 class="mb-0">{{ number_format($performanceData['summary']['excellent_loans'] ?? 0) }}</h3>
                                <p class="mb-0">Excellent Performance</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-star bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($performanceData['summary']['good_loans'] ?? 0) }}</h3>
                                <p class="mb-0">Good Performance</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-check bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($performanceData['summary']['fair_loans'] ?? 0) }}</h3>
                                <p class="mb-0">Fair Performance</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-minus bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards Row 2 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($performanceData['summary']['poor_loans'] ?? 0) }}</h3>
                                <p class="mb-0">Poor Performance</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-x bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($performanceData['summary']['average_repayment_rate'] ?? 0, 2) }}%</h3>
                                <p class="mb-0">Avg Repayment Rate</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-trending-up bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">TZS {{ number_format($performanceData['summary']['total_collections'], 2) }}</h3>
                                <p class="mb-0">Total Collections</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-money bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">TZS {{ number_format($performanceData['summary']['period_collections'], 2) }}</h3>
                                <p class="mb-0">Period Collections</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-calendar bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Performance Summary</h5>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('accounting.loans.reports.performance') }}" class="d-inline">
                        <input type="hidden" name="from_date" value="{{ request('from_date') }}">
                        <input type="hidden" name="to_date" value="{{ request('to_date') }}">
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                        <input type="hidden" name="loan_officer_id" value="{{ request('loan_officer_id') }}">
                        <input type="hidden" name="export_type" value="excel">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bx bx-download me-1"></i> Excel
                        </button>
                    </form>
                    <form method="GET" action="{{ route('accounting.loans.reports.performance') }}" class="d-inline">
                        <input type="hidden" name="from_date" value="{{ request('from_date') }}">
                        <input type="hidden" name="to_date" value="{{ request('to_date') }}">
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                        <input type="hidden" name="loan_officer_id" value="{{ request('loan_officer_id') }}">
                        <input type="hidden" name="export_type" value="pdf">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bx bx-download me-1"></i> PDF
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Customer No</th>
                                <th>Branch</th>
                                <th>Group</th>
                                <th>Loan Officer</th>
                                <th>Outstanding</th>
                                <th>Repayment Rate</th>
                                <th>Days in Arrears</th>
                                <th>Performance Grade</th>
                                <th>Risk Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($performanceData['loans'] as $loan)
                            <tr>
                                <td>{{ $loan['customer'] }}</td>
                                <td>{{ $loan['customer_no'] }}</td>
                                <td>{{ $loan['branch'] }}</td>
                                <td>{{ $loan['group'] }}</td>
                                <td>{{ $loan['loan_officer'] }}</td>
                                <td class="text-end">TZS {{ number_format($loan['outstanding_amount'], 2) }}</td>
                                <td class="text-end">{{ number_format($loan['repayment_rate'], 2) }}%</td>
                                <td class="text-end">
                                    <span class="badge
                                        @if($loan['days_in_arrears'] == 0) bg-success
                                        @elseif($loan['days_in_arrears'] <= 30) bg-warning
                                        @elseif($loan['days_in_arrears'] <= 60) bg-secondary
                                        @elseif($loan['days_in_arrears'] <= 90) bg-danger
                                        @else bg-dark
                                        @endif">
                                        {{ $loan['days_in_arrears'] }} days
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge
                                        @if($loan['performance_grade'] == 'Excellent') bg-success
                                        @elseif($loan['performance_grade'] == 'Good') bg-primary
                                        @elseif($loan['performance_grade'] == 'Fair') bg-warning
                                        @elseif($loan['performance_grade'] == 'Poor') bg-secondary
                                        @else bg-danger
                                        @endif">
                                        {{ $loan['performance_grade'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge
                                        @if($loan['risk_category'] == 'Low Risk') bg-success
                                        @elseif($loan['risk_category'] == 'Medium Risk') bg-warning
                                        @elseif($loan['risk_category'] == 'High Risk') bg-danger
                                        @else bg-dark
                                        @endif">
                                        {{ $loan['risk_category'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No loans found for the selected criteria.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
