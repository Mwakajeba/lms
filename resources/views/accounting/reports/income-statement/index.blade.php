@extends('layouts.main')

@section('title', 'Income Statement Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Income Statement Report', 'url' => '#', 'icon' => 'bx bx-line-chart']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-line-chart me-2"></i>Income Statement Report</h5>
                                <small class="text-muted">Generate income statement for the specified period</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" onclick="generateReport()">
                                    <i class="bx bx-refresh me-1"></i> Generate Report
                                </button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')">
                                            <i class="bx bx-file-pdf me-2"></i> Export PDF
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">
                                            <i class="bx bx-file me-2"></i> Export Excel
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section -->
                        <form id="incomeStatementForm" method="GET" action="{{ route('accounting.reports.income-statement') }}">
                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ $startDate }}" required>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ $endDate }}" required>
                                </div>

                                <!-- Reporting Type -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="reporting_type" class="form-label">Reporting Type</label>
                                    <select class="form-select" id="reporting_type" name="reporting_type" required>
                                        <option value="accrual" {{ $reportingType === 'accrual' ? 'selected' : '' }}>Accrual Basis</option>
                                        <option value="cash" {{ $reportingType === 'cash' ? 'selected' : '' }}>Cash Basis</option>
                                    </select>
                                </div>

                                <!-- Branch (Assigned) -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        @if(($branches->count() ?? 0) > 1)
                                            <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All Branches</option>
                                        @endif
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12 mt-2">
                                    <div class="border rounded p-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label mb-0">Comparative Periods (optional)</label>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addComparative()">
                                                <i class="bx bx-plus"></i> Add Comparative
                                            </button>
                                        </div>
                                        <div id="comparatives_container">
                                            @if(!empty($comparativeColumns))
                                                @foreach($comparativeColumns as $idx => $col)
                                                    <div class="row g-2 align-items-end mb-2 comparative-row">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Name</label>
                                                            <input type="text" class="form-control" name="comparative_columns[{{ $idx }}][name]" value="{{ $col['name'] ?? ('Comparative '.($idx+1)) }}" placeholder="e.g. Previous Period">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Start Date</label>
                                                            <input type="date" class="form-control" name="comparative_columns[{{ $idx }}][start_date]" value="{{ $col['start_date'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">End Date</label>
                                                            <input type="date" class="form-control" name="comparative_columns[{{ $idx }}][end_date]" value="{{ $col['end_date'] ?? '' }}">
                                                        </div>
                                                        <div class="col-md-3 text-end">
                                                            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.comparative-row').remove()">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        @if(isset($incomeStatementData))
                        <!-- Results -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">INCOME STATEMENT</h6>
                                                <small class="text-muted">
                                                    @if($startDate === $endDate)
                                                        As at: {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} | 
                                                    @else
                                                        Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} | 
                                                    @endif
                                                    Basis: {{ ucfirst($reportingType) }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($incomeStatementData) && (count($incomeStatementData['data']['revenues'] ?? []) > 0 || count($incomeStatementData['data']['expenses'] ?? []) > 0))
                                            <div class="table-responsive">
                                                @php
                                                    $comparatives = $incomeStatementData['comparative'] ?? [];
                                                    $comparativesCount = is_array($comparatives) ? count($comparatives) : 0;
                                                @endphp
                                                <table class="table table-bordered table-striped">
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="{{ 3 + $comparativesCount }}" style="text-align: center; font-weight:bold">INCOME STATEMENT</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Financial Statement Line Item</th>
                                                            <th>Ledger Account</th>
                                                            <th>Current Period</th>
                                                            @if($comparativesCount)
                                                                @foreach($comparatives as $label => $comp)
                                                                    <th>{{ $label }}</th>
                                                                @endforeach
                                                            @endif
                                                        </tr>
                                                        <!-- Revenue Section -->
                                                        <tr class="line-item-header">
                                                            <td><b>Revenue</b></td>
                                                            <td colspan="{{ 2 + $comparativesCount }}"></td>
                                                        </tr>
                                                        @php
                                                            $revenueTotalCurrent = 0;
                                                            $compRevenueTotals = [];
                                                        @endphp

                                                        @foreach($incomeStatementData['data']['revenues'] as $group => $accounts)
                                                            @foreach($accounts as $account)
                                                                @php
                                                                    $rowComps = [];
                                                                    foreach ($comparatives as $label => $cdata) {
                                                                        $prev = collect($cdata['revenues'][$group] ?? [])->firstWhere('account_id', $account['account_id'])['sum'] ?? 0;
                                                                        $rowComps[$label] = $prev;
                                                                        $compRevenueTotals[$label] = ($compRevenueTotals[$label] ?? 0) + $prev;
                                                                    }
                                                                @endphp

                                                                @if($account['sum'] != 0 || collect($rowComps)->sum() != 0)
                                                                    @php
                                                                        $revenueTotalCurrent += $account['sum'];
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $group }}</td>
                                                                        <td>{{ $account['account_code'] }} - {{ $account['account'] }}</td>
                                                                        <td class="right-align">{{ number_format($account['sum'], 2) }}</td>
                                                                        @if($comparativesCount)
                                                                            @foreach($comparatives as $label => $ignored)
                                                                                <td class="right-align">{{ number_format($rowComps[$label] ?? 0, 2) }}</td>
                                                                            @endforeach
                                                                        @endif
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @endforeach

                                                        <tr>
                                                            <td><b>Total Revenue</b></td>
                                                            <td></td>
                                                            <td class="right-align total"><b>{{ number_format($revenueTotalCurrent, 2) }}</b></td>
                                                            @if($comparativesCount)
                                                                @foreach($comparatives as $label => $ignored)
                                                                    <td class="right-align total"><b>{{ number_format($compRevenueTotals[$label] ?? 0, 2) }}</b></td>
                                                                @endforeach
                                                            @endif
                                                        </tr>

                                                        <!-- Expense Section -->
                                                        <tr class="line-item-header">
                                                            <td><b>Expenses</b></td>
                                                            <td colspan="{{ 2 + $comparativesCount }}"></td>
                                                        </tr>
                                                        @php
                                                            $expenseTotalCurrent = 0;
                                                            $compExpenseTotals = [];
                                                        @endphp

                                                        @foreach($incomeStatementData['data']['expenses'] as $group => $accounts)
                                                            @foreach($accounts as $account)
                                                                @php
                                                                    $rowComps = [];
                                                                    foreach ($comparatives as $label => $cdata) {
                                                                        $prev = collect($cdata['expenses'][$group] ?? [])->firstWhere('account_id', $account['account_id'])['sum'] ?? 0;
                                                                        $rowComps[$label] = $prev;
                                                                        $compExpenseTotals[$label] = ($compExpenseTotals[$label] ?? 0) + $prev;
                                                                    }
                                                                @endphp

                                                                @if($account['sum'] != 0 || collect($rowComps)->sum() != 0)
                                                                    @php
                                                                        $expenseTotalCurrent += $account['sum'];
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $group }}</td>
                                                                        <td>{{ $account['account_code'] }} - {{ $account['account'] }}</td>
                                                                        <td class="right-align">{{ number_format($account['sum'], 2) }}</td>
                                                                        @if($comparativesCount)
                                                                            @foreach($comparatives as $label => $ignored)
                                                                                <td class="right-align">{{ number_format($rowComps[$label] ?? 0, 2) }}</td>
                                                                            @endforeach
                                                                        @endif
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        @endforeach

                                                        <tr>
                                                            <td><b>Total Expenses</b></td>
                                                            <td></td>
                                                            <td class="right-align total"><b>{{ number_format($expenseTotalCurrent, 2) }}</b></td>
                                                            @if($comparativesCount)
                                                                @foreach($comparatives as $label => $ignored)
                                                                    <td class="right-align total"><b>{{ number_format($compExpenseTotals[$label] ?? 0, 2) }}</b></td>
                                                                @endforeach
                                                            @endif
                                                        </tr>

                                                        <!-- Net Income -->
                                                        <tr>
                                                            <td><b>Net Income</b></td>
                                                            <td></td>
                                                            @php $netCurrent = $revenueTotalCurrent - $expenseTotalCurrent; @endphp
                                                            <td class="right-align total"><b>{{ number_format($netCurrent, 2) }}</b></td>
                                                            @if($comparativesCount)
                                                                @foreach($comparatives as $label => $ignored)
                                                                    @php $netComp = ($compRevenueTotals[$label] ?? 0) - ($compExpenseTotals[$label] ?? 0); @endphp
                                                                    <td class="right-align total"><b>{{ number_format($netComp, 2) }}</b></td>
                                                                @endforeach
                                                            @endif
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="bx bx-info-circle fs-1 text-muted"></i>
                                                <p class="mt-2 text-muted">No income statement data found for the selected criteria.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateReport() {
    document.getElementById('incomeStatementForm').submit();
}

function exportReport(type) {
    const form = document.getElementById('incomeStatementForm');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    const url = '{{ route("accounting.reports.income-statement.export") }}?' + new URLSearchParams(formData);
    
    // Show loading state
    Swal.fire({
        title: 'Generating Report...',
        text: 'Please wait while we prepare your ' + type.toUpperCase() + ' report.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Download the file
    window.location.href = url;

    // close the loading state after a short delay
    setTimeout(() => {
        Swal.close();
    }, 2000);
}

function addComparative(){
    const container = document.getElementById('comparatives_container');
    const idx = container.querySelectorAll('.comparative-row').length;
    const row = document.createElement('div');
    row.className = 'row g-2 align-items-end mb-2 comparative-row';
    row.innerHTML = `
        <div class="col-md-3">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="comparative_columns[${idx}][name]" placeholder="e.g. Previous Period">
        </div>
        <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" name="comparative_columns[${idx}][start_date]">
        </div>
        <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" name="comparative_columns[${idx}][end_date]">
        </div>
        <div class="col-md-3 text-end">
            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.comparative-row').remove()">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
}
</script>
@endsection 