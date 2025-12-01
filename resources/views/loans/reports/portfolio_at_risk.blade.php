@extends('layouts.main')

@section('title', 'Portfolio at Risk (PAR) Report')

@push('styles')
<style>
    .summary-card {
        background: linear-gradient(135deg, #fd7e14 0%, #dc3545 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .summary-card h3 {
        font-size: 14px;
        margin-bottom: 10px;
        opacity: 0.9;
    }
    
    .summary-card .value {
        font-size: 24px;
        font-weight: bold;
        margin: 0;
    }
    
    .filter-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }
    
    .risk-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: bold;
    }
    
    .risk-low { background-color: #d4edda; color: #155724; }
    .risk-medium { background-color: #fff3cd; color: #856404; }
    .risk-high { background-color: #f8d7da; color: #721c24; }
    .risk-critical { background-color: #f5c6cb; color: #721c24; }
    
    .at-risk { color: #dc3545; font-weight: bold; }
    .not-at-risk { color: #28a745; }
    
    .table-responsive {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        font-size: 12px;
    }
    
    .table td {
        font-size: 12px;
        vertical-align: middle;
    }
    
    .btn-export {
        margin-left: 5px;
    }
    
    .par-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 5px;
    }
    
    .par-safe { background-color: #28a745; }
    .par-warning { background-color: #ffc107; }
    .par-danger { background-color: #dc3545; }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
                ['label' => 'Portfolio at Risk Report', 'url' => '#', 'icon' => 'bx bx-error-circle']
            ]" />
        
        <h6 class="mb-0 text-uppercase">Portfolio at Risk (PAR) Report</h6>
        <hr />

        <!-- Summary Cards -->
        @if(!empty($parData))
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <h3>Total Portfolio</h3>
                    <p class="value" id="totalPortfolio">TZS {{ number_format(array_sum(array_column($parData, 'outstanding_balance')), 2) }}</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <h3>At Risk Amount</h3>
                    <p class="value" id="atRiskAmount">TZS {{ number_format(array_sum(array_column($parData, 'at_risk_amount')), 2) }}</p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <h3>PAR {{ $parDays }} Ratio</h3>
                    <p class="value" id="parRatio">
                        {{ array_sum(array_column($parData, 'outstanding_balance')) > 0 ? 
                           number_format((array_sum(array_column($parData, 'at_risk_amount')) / array_sum(array_column($parData, 'outstanding_balance'))) * 100, 1) : '0' }}%
                    </p>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="summary-card">
                    <h3>Loans at Risk</h3>
                    <p class="value" id="loansAtRisk">
                        {{ count(array_filter($parData, function($item) { return $item['is_at_risk']; })) }} / {{ count($parData) }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Filter Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filters</h5>
                        <form method="GET" action="{{ route('accounting.loans.reports.portfolio_at_risk') }}" id="filterForm">
                            <div class="row">
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
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary" id="filterBtn">
                                            <i class="bx bx-search me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Export Buttons -->
                            @if(!empty($parData))
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-success btn-export" onclick="exportReport('excel')">
                                            <i class="bx bx-file me-1"></i> Export Excel
                                        </button>
                                        <button type="button" class="btn btn-danger btn-export" onclick="exportReport('pdf')">
                                            <i class="bx bx-file-pdf me-1"></i> Export PDF
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-export" onclick="resetFilters()">
                                            <i class="bx bx-refresh me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            Portfolio at Risk Analysis
                            @if(!empty($parData))
                                <span class="badge bg-primary ms-2">{{ count($parData) }} Loans</span>
                                <span class="badge bg-warning ms-1">PAR {{ $parDays }}</span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(!empty($parData))
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="parTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 3%;">#</th>
                                            <th style="width: 10%;">Customer</th>
                                            <th style="width: 6%;">Customer No</th>
                                            <th style="width: 7%;">Phone</th>
                                            <th style="width: 6%;">Loan No</th>
                                            <th style="width: 7%;">Loan Amount</th>
                                            <th style="width: 6%;">Branch</th>
                                            <th style="width: 6%;">Group</th>
                                            <th style="width: 7%;">Officer</th>
                                            <th style="width: 8%;">Outstanding</th>
                                            <th style="width: 8%;">At Risk Amount</th>
                                            <th style="width: 6%;">Risk %</th>
                                            <th style="width: 5%;">Days</th>
                                            <th style="width: 6%;">Risk Level</th>
                                            <th style="width: 4%;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($parData as $index => $row)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $row['customer'] }}</td>
                                                <td class="text-center">{{ $row['customer_no'] }}</td>
                                                <td class="text-center">{{ $row['phone'] }}</td>
                                                <td class="text-center">{{ $row['loan_no'] }}</td>
                                                <td class="text-end">{{ number_format($row['loan_amount'], 0) }}</td>
                                                <td>{{ $row['branch'] }}</td>
                                                <td>{{ $row['group'] }}</td>
                                                <td>{{ $row['loan_officer'] }}</td>
                                                <td class="text-end">{{ number_format($row['outstanding_balance'], 2) }}</td>
                                                <td class="text-end at-risk">{{ number_format($row['at_risk_amount'], 2) }}</td>
                                                <td class="text-center">
                                                    <span class="par-indicator 
                                                        @if($row['risk_percentage'] == 0) par-safe 
                                                        @elseif($row['risk_percentage'] < 50) par-warning 
                                                        @else par-danger @endif">
                                                    </span>
                                                    {{ $row['risk_percentage'] }}%
                                                </td>
                                                <td class="text-center">{{ $row['days_in_arrears'] }}</td>
                                                <td class="text-center">
                                                    <span class="risk-badge risk-{{ strtolower($row['risk_level']) }}">
                                                        {{ $row['risk_level'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($row['is_at_risk'])
                                                        <i class="bx bx-error-circle text-danger" title="At Risk"></i>
                                                    @else
                                                        <i class="bx bx-check-circle text-success" title="Safe"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-dark">
                                            <td colspan="9" class="text-center fw-bold">TOTALS</td>
                                            <td class="text-end fw-bold">{{ number_format(array_sum(array_column($parData, 'outstanding_balance')), 2) }}</td>
                                            <td class="text-end fw-bold">{{ number_format(array_sum(array_column($parData, 'at_risk_amount')), 2) }}</td>
                                            <td class="text-center fw-bold">
                                                {{ array_sum(array_column($parData, 'outstanding_balance')) > 0 ? 
                                                   number_format((array_sum(array_column($parData, 'at_risk_amount')) / array_sum(array_column($parData, 'outstanding_balance'))) * 100, 1) : '0' }}%
                                            </td>
                                            <td class="text-center fw-bold">-</td>
                                            <td class="text-center fw-bold">-</td>
                                            <td class="text-center fw-bold">{{ count($parData) }} Loans</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-error-circle" style="font-size: 48px; color: #ccc;"></i>
                                <h5 class="mt-3 text-muted">No Data Found</h5>
                                <p class="text-muted">Please select filters and click "Filter" to generate the PAR report.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function exportReport(format) {
        const form = document.getElementById('filterForm');
        const originalAction = form.action;
        
        if (format === 'excel') {
            form.action = '{{ route('accounting.loans.reports.portfolio_at_risk.export_excel') }}';
        } else if (format === 'pdf') {
            form.action = '{{ route('accounting.loans.reports.portfolio_at_risk.export_pdf') }}';
        }
        
        form.submit();
        
        // Restore original action
        setTimeout(() => {
            form.action = originalAction;
        }, 100);
    }
    
    function resetFilters() {
        document.getElementById('as_of_date').value = '{{ \Carbon\Carbon::now()->toDateString() }}';
        document.getElementById('par_days').value = '30';
        document.getElementById('branch_id').value = '';
        document.getElementById('group_id').value = '';
        document.getElementById('loan_officer_id').value = '';
        document.getElementById('filterForm').submit();
    }
    
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();
        
        // Add hover effects for risk indicators
        $('.par-indicator').hover(
            function() {
                $(this).css('transform', 'scale(1.2)');
            },
            function() {
                $(this).css('transform', 'scale(1)');
            }
        );
    });
</script>
@endpush
