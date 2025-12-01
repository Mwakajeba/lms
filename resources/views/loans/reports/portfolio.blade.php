@extends('layouts.main')

@section('title', 'Loan Portfolio Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
            ['label' => 'Portfolio Report', 'url' => '#', 'icon' => 'bx bx-chart-pie']
        ]" />
        <h6 class="mb-0 text-uppercase">LOAN PORTFOLIO REPORT</h6>
        <hr />

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bx bx-chart-pie me-2"></i>Loan Portfolio Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.loans.reports.portfolio') }}">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="as_of_date" class="form-label">As of Date</label>
                            <input type="date" class="form-control" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}">
                        </div>
                        <div class="col-md-3 mb-3">
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
                            <select class="form-select" id="loan_officer_id" name="loan_officer_id">
                                <option value="">All Loan Officers</option>
                                @foreach($loanOfficers as $officer)
                                    <option value="{{ $officer->id }}" {{ $loanOfficerId == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="defaulted" {{ $status == 'defaulted' ? 'selected' : '' }}>Defaulted</option>
                                <option value="active_completed" {{ ($status == 'active_completed' || !request()->has('status')) ? 'selected' : '' }}>Active & Completed</option>
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
                                <h3 class="mb-0">{{ number_format($portfolioData['summary']['total_loans']) }}</h3>
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
                                <h3 class="mb-0">TZS {{ number_format($portfolioData['summary']['total_disbursed'], 2) }}</h3>
                                <p class="mb-0">Total Disbursed</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-money bx-lg"></i>
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
                                <h3 class="mb-0">TZS {{ number_format($portfolioData['summary']['total_outstanding'], 2) }}</h3>
                                <p class="mb-0">Total Outstanding</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-time bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($portfolioData['summary']['par_ratio'], 2) }}%</h3>
                                <p class="mb-0">Portfolio at Risk</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-error bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards Row 2 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($portfolioData['summary']['active_loans']) }}</h3>
                                <p class="mb-0">Active Loans</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-play-circle bx-lg"></i>
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
                                <h3 class="mb-0">{{ number_format($portfolioData['summary']['completed_loans']) }}</h3>
                                <p class="mb-0">Completed Loans</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-check-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($portfolioData['summary']['defaulted_loans']) }}</h3>
                                <p class="mb-0">Defaulted Loans</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-x-circle bx-lg"></i>
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
                                <h3 class="mb-0">{{ number_format($portfolioData['summary']['overall_repayment_rate'], 2) }}%</h3>
                                <p class="mb-0">Repayment Rate</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-trending-up bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Portfolio Details</h5>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('accounting.loans.reports.portfolio.export_excel') }}" class="d-inline">
                        <input type="hidden" name="as_of_date" value="{{ request('as_of_date') }}">
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                        <input type="hidden" name="loan_officer_id" value="{{ request('loan_officer_id') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bx bx-download me-1"></i> Excel
                        </button>
                    </form>
                    <form method="GET" action="{{ route('accounting.loans.reports.portfolio.export_pdf') }}" class="d-inline">
                        <input type="hidden" name="as_of_date" value="{{ request('as_of_date') }}">
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                        <input type="hidden" name="loan_officer_id" value="{{ request('loan_officer_id') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
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
                                    <th>Status</th>
                                    <th>Disbursed Amount</th>
                                    <th>Outstanding</th>
                                    <th>Repayment Rate</th>
                                    <th>Days in Arrears</th>
                                    <th>Disbursed Date</th>
                                    <th>Maturity Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($portfolioData['loans'] as $loan)
                                <tr>
                                    <td>{{ $loan['customer'] }}</td>
                                    <td>{{ $loan['customer_no'] }}</td>
                                    <td>{{ $loan['branch'] }}</td>
                                    <td>{{ $loan['group'] }}</td>
                                    <td>{{ $loan['loan_officer'] }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($loan['status'] == 'active') bg-success
                                            @elseif($loan['status'] == 'completed') bg-primary
                                            @elseif($loan['status'] == 'defaulted') bg-danger
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst($loan['status']) }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format($loan['disbursed_amount'], 2) }}</td>
                                    <td class="text-end">{{ number_format($loan['outstanding_amount'], 2) }}</td>
                                    <td class="text-end">{{ number_format($loan['repayment_rate'], 2) }}%</td>
                                    <td class="text-end">
                                        <span class="badge 
                                            @if($loan['days_in_arrears'] == 0) bg-success
                                            @elseif($loan['days_in_arrears'] <= 30) bg-warning
                                            @elseif($loan['days_in_arrears'] <= 60) bg-orange
                                            @elseif($loan['days_in_arrears'] <= 90) bg-danger
                                            @else bg-dark
                                            @endif">
                                            {{ $loan['days_in_arrears'] }} days
                                        </span>
                                    </td>
                                    <td>{{ $loan['disbursed_date'] }}</td>
                                    <td>{{ $loan['maturity_date'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center">No loans found for the selected criteria.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Loan Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Portfolio Performance</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Loan Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Active', 'Completed', 'Defaulted'],
        datasets: [{
            data: [
                {{ $portfolioData['summary']['active_loans'] }},
                {{ $portfolioData['summary']['completed_loans'] }},
                {{ $portfolioData['summary']['defaulted_loans'] }}
            ],
            backgroundColor: [
                '#28a745',
                '#007bff',
                '#dc3545'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Portfolio Performance Chart
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(performanceCtx, {
    type: 'bar',
    data: {
        labels: ['Total Disbursed', 'Total Outstanding', 'Total Paid', 'Portfolio at Risk'],
        datasets: [{
            label: 'Amount (TZS)',
            data: [
                {{ $portfolioData['summary']['total_disbursed'] }},
                {{ $portfolioData['summary']['total_outstanding'] }},
                {{ $portfolioData['summary']['total_paid'] }},
                {{ $portfolioData['summary']['portfolio_at_risk'] }}
            ],
            backgroundColor: [
                '#28a745',
                '#ffc107',
                '#007bff',
                '#dc3545'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'TZS ' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
@endif

<style>
.bg-orange {
    background-color: #fd7e14 !important;
}
</style>
@endsection
