@extends('layouts.main')

@section('title', 'Internal Portfolio Analysis Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
            ['label' => 'Internal Portfolio Analysis', 'url' => '#', 'icon' => 'bx bx-analyze']
        ]" />

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-0">Internal Portfolio Analysis Report</h4>
                <p class="text-muted mb-0">Conservative risk analysis showing only overdue amounts at risk</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-success" onclick="exportToExcel()">
                    <i class="bx bx-download me-1"></i> Export Excel
                </button>
                <button type="button" class="btn btn-danger" onclick="exportToPdf()">
                    <i class="bx bx-file-pdf me-1"></i> Export PDF
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('accounting.loans.reports.internal_portfolio_analysis') }}" id="filterForm">
                            <div class="row mb-4">
                                <div class="col-md-2">
                                    <label for="as_of_date" class="form-label">As of Date</label>
                                    <input type="date" class="form-control" id="as_of_date" name="as_of_date" 
                                           value="{{ $asOfDate }}" required>
                                </div>
                                <div class="col-md-2">
                                    <label for="par_days" class="form-label">PAR Days</label>
                                    <select class="form-select" id="par_days" name="par_days">
                                        <option value="1" {{ $parDays == 1 ? 'selected' : '' }}>PAR 1</option>
                                        <option value="30" {{ $parDays == 30 ? 'selected' : '' }}>PAR 30</option>
                                        <option value="60" {{ $parDays == 60 ? 'selected' : '' }}>PAR 60</option>
                                        <option value="90" {{ $parDays == 90 ? 'selected' : '' }}>PAR 90</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        @if(($branches->count() ?? 0) > 1)
                                            <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All My Branches</option>
                                        @endif
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="group_id" class="form-label">Group</label>
                                    <select class="form-select" id="group_id" name="group_id">
                                        <option value="">All Groups</option>
                                        @foreach($groups as $group)
                                            <option value="{{ $group->id }}" {{ $groupId == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="loan_officer_id" class="form-label">Loan Officer</label>
                                    <select class="form-select" id="loan_officer_id" name="loan_officer_id">
                                        <option value="">All Officers</option>
                                        @foreach($loanOfficers as $officer)
                                            <option value="{{ $officer->id }}" {{ $loanOfficerId == $officer->id ? 'selected' : '' }}>
                                                {{ $officer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-filter me-1"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Summary Cards -->
                        @if(count($analysisData) > 0)
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-1">TZS {{ number_format(array_sum(array_column($analysisData, 'outstanding_balance')), 2) }}</h3>
                                        <p class="mb-0">Total Portfolio</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-1">TZS {{ number_format(array_sum(array_column($analysisData, 'overdue_amount')), 2) }}</h3>
                                        <p class="mb-0">Total Overdue</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-1">TZS {{ number_format(array_sum(array_column($analysisData, 'at_risk_amount')), 2) }}</h3>
                                        <p class="mb-0">At Risk Amount</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3 class="mb-1">{{ count(array_filter($analysisData, function($item) { return $item['is_at_risk']; })) }}/{{ count($analysisData) }}</h3>
                                        <p class="mb-0">Loans at Risk</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Analysis Metrics -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-secondary">
                                    <div class="card-body">
                                        <h6 class="card-title">Portfolio Ratios</h6>
                                        @php
                                            $totalOutstanding = array_sum(array_column($analysisData, 'outstanding_balance'));
                                            $totalOverdue = array_sum(array_column($analysisData, 'overdue_amount'));
                                            $totalAtRisk = array_sum(array_column($analysisData, 'at_risk_amount'));
                                            $overdueRatio = $totalOutstanding > 0 ? ($totalOverdue / $totalOutstanding) * 100 : 0;
                                            $conservativeParRatio = $totalOutstanding > 0 ? ($totalAtRisk / $totalOutstanding) * 100 : 0;
                                        @endphp
                                        <p class="mb-1"><strong>Overdue Ratio:</strong> {{ number_format($overdueRatio, 2) }}%</p>
                                        <p class="mb-1"><strong>Conservative PAR {{ $parDays }}:</strong> {{ number_format($conservativeParRatio, 2) }}%</p>
                                        <p class="mb-0"><strong>Current Ratio:</strong> {{ number_format(100 - $overdueRatio, 2) }}%</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-secondary">
                                    <div class="card-body">
                                        <h6 class="card-title">Exposure Distribution</h6>
                                        @php
                                            $exposureCategories = ['Current' => 0, 'Low Exposure' => 0, 'Medium Exposure' => 0, 'High Exposure' => 0, 'Critical Exposure' => 0];
                                            foreach ($analysisData as $loan) {
                                                if (isset($exposureCategories[$loan['exposure_category']])) {
                                                    $exposureCategories[$loan['exposure_category']]++;
                                                }
                                            }
                                        @endphp
                                        @foreach($exposureCategories as $category => $count)
                                            <p class="mb-1"><strong>{{ $category }}:</strong> {{ $count }} loans</p>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 3%;">#</th>
                                        <th style="width: 12%;">Customer</th>
                                        <th style="width: 8%;">Customer No</th>
                                        <th style="width: 8%;">Phone</th>
                                        <th style="width: 8%;">Loan No</th>
                                        <th style="width: 8%;">Branch</th>
                                        <th style="width: 8%;">Group</th>
                                        <th style="width: 8%;">Outstanding</th>
                                        <th style="width: 8%;">Overdue</th>
                                        <th style="width: 8%;">At Risk</th>
                                        <th style="width: 6%;">Overdue %</th>
                                        <th style="width: 5%;">Days</th>
                                        <th style="width: 8%;">Risk Level</th>
                                        <th style="width: 10%;">Exposure</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($analysisData) > 0)
                                        @foreach($analysisData as $index => $row)
                                        <tr class="{{ $row['is_at_risk'] ? 'table-danger' : ($row['overdue_amount'] > 0 ? 'table-warning' : '') }}">
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $row['customer'] }}</td>
                                            <td class="text-center">{{ $row['customer_no'] }}</td>
                                            <td class="text-center">{{ $row['phone'] }}</td>
                                            <td class="text-center">{{ $row['loan_no'] }}</td>
                                            <td>{{ $row['branch'] }}</td>
                                            <td>{{ $row['group'] }}</td>
                                            <td class="text-end">{{ number_format($row['outstanding_balance'], 0) }}</td>
                                            <td class="text-end text-warning fw-bold">{{ number_format($row['overdue_amount'], 0) }}</td>
                                            <td class="text-end text-danger fw-bold">{{ number_format($row['at_risk_amount'], 0) }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $row['overdue_ratio'] > 50 ? 'danger' : ($row['overdue_ratio'] > 25 ? 'warning' : ($row['overdue_ratio'] > 0 ? 'info' : 'success')) }}">
                                                    {{ $row['overdue_ratio'] }}%
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $row['days_in_arrears'] }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $row['risk_level'] == 'Critical' ? 'danger' : ($row['risk_level'] == 'High' ? 'warning' : ($row['risk_level'] == 'Medium' ? 'info' : 'success')) }}">
                                                    {{ $row['risk_level'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $row['exposure_category'] == 'Critical Exposure' ? 'danger' : ($row['exposure_category'] == 'High Exposure' ? 'warning' : 'secondary') }}">
                                                    {{ $row['exposure_category'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                        
                                        <!-- Summary Row -->
                                        <tr class="table-secondary fw-bold">
                                            <td colspan="7" class="text-center">TOTALS</td>
                                            <td class="text-end">TZS {{ number_format(array_sum(array_column($analysisData, 'outstanding_balance')), 0) }}</td>
                                            <td class="text-end">TZS {{ number_format(array_sum(array_column($analysisData, 'overdue_amount')), 0) }}</td>
                                            <td class="text-end">TZS {{ number_format(array_sum(array_column($analysisData, 'at_risk_amount')), 0) }}</td>
                                            <td class="text-center">
                                                {{ array_sum(array_column($analysisData, 'outstanding_balance')) > 0 ? 
                                                   number_format((array_sum(array_column($analysisData, 'overdue_amount')) / array_sum(array_column($analysisData, 'outstanding_balance'))) * 100, 1) : '0' }}%
                                            </td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">{{ count($analysisData) }} Loans</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="14" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="bx bx-info-circle fs-1"></i>
                                                    <p class="mt-2">No loans found matching the selected criteria.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function exportToExcel() {
        const form = document.getElementById('filterForm');
        form.action = '{{ route('accounting.loans.reports.internal_portfolio_analysis.export_excel') }}';
        form.submit();
        form.action = '{{ route('accounting.loans.reports.internal_portfolio_analysis') }}';
    }

    function exportToPdf() {
        const form = document.getElementById('filterForm');
        form.action = '{{ route('accounting.loans.reports.internal_portfolio_analysis.export_pdf') }}';
        form.submit();
        form.action = '{{ route('accounting.loans.reports.internal_portfolio_analysis') }}';
    }
</script>
@endsection
