@extends('layouts.main')

@section('title', 'Loan Arrears Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Loan Arrears Report', 'url' => '#', 'icon' => 'bx bx-error-circle']
        ]" />

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold text-dark mb-0">Loan Arrears Report</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('accounting.loans.reports.loan_arrears.export_excel', request()->query()) }}" 
                   class="btn btn-success btn-sm">
                    <i class="bx bx-download me-1"></i>Export Excel
                </a>
                <a href="{{ route('accounting.loans.reports.loan_arrears.export_pdf', request()->query()) }}" 
                   class="btn btn-danger btn-sm">
                    <i class="bx bx-download me-1"></i>Export PDF
                </a>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bx bx-filter me-2"></i>Filter Options</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.loans.reports.loan_arrears') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select class="form-select" name="branch_id" id="branch_id">
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
                        <div class="col-md-3">
                            <label for="group_id" class="form-label">Group</label>
                            <select class="form-select" name="group_id" id="group_id">
                                <option value="">All Groups</option>
                                @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ $groupId == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="loan_officer_id" class="form-label">Loan Officer</label>
                            <select class="form-select" name="loan_officer_id" id="loan_officer_id">
                                <option value="">All Officers</option>
                                @foreach($loanOfficers as $officer)
                                <option value="{{ $officer->id }}" {{ $loanOfficerId == $officer->id ? 'selected' : '' }}>
                                    {{ $officer->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary me-2" id="filterBtn">
                                <i class="bx bx-search me-1"></i>Filter
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                                <i class="bx bx-refresh me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4" id="summaryCards">
            <div class="col-md-3">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Total Loans in Arrears</h6>
                                <h4 class="mb-0" id="totalLoans">0</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-error-circle fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Total Arrears Amount</h6>
                                <h4 class="mb-0" id="totalArrears">TZS 0.00</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-money fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Average Days in Arrears</h6>
                                <h4 class="mb-0" id="avgDays">0 days</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-time-five fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">Critical Cases (90+ days)</h6>
                                <h4 class="mb-0" id="criticalCases">0</h4>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-error fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Arrears Data Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bx bx-table me-2"></i>Loan Arrears Details</h6>
            </div>
                        <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Customer No</th>
                                <th>Phone</th>
                                <th>Loan No</th>
                                <th>Loan Amount</th>
                                <th>Disbursed Date</th>
                                <th>Branch</th>
                                <th>Group</th>
                                <th>Loan Officer</th>
                                <th>Arrears Amount</th>
                                <th>Days in Arrears</th>
                                <th>First Overdue</th>
                                <th>Overdue Items</th>
                                <th>Severity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($arrearsData) && count($arrearsData) > 0)
                                @foreach($arrearsData as $index => $arrears)
                                <tr class="@if($arrears['arrears_severity'] === 'Critical') table-danger @elseif($arrears['arrears_severity'] === 'High') table-warning @endif">
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="fw-bold">{{ $arrears['customer'] }}</td>
                                    <td>{{ $arrears['customer_no'] }}</td>
                                    <td>{{ $arrears['phone'] }}</td>
                                    <td class="fw-bold text-primary">{{ $arrears['loan_no'] }}</td>
                                    <td class="text-end">TZS {{ number_format($arrears['loan_amount'], 2) }}</td>
                                    <td>{{ $arrears['disbursed_date'] }}</td>
                                    <td>{{ $arrears['branch'] }}</td>
                                    <td>{{ $arrears['group'] }}</td>
                                    <td>{{ $arrears['loan_officer'] }}</td>
                                    <td class="text-end">
                                        <span class="fw-bold text-danger">TZS {{ number_format($arrears['arrears_amount'], 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">{{ $arrears['days_in_arrears'] }} days</span>
                                    </td>
                                    <td>{{ $arrears['first_overdue_date'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $arrears['overdue_schedules_count'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $badgeClass = match($arrears['arrears_severity']) {
                                                'Low' => 'bg-success',
                                                'Medium' => 'bg-warning text-dark',
                                                'High' => 'bg-danger',
                                                'Critical' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $arrears['arrears_severity'] }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="15" class="text-center text-muted py-4">
                                        <i class="bx bx-info-circle me-2"></i>No loans in arrears found
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Calculate and update summary cards immediately
        updateSummaryCards();
        
        // Filter button click event
        $('#filterBtn').on('click', function() {
            $('#filterForm').submit();
        });
        
        // Reset button click event
        $('#resetBtn').on('click', function() {
            $('#branch_id').val('');
            $('#group_id').val('');
            $('#loan_officer_id').val('');
            $('#filterForm').submit();
        });
    });
    
    function updateSummaryCards() {
        var arrearsData = @json($arrearsData ?? []);
        
        if (arrearsData.length > 0) {
            let totalLoans = arrearsData.length;
            let totalArrears = arrearsData.reduce((sum, row) => sum + parseFloat(row.arrears_amount || 0), 0);
            let avgDays = Math.round(arrearsData.reduce((sum, row) => sum + parseInt(row.days_in_arrears || 0), 0) / totalLoans);
            let criticalCases = arrearsData.filter(row => parseInt(row.days_in_arrears || 0) > 90).length;
            
            $('#totalLoans').text(totalLoans);
            $('#totalArrears').text('TZS ' + totalArrears.toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#avgDays').text(avgDays + ' days');
            $('#criticalCases').text(criticalCases);
        } else {
            $('#totalLoans').text('0');
            $('#totalArrears').text('TZS 0.00');
            $('#avgDays').text('0 days');
            $('#criticalCases').text('0');
        }
    }
</script>
@endpush
