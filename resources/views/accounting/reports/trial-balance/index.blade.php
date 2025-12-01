@extends('layouts.main')

@section('title', 'Trial Balance Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Trial Balance Report', 'url' => '#', 'icon' => 'bx bx-calculator']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-calculator me-2"></i>Trial Balance Report</h5>
                                <small class="text-muted">Generate trial balance for the specified period</small>
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
                        <!-- Filters -->
                        <form id="trialBalanceForm" method="GET" action="{{ route('accounting.reports.trial-balance') }}">
                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}" required>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}" required>
                                </div>

                                <!-- Reporting Type -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="reporting_type" class="form-label">Type</label>
                                    <select class="form-select" id="reporting_type" name="reporting_type">
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

                                <!-- Layout -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="layout" class="form-label">Trial Balance Layout</label>
                                    <select class="form-select" id="layout" name="layout">
                                        <option value="single_column" {{ $layout === 'single_column' ? 'selected' : '' }}>Single Column</option>
                                        <option value="double_column" {{ $layout === 'double_column' ? 'selected' : '' }}>Double Column</option>
                                        <option value="multi_column" {{ $layout === 'multi_column' ? 'selected' : '' }}>Multiple Columns</option>
                                    </select>
                                </div>

                                <!-- Level of Detail -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="level_of_detail" class="form-label">Level of Detail</label>
                                    <select class="form-select" id="level_of_detail" name="level_of_detail">
                                        <option value="summary" {{ $levelOfDetail === 'summary' ? 'selected' : '' }}>Summary</option>
                                        <option value="detailed" {{ $levelOfDetail === 'detailed' ? 'selected' : '' }}>Detailed</option>
                                    </select>
                                </div>

                                <!-- Comparative Period (optional) -->
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

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bx bx-search me-1"></i>Generate Report
                                    </button>
                                    <a href="{{ route('accounting.reports.trial-balance') }}" class="btn btn-outline-secondary ms-2">
                                        <i class="bx bx-reset me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Report Results -->
                        @if(isset($trialBalanceData))
                        <div class="mt-5">
                        <div class="row">
                            <div class="col-12">
                                <div class="panel-body" style="background: #fff">
                                    <div id="printingArea">
                                        @php
                                            $comparatives = $trialBalanceData['comparative'] ?? [];
                                            $comparativesCount = is_array($comparatives) ? count($comparatives) : 0;
                                        @endphp
                                        <table id="myTable" class="table table-bordered">
                                            <tr>
                                                <td colspan="{{ $layout === 'multi_column' ? 9 : ($layout === 'double_column' ? (4 + ($comparativesCount*2)) : (3 + $comparativesCount)) }}"
                                                style="text-align: center; font-weight:bold">
                                                {{ $user->company->name ?? 'SMARTFINANCE' }}
                                            </td>
                                            </tr>
                                            <tr>
                                                <td colspan="{{ $layout === 'multi_column' ? 9 : ($layout === 'double_column' ? (4 + ($comparativesCount*2)) : (3 + $comparativesCount)) }}"
                                                    style="text-align: center; font-weight:bold">Trial Balance</td>
                                            </tr>
                                            <tr>
                                                <td colspan="{{ $layout === 'multi_column' ? 9 : ($layout === 'double_column' ? (4 + ($comparativesCount*2)) : (3 + $comparativesCount)) }}"
                                                    style="text-align: center; font-weight:bold">AS At {{ date('d-m-Y', strtotime($endDate)) }}
                                                    </td>
                                            </tr>

                                            @if($layout === 'double_column')
                                                <tr style="font-weight:bold">
                                                    <th>ACCOUNT NAME</th>
                                                    <th>ACCOUNT CODE</th>
                                                    <th>DEBIT</th>
                                                    <th>CREDIT</th>
                                                    @if($comparativesCount)
                                                        @foreach($comparatives as $label => $comp)
                                                            <th>DEBIT ({{ $label }})</th>
                                                            <th>CREDIT ({{ $label }})</th>
                                                        @endforeach
                                                    @endif
                                                </tr>
                                                @php
                                                    $totalDebit = 0;
                                                    $totalCredit = 0;
                                                    $compTotals = [];
                                                @endphp
                                                @foreach($trialBalanceData['data'] as $class => $accounts)
                                                    @foreach($accounts as $account)
                                                        @if(floatval($account->sum) != 0)
                                                            @php
                                                                $currentDebit = $account->nature === 'debit' ? abs(floatval($account->sum)) : 0;
                                                                $currentCredit = $account->nature === 'credit' ? abs(floatval($account->sum)) : 0;
                                                                $totalDebit += $currentDebit;
                                                                $totalCredit += $currentCredit;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $account->account }}</td>
                                                                <td class="account-code">{{ $account->account_code }}</td>
                                                                <td class="balance">{{ $currentDebit ? number_format($currentDebit, 2) : '-' }}</td>
                                                                <td class="balance">{{ $currentCredit ? number_format($currentCredit, 2) : '-' }}</td>
                                                                @if($comparativesCount)
                                                                    @foreach($comparatives as $label => $compData)
                                                                        @php
                                                                            $compAccount = collect($compData[$class] ?? [])->first(function($a) use ($account) {
                                                                                return isset($a->account_code) && $a->account_code == $account->account_code;
                                                                            });
                                                                            $compDebit = $compAccount && $compAccount->nature === 'debit' ? abs(floatval($compAccount->sum)) : 0;
                                                                            $compCredit = $compAccount && $compAccount->nature === 'credit' ? abs(floatval($compAccount->sum)) : 0;
                                                                            $compTotals[$label]['debit'] = ($compTotals[$label]['debit'] ?? 0) + $compDebit;
                                                                            $compTotals[$label]['credit'] = ($compTotals[$label]['credit'] ?? 0) + $compCredit;
                                                                        @endphp
                                                                        <td class="balance">{{ $compDebit ? number_format($compDebit, 2) : '-' }}</td>
                                                                        <td class="balance">{{ $compCredit ? number_format($compCredit, 2) : '-' }}</td>
                                                                    @endforeach
                                                                @endif
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                                <tr style="font-weight: bold">
                                                    <td colspan="2" style="text-align: right;">TOTAL</td>
                                                    <td class="balance">{{ number_format($totalDebit, 2) }}</td>
                                                    <td class="balance">{{ number_format($totalCredit, 2) }}</td>
                                                    @if($comparativesCount)
                                                        @foreach($comparatives as $label => $ignored)
                                                            <td class="balance">{{ number_format($compTotals[$label]['debit'] ?? 0, 2) }}</td>
                                                            <td class="balance">{{ number_format($compTotals[$label]['credit'] ?? 0, 2) }}</td>
                                                        @endforeach
                                                    @endif
                                                </tr>
                                                <tr style="font-weight: bold">
                                                    <td colspan="2" style="text-align: right;">Net Balance (Debit - Credit)</td>
                                                    <td colspan="{{ 2 + ($comparativesCount ? ($comparativesCount*2) : 0) }}" class="balance"
                                                        style="color: {{ ($totalDebit - $totalCredit) == 0 ? 'green' : 'red' }};">
                                                        {{ number_format($totalDebit - $totalCredit, 2) }}
                                                    </td>
                                                </tr>
                                            @elseif($layout === 'single_column')
                                                <tr style="font-weight:bold">
                                                    <th>ACCOUNT NAME</th>
                                                    <th>ACCOUNT CODE</th>
                                                    <th style="text-align: right">BALANCE</th>
                                                    @if($comparativesCount)
                                                        @foreach($comparatives as $label => $comp)
                                                            <th style="text-align: right">BAL ({{ $label }})</th>
                                                        @endforeach
                                                    @endif
                                                </tr>
                                                @php
                                                    $totalDebit = 0;
                                                    $totalCredit = 0;
                                                    $compTotals = [];
                                                @endphp
                                                @foreach($trialBalanceData['data'] as $class => $accounts)
                                                    @foreach($accounts as $account)
                                                        @if(floatval($account->sum) != 0)
                                                            <tr>
                                                                <td>{{ $account->account }}</td>
                                                                <td class="account-code">{{ $account->account_code }}</td>
                                                                <td class="balance">
                                                                    @if($account->nature === 'credit')
                                                                        ({{ number_format(abs(floatval($account->sum)), 2) }})
                                                                        @php $totalCredit += abs(floatval($account->sum)); @endphp
                                                                    @else
                                                                        {{ number_format(abs(floatval($account->sum)), 2) }}
                                                                        @php $totalDebit += abs(floatval($account->sum)); @endphp
                                                                    @endif
                                                                </td>
                                                                @if($comparativesCount)
                                                                    @foreach($comparatives as $label => $compData)
                                                                        @php
                                                                            $compAccount = collect($compData[$class] ?? [])->first(function($a) use ($account) {
                                                                                return isset($a->account_code) && $a->account_code == $account->account_code;
                                                                            });
                                                                            $compVal = 0;
                                                                            if ($compAccount) {
                                                                                if ($compAccount->nature === 'credit') {
                                                                                    $compVal = -1 * abs(floatval($compAccount->sum));
                                                                                } else {
                                                                                    $compVal = abs(floatval($compAccount->sum));
                                                                                }
                                                                            }
                                                                            $compTotals[$label] = ($compTotals[$label] ?? 0) + $compVal;
                                                                        @endphp
                                                                        <td class="balance">{{ $compVal < 0 ? '('.number_format(abs($compVal), 2).')' : number_format($compVal, 2) }}</td>
                                                                    @endforeach
                                                                @endif
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                                <tr style="font-weight: bold">
                                                    <td colspan="2" style="text-align: right;">Net Balance (Debit - Credit)</td>
                                                    <td class="balance"
                                                        style="color: {{ ($totalDebit - $totalCredit) == 0 ? 'green' : 'red' }};">
                                                        {{ number_format($totalDebit - $totalCredit, 2) }}
                                                    </td>
                                                    @if($comparativesCount)
                                                        @foreach($comparatives as $label => $ignored)
                                                            @php $tv = $compTotals[$label] ?? 0; @endphp
                                                            <td class="balance" style="color: {{ $tv == 0 ? 'green' : 'red' }};">
                                                                {{ $tv < 0 ? '('.number_format(abs($tv), 2).')' : number_format($tv, 2) }}
                                                            </td>
                                                        @endforeach
                                                    @endif
                                                </tr>
                                            @else
                                                <tr style="font-weight:bold">
                                                    <th rowspan="2">ACCOUNT NAME</th>
                                                    <th rowspan="2">ACCOUNT CODE</th>
                                                    <th colspan="2">OPENING BALANCES</th>
                                                    <th colspan="2">CURRENT YEAR CHANGES</th>
                                                    <th colspan="2">CLOSING BALANCES</th>
                                                    <th rowspan="2">DIFFERENCE</th>
                                                </tr>
                                                <tr style="font-weight:bold">
                                                    <th>DR</th>
                                                    <th>CR</th>
                                                    <th>DR</th>
                                                    <th>CR</th>
                                                    <th>DR</th>
                                                    <th>CR</th>
                                                </tr>
                                                
                                                @php
                                                    $totalOpeningDr = 0;
                                                    $totalOpeningCr = 0;
                                                    $totalChangeDr = 0;
                                                    $totalChangeCr = 0;
                                                    $totalClosingDr = 0;
                                                    $totalClosingCr = 0;
                                                    $totalDiff = 0;
                                                @endphp
                                                
                                                @foreach($trialBalanceData['data'] as $class => $accounts)
                                                    @foreach($accounts as $account)
                                                        @php
                                                            $openingDr = property_exists($account, 'opening_debit') ? floatval($account->opening_debit) : 0;
                                                            $openingCr = property_exists($account, 'opening_credit') ? floatval($account->opening_credit) : 0;
                                                            $changeDr  = property_exists($account, 'change_debit') ? floatval($account->change_debit) : 0;
                                                            $changeCr  = property_exists($account, 'change_credit') ? floatval($account->change_credit) : 0;
                                                            $closingDr = property_exists($account, 'closing_debit') ? floatval($account->closing_debit) : 0;
                                                            $closingCr = property_exists($account, 'closing_credit') ? floatval($account->closing_credit) : 0;
                                                
                                                            $openingDiff = $openingDr - $openingCr;
                                                            $changeDiff = $changeDr - $changeCr;
                                                            $closingDiff = $closingDr - $closingCr;
                                                            $difference = $closingDiff;
                                                
                                                            $totalOpeningDr += $openingDiff > 0 ? $openingDiff : 0;
                                                            $totalOpeningCr += $openingDiff < 0 ? abs($openingDiff) : 0;
                                                
                                                            $totalChangeDr += $changeDiff > 0 ? $changeDiff : 0;
                                                            $totalChangeCr += $changeDiff < 0 ? abs($changeDiff) : 0;
                                                
                                                            $totalClosingDr += $closingDiff > 0 ? $closingDiff : 0;
                                                            $totalClosingCr += $closingDiff < 0 ? abs($closingDiff) : 0;
                                                
                                                            $totalDiff += $difference;
                                                        @endphp
                                                
                                                        @if($openingDiff != 0 || $changeDiff != 0 || $closingDiff != 0)
                                                            <tr>
                                                                <td>{{ $account->account }}</td>
                                                                <td class="account-code">{{ $account->account_code }}</td>
                                                
                                                                <td class="balance">{{ $openingDiff > 0 ? number_format(abs($openingDiff), 2) : '-' }}</td>
                                                                <td class="balance">{{ $openingDiff < 0 ? number_format(abs($openingDiff), 2) : '-' }}</td>
                                                
                                                                <td class="balance">{{ $changeDiff > 0 ? number_format(abs($changeDiff), 2) : '-' }}</td>
                                                                <td class="balance">{{ $changeDiff < 0 ? number_format(abs($changeDiff), 2) : '-' }}</td>
                                                
                                                                <td class="balance">{{ $closingDiff > 0 ? number_format(abs($closingDiff), 2) : '-' }}</td>
                                                                <td class="balance">{{ $closingDiff < 0 ? number_format(abs($closingDiff), 2) : '-' }}</td>
                                                
                                                                <td class="balance">
                                                                    {{ $difference != 0 ? number_format(abs($difference), 2) : '-' }}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                                
                                                <tr style="font-weight: bold">
                                                    <td colspan="2" style="text-align: right;">TOTAL</td>
                                                    <td class="balance">{{ number_format($totalOpeningDr, 2) }}</td>
                                                    <td class="balance">{{ number_format($totalOpeningCr, 2) }}</td>
                                                    <td class="balance">{{ number_format($totalChangeDr, 2) }}</td>
                                                    <td class="balance">{{ number_format($totalChangeCr, 2) }}</td>
                                                    <td class="balance">{{ number_format($totalClosingDr, 2) }}</td>
                                                    <td class="balance">{{ number_format($totalClosingCr, 2) }}</td>
                                                    <td class="balance">
                                                        {{ $totalDiff != 0 ? number_format(abs($totalDiff), 2) : '-' }}
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
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
    const form = document.getElementById('trialBalanceForm');
    form.submit();
}

function exportReport(type) {
    const form = document.getElementById('trialBalanceForm');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    const url = '{{ route("accounting.reports.trial-balance.export") }}?' + new URLSearchParams(formData);
    
    // Show simple loading message
    const loadingMsg = document.createElement('div');
    loadingMsg.innerHTML = 'Generating ' + type.toUpperCase() + ' report...';
    loadingMsg.style.cssText = 'position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#333;color:white;padding:20px;border-radius:5px;z-index:9999;';
    document.body.appendChild(loadingMsg);
    
    // Download the file
    window.location.href = url;

    // Remove loading message after a short delay
    setTimeout(() => {
        if (loadingMsg.parentNode) {
            loadingMsg.parentNode.removeChild(loadingMsg);
        }
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

<style>
.account-code {
    font-family: monospace;
    font-size: 0.9em;
}

.balance {
    text-align: right;
    font-family: monospace;
}

#myTable {
    width: 100%;
    border-collapse: collapse;
}

#myTable th,
#myTable td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

#myTable th {
    background-color: #f8f9fa;
    font-weight: bold;
}
</style>
@endsection 