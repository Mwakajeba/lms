<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Income Statement Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-period {
            font-size: 12px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .section-header {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
        }

        .group-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .total-row {
            background-color: #fff3cd;
            font-weight: bold;
        }

        .profit-loss-row {
            background-color: #343a40;
            color: white;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .account-link {
            color: #007bff;
            text-decoration: none;
        }

        .logo-wrapper {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-wrapper img {
            max-height: 70px;
        }
    </style>
</head>

<body>
    @php
    $companyModel = isset($company) ? $company : (function_exists('current_company') ? current_company() : null);
    $logoPath = ($companyModel && !empty($companyModel->logo)) ? public_path('storage/' . $companyModel->logo) : null;
    @endphp
    @if($logoPath && file_exists($logoPath))
    <div class="logo-wrapper">
        <img src="{{ $logoPath }}" alt="Company Logo">
    </div>
    @endif
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">INCOME STATEMENT</div>
        <div class="report-period">
            @if($incomeStatementData['start_date'] === $incomeStatementData['end_date'])
            AS AT {{ \Carbon\Carbon::parse($incomeStatementData['end_date'])->format('d-m-Y') }}
            @else
            FROM {{ \Carbon\Carbon::parse($incomeStatementData['start_date'])->format('d-m-Y') }} TO {{ \Carbon\Carbon::parse($incomeStatementData['end_date'])->format('d-m-Y') }}
            @endif
        </div>
        <div class="report-period">Basis: {{ ucfirst($incomeStatementData['reporting_type']) }}</div>
    </div>

    @php
    $comparatives = $incomeStatementData['comparative'] ?? [];
    $comparativesCount = is_array($comparatives) ? count($comparatives) : 0;
    @endphp

    <table>
        <tr>
            <th>Financial Statement Line Item</th>
            <th>Ledger Account</th>
            <th class="text-right">Current Period</th>
            @if($comparativesCount)
            @foreach($comparatives as $label => $comp)
            <th class="text-right">{{ $label }}</th>
            @endforeach
            @endif
        </tr>

        <tr class="section-header">
            <td colspan="{{ 3 + $comparativesCount }}">INCOME</td>
        </tr>

        @php
        $sumRevenue = 0;
        $compRevenueTotals = [];
        @endphp
        @foreach($incomeStatementData['data']['revenues'] as $groupName => $accounts)
        @php $groupTotal = collect($accounts)->sum('sum'); @endphp
        @if($groupTotal != 0)
        <tr class="group-header">
            <td colspan="{{ 3 + $comparativesCount }}">{{ $groupName }}</td>
        </tr>
        @foreach($accounts as $chartAccountRevenue)
        @php
        // Build comparative row values for this account by matching on account_id within the same group
        $rowComps = [];
        foreach ($comparatives as $label => $cdata) {
        $prev = collect($cdata['revenues'][$groupName] ?? [])->firstWhere('account_id', $chartAccountRevenue['account_id'])['sum'] ?? 0;
        $rowComps[$label] = $prev;
        $compRevenueTotals[$label] = ($compRevenueTotals[$label] ?? 0) + $prev;
        }
        @endphp
        @if($chartAccountRevenue['sum'] != 0 || collect($rowComps)->sum() != 0)
        @php $sumRevenue += $chartAccountRevenue['sum']; @endphp
        <tr>
            <td>{{ $groupName }}</td>
            <td>{{ $chartAccountRevenue['account'] }}</td>
            <td class="text-right">{{ number_format($chartAccountRevenue['sum'], 2) }}</td>
            @if($comparativesCount)
            @foreach($comparatives as $label => $ignored)
            <td class="text-right">{{ number_format($rowComps[$label] ?? 0, 2) }}</td>
            @endforeach
            @endif
        </tr>
        @endif
        @endforeach
        @endif
        @endforeach

        <tr class="total-row">
            <td><strong>TOTAL INCOME</strong></td>
            <td></td>
            <td class="text-right"><strong>{{ number_format($sumRevenue, 2) }}</strong></td>
            @if($comparativesCount)
            @foreach($comparatives as $label => $ignored)
            <td class="text-right"><strong>{{ number_format($compRevenueTotals[$label] ?? 0, 2) }}</strong></td>
            @endforeach
            @endif
        </tr>

        <tr class="section-header">
            <td colspan="{{ 3 + $comparativesCount }}">LESS EXPENSES</td>
        </tr>

        @php
        $sumExpense = 0;
        $compExpenseTotals = [];
        @endphp
        @foreach($incomeStatementData['data']['expenses'] as $groupName => $accounts)
        @php $groupTotal = collect($accounts)->sum('sum'); @endphp
        @if($groupTotal != 0)
        <tr class="group-header">
            <td colspan="{{ 3 + $comparativesCount }}">{{ $groupName }}</td>
        </tr>
        @foreach($accounts as $chartAccountExpenses)
        @php
        $rowComps = [];
        foreach ($comparatives as $label => $cdata) {
        $prev = collect($cdata['expenses'][$groupName] ?? [])->firstWhere('account_id', $chartAccountExpenses['account_id'])['sum'] ?? 0;
        $rowComps[$label] = $prev;
        $compExpenseTotals[$label] = ($compExpenseTotals[$label] ?? 0) + $prev;
        }
        @endphp
        @if($chartAccountExpenses['sum'] != 0 || collect($rowComps)->sum() != 0)
        @php $sumExpense += $chartAccountExpenses['sum']; @endphp
        <tr>
            <td>{{ $groupName }}</td>
            <td>{{ $chartAccountExpenses['account'] }}</td>
            <td class="text-right">{{ number_format(abs($chartAccountExpenses['sum']), 2) }}</td>
            @if($comparativesCount)
            @foreach($comparatives as $label => $ignored)
            <td class="text-right">{{ number_format($rowComps[$label] ?? 0, 2) }}</td>
            @endforeach
            @endif
        </tr>
        @endif
        @endforeach
        @endif
        @endforeach

        <tr class="total-row">
            <td><strong>TOTAL EXPENSES</strong></td>
            <td></td>
            <td class="text-right"><strong>{{ number_format(abs($sumExpense), 2) }}</strong></td>
            @if($comparativesCount)
            @foreach($comparatives as $label => $ignored)
            <td class="text-right"><strong>{{ number_format(abs($compExpenseTotals[$label] ?? 0), 2) }}</strong></td>
            @endforeach
            @endif
        </tr>

        <tr class="profit-loss-row">
            <td><strong>PROFIT / LOSS</strong></td>
            <td></td>
            @php $netCurrent = $sumRevenue - abs($sumExpense); @endphp
            <td class="text-right"><strong>{{ number_format($netCurrent, 2) }}</strong></td>
            @if($comparativesCount)
            @foreach($comparatives as $label => $ignored)
            @php $netComp = ($compRevenueTotals[$label] ?? 0) - abs($compExpenseTotals[$label] ?? 0); @endphp
            <td class="text-right"><strong>{{ number_format($netComp, 2) }}</strong></td>
            @endforeach
            @endif
        </tr>
    </table>

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <p><strong>Report Generated:</strong> {{ now()->format('d-m-Y H:i:s') }}</p>
        <p><strong>Generated By:</strong> {{ auth()->user()->name ?? 'System' }}</p>
    </div>
</body>

</html>