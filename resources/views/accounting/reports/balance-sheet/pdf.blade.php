<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Sheet - {{ $company->name ?? 'SmartFinance' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-info {
            font-size: 11px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .section-header {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            padding: 10px 12px;
        }
        .group-header {
            background-color: #e9ecef;
            font-weight: bold;
            padding: 8px 12px;
        }
        .total-row {
            font-weight: bold;
            border-top: 2px solid #333;
            background-color: #f8f9fa;
        }
        .check-row {
            font-weight: bold;
            border-top: 2px solid #333;
            background-color: #d4edda;
        }
        .number {
            text-align: right;
            font-family: monospace;
        }
        .positive {
            color: #28a745;
        }
        .negative {
            color: #dc3545;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">BALANCE SHEET</div>
        <div class="report-info">
            As of: {{ \Carbon\Carbon::parse($asOf)->format('M d, Y') }}
            @if($comparativeAsOf)
                | Comparative: {{ \Carbon\Carbon::parse($comparativeAsOf)->format('M d, Y') }}
            @endif
        </div>
    </div>

    @if($viewType === 'summary')
        {{-- Summary View (Class and Group totals) --}}
        @php $firstComp = $data['comparativesData'][0] ?? null; $compDate = $firstComp['date'] ?? null; @endphp
        <table>
            <thead>
                <tr>
                    <th>Line</th>
                    <th class="text-end">Current As Of</th>
                    @if($compDate)
                        <th class="text-end">Comparative As Of</th>
                        <th class="text-end">Variance</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                {{-- ASSETS --}}
                <tr>
                    <td><strong>ASSETS</strong></td>
                    <td class="text-end number"><strong>{{ number_format($data['assetsTotal'], 2) }}</strong></td>
                    @if($compDate)
                        <td class="text-end number"><strong>{{ number_format($firstComp['assetsTotal'] ?? 0, 2) }}</strong></td>
                        <td class="text-end number {{ (($data['assetsTotal'] - ($firstComp['assetsTotal'] ?? 0)) >= 0) ? 'positive' : 'negative' }}">
                            <strong>{{ number_format($data['assetsTotal'] - ($firstComp['assetsTotal'] ?? 0), 2) }}</strong>
                        </td>
                    @endif
                </tr>
                @foreach(($data['groupTotals']['Assets'] ?? []) as $groupName => $groupTotal)
                    <tr>
                        <td style="padding-left: 16px;">{{ $groupName }}</td>
                        <td class="text-end number">{{ number_format($groupTotal, 2) }}</td>
                        @if($compDate)
                            @php $gcmp = $data['comparativeGroupTotals'][$compDate]['Assets'][$groupName] ?? 0; @endphp
                            <td class="text-end number">{{ number_format($gcmp, 2) }}</td>
                            <td class="text-end number {{ (($groupTotal - $gcmp) >= 0) ? 'positive' : 'negative' }}">{{ number_format($groupTotal - $gcmp, 2) }}</td>
                        @endif
                    </tr>
                @endforeach

                {{-- LIABILITIES --}}
                <tr>
                    <td><strong>LIABILITIES</strong></td>
                    <td class="text-end number"><strong>{{ number_format($data['liabilitiesTotal'], 2) }}</strong></td>
                    @if($compDate)
                        <td class="text-end number"><strong>{{ number_format($firstComp['liabilitiesTotal'] ?? 0, 2) }}</strong></td>
                        <td class="text-end number {{ (($data['liabilitiesTotal'] - ($firstComp['liabilitiesTotal'] ?? 0)) >= 0) ? 'positive' : 'negative' }}">
                            <strong>{{ number_format($data['liabilitiesTotal'] - ($firstComp['liabilitiesTotal'] ?? 0), 2) }}</strong>
                        </td>
                    @endif
                </tr>
                @foreach(($data['groupTotals']['Liabilities'] ?? []) as $groupName => $groupTotal)
                    <tr>
                        <td style="padding-left: 16px;">{{ $groupName }}</td>
                        <td class="text-end number">{{ number_format($groupTotal, 2) }}</td>
                        @if($compDate)
                            @php $gcmp = $data['comparativeGroupTotals'][$compDate]['Liabilities'][$groupName] ?? 0; @endphp
                            <td class="text-end number">{{ number_format($gcmp, 2) }}</td>
                            <td class="text-end number {{ (($groupTotal - $gcmp) >= 0) ? 'positive' : 'negative' }}">{{ number_format($groupTotal - $gcmp, 2) }}</td>
                        @endif
                    </tr>
                @endforeach

                {{-- EQUITY --}}
                <tr>
                    <td><strong>EQUITY</strong></td>
                    <td class="text-end number"><strong>{{ number_format($data['equityTotal'], 2) }}</strong></td>
                    @if($compDate)
                        <td class="text-end number"><strong>{{ number_format($firstComp['equityTotal'] ?? 0, 2) }}</strong></td>
                        <td class="text-end number {{ (($data['equityTotal'] - ($firstComp['equityTotal'] ?? 0)) >= 0) ? 'positive' : 'negative' }}">
                            <strong>{{ number_format($data['equityTotal'] - ($firstComp['equityTotal'] ?? 0), 2) }}</strong>
                        </td>
                    @endif
                </tr>
                @foreach(($data['groupTotals']['Equity'] ?? []) as $groupName => $groupTotal)
                    <tr>
                        <td style="padding-left: 16px;">{{ $groupName }}</td>
                        <td class="text-end number">{{ number_format($groupTotal, 2) }}</td>
                        @if($compDate)
                            @php $gcmp = $data['comparativeGroupTotals'][$compDate]['Equity'][$groupName] ?? 0; @endphp
                            <td class="text-end number">{{ number_format($gcmp, 2) }}</td>
                            <td class="text-end number {{ (($groupTotal - $gcmp) >= 0) ? 'positive' : 'negative' }}">{{ number_format($groupTotal - $gcmp, 2) }}</td>
                        @endif
                    </tr>
                @endforeach

                {{-- PROFIT / LOSS --}}
                <tr>
                    <td><strong>Profit / Loss</strong></td>
                    <td class="text-end number"><strong>{{ number_format($data['profitLoss'], 2) }}</strong></td>
                    @if($compDate)
                        <td class="text-end number"><strong>{{ number_format($firstComp['profitLoss'] ?? 0, 2) }}</strong></td>
                        @php $pv = $data['profitLoss']; $pc = ($firstComp['profitLoss'] ?? 0); @endphp
                        <td class="text-end number {{ (($pv - $pc) >= 0) ? 'positive' : 'negative' }}"><strong>{{ number_format($pv - $pc, 2) }}</strong></td>
                    @endif
                </tr>

                <tr class="total-row">
                    <td><strong>TOTAL LIABILITIES + EQUITY</strong></td>
                    <td class="text-end number"><strong>{{ number_format($data['liabilitiesTotal'] + $data['equityTotal'], 2) }}</strong></td>
                    @if($compDate)
                        <td class="text-end number"><strong>{{ number_format(($firstComp['liabilitiesTotal'] ?? 0) + ($firstComp['equityTotal'] ?? 0), 2) }}</strong></td>
                        @php $lv = ($data['liabilitiesTotal'] + $data['equityTotal']); $cv = (($firstComp['liabilitiesTotal'] ?? 0) + ($firstComp['equityTotal'] ?? 0)); @endphp
                        <td class="text-end number {{ (($lv - $cv) >= 0) ? 'positive' : 'negative' }}"><strong>{{ number_format($lv - $cv, 2) }}</strong></td>
                    @endif
                </tr>
            </tbody>
        </table>

    @else
        {{-- Detailed View --}}
        {{-- Assets Section --}}
        <table>
            <thead>
                <tr class="section-header">
                    <th colspan="{{ $comparativeAsOf ? '4' : '2' }}">ASSETS</th>
                </tr>
                <tr>
                    <th>Account</th>
                    <th class="text-end">Current As Of</th>
                    @if($comparativeAsOf)
                        <th class="text-end">Comparative As Of</th>
                        <th class="text-end">Variance</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @if(isset($data['detailed']['Assets']))
                    @foreach($data['detailed']['Assets']['groups'] as $groupName => $group)
                        <tr class="group-header">
                            <td colspan="{{ $comparativeAsOf ? '4' : '2' }}">{{ $groupName }}</td>
                        </tr>
                        @foreach($group['accounts'] as $account)
                            <tr>
                                <td style="padding-left: 20px;">{{ $account['account_name'] ?? ($account['name'] ?? '-') }}</td>
                                <td class="text-end number">{{ number_format($account['balance'], 2) }}</td>
                                @if($comparativeAsOf)
                                    <td class="text-end number">-</td>
                                    <td class="text-end number">-</td>
                                @endif
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>Total {{ $groupName }}</strong></td>
                            <td class="text-end number"><strong>{{ number_format($group['total'], 2) }}</strong></td>
                            @if($comparativeAsOf)
                                <td class="text-end number">-</td>
                                <td class="text-end number">-</td>
                            @endif
                        </tr>
                    @endforeach
                @endif
                <tr class="total-row">
                    <td><strong>TOTAL ASSETS</strong></td>
                    <td class="text-end number"><strong>{{ number_format($data['assetsTotal'], 2) }}</strong></td>
                    @if($comparativeAsOf && !empty($data['comparativesData']))
                        @php $comp = $data['comparativesData'][0] ?? null; @endphp
                        @if($comp)
                            <td class="text-end number"><strong>{{ number_format($comp['assetsTotal'], 2) }}</strong></td>
                            <td class="text-end number {{ ($data['assetsTotal'] - $comp['assetsTotal']) >= 0 ? 'positive' : 'negative' }}">
                                <strong>{{ number_format($data['assetsTotal'] - $comp['assetsTotal'], 2) }}</strong>
                            </td>
                        @endif
                    @endif
                </tr>
            </tbody>
        </table>

        <div class="page-break"></div>

        {{-- Liabilities Section --}}
        <table>
            <thead>
                <tr class="section-header">
                    <th colspan="{{ $comparativeAsOf ? '4' : '2' }}">LIABILITIES</th>
                </tr>
                <tr>
                    <th>Account</th>
                    <th class="text-end">Current As Of</th>
                    @if($comparativeAsOf)
                        <th class="text-end">Comparative As Of</th>
                        <th class="text-end">Variance</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @if(isset($data['detailed']['Liabilities']))
                    @foreach($data['detailed']['Liabilities']['groups'] as $groupName => $group)
                        <tr class="group-header">
                            <td colspan="{{ $comparativeAsOf ? '4' : '2' }}">{{ $groupName }}</td>
                        </tr>
                        @foreach($group['accounts'] as $account)
                            <tr>
                                <td style="padding-left: 20px;">{{ $account['account_name'] ?? ($account['name'] ?? '-') }}</td>
                                <td class="text-end number">{{ number_format($account['balance'], 2) }}</td>
                                @if($comparativeAsOf)
                                    <td class="text-end number">-</td>
                                    <td class="text-end number">-</td>
                                @endif
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>Total {{ $groupName }}</strong></td>
                            <td class="text-end number"><strong>{{ number_format($group['total'], 2) }}</strong></td>
                            @if($comparativeAsOf)
                                <td class="text-end number">-</td>
                                <td class="text-end number">-</td>
                            @endif
                        </tr>
                    @endforeach
                @endif
                <tr class="total-row">
                    <td><strong>TOTAL LIABILITIES</strong></td>
                    <td class="text-end number"><strong>{{ number_format($data['liabilitiesTotal'], 2) }}</strong></td>
                    @if($comparativeAsOf && !empty($data['comparativesData']))
                        @php $comp = $data['comparativesData'][0] ?? null; @endphp
                        @if($comp)
                            <td class="text-end number"><strong>{{ number_format($comp['liabilitiesTotal'], 2) }}</strong></td>
                            <td class="text-end number {{ ($data['liabilitiesTotal'] - $comp['liabilitiesTotal']) >= 0 ? 'positive' : 'negative' }}">
                                <strong>{{ number_format($data['liabilitiesTotal'] - $comp['liabilitiesTotal'], 2) }}</strong>
                            </td>
                        @endif
                    @endif
                </tr>
            </tbody>
        </table>

        {{-- Equity Section --}}
        <table>
            <thead>
                <tr class="section-header">
                    <th colspan="{{ $comparativeAsOf ? '4' : '2' }}">SHAREHOLDER'S EQUITY</th>
                </tr>
                <tr>
                    <th>Account</th>
                    <th class="text-end">Current As Of</th>
                    @if($comparativeAsOf)
                        <th class="text-end">Comparative As Of</th>
                        <th class="text-end">Variance</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @if(isset($data['detailed']['Equity']))
                    @foreach($data['detailed']['Equity']['groups'] as $groupName => $group)
                        <tr class="group-header">
                            <td colspan="{{ $comparativeAsOf ? '4' : '2' }}">{{ $groupName }}</td>
                        </tr>
                        @foreach($group['accounts'] as $account)
                            <tr>
                                <td style="padding-left: 20px;">{{ $account['account_name'] ?? ($account['name'] ?? '-') }}</td>
                                <td class="text-end number">{{ number_format($account['balance'], 2) }}</td>
                                @if($comparativeAsOf)
                                    <td class="text-end number">-</td>
                                    <td class="text-end number">-</td>
                                @endif
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>Total {{ $groupName }}</strong></td>
                            <td class="text-end number"><strong>{{ number_format($group['total'], 2) }}</strong></td>
                            @if($comparativeAsOf)
                                <td class="text-end number">-</td>
                                <td class="text-end number">-</td>
                            @endif
                        </tr>
                    @endforeach
                @endif
                <tr>
                    <td><strong>Profit / Loss</strong></td>
                    <td class="text-end number"><strong>{{ number_format($data['profitLoss'], 2) }}</strong></td>
                    @if($comparativeAsOf && !empty($data['comparativesData']))
                        @php $comp = $data['comparativesData'][0] ?? null; @endphp
                        @if($comp)
                            <td class="text-end number"><strong>{{ number_format($comp['profitLoss'], 2) }}</strong></td>
                            <td class="text-end number {{ ($data['profitLoss'] - $comp['profitLoss']) >= 0 ? 'positive' : 'negative' }}">
                                <strong>{{ number_format($data['profitLoss'] - $comp['profitLoss'], 2) }}</strong>
                            </td>
                        @endif
                    @endif
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL EQUITY</strong></td>
                    <td class="text-end number"><strong>{{ number_format($data['equityTotal'], 2) }}</strong></td>
                    @if($comparativeAsOf && !empty($data['comparativesData']))
                        @php $comp = $data['comparativesData'][0] ?? null; @endphp
                        @if($comp)
                            <td class="text-end number"><strong>{{ number_format($comp['equityTotal'], 2) }}</strong></td>
                            <td class="text-end number {{ ($data['equityTotal'] - $comp['equityTotal']) >= 0 ? 'positive' : 'negative' }}">
                                <strong>{{ number_format($data['equityTotal'] - $comp['equityTotal'], 2) }}</strong>
                            </td>
                        @endif
                    @endif
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL LIABILITIES + EQUITY</strong></td>
                    <td class="text-end number"><strong>{{ number_format($data['liabilitiesTotal'] + $data['equityTotal'], 2) }}</strong></td>
                    @if($comparativeAsOf && !empty($data['comparativesData']))
                        @php $comp = $data['comparativesData'][0] ?? null; @endphp
                        @if($comp)
                            <td class="text-end number"><strong>{{ number_format($comp['liabilitiesTotal'] + $comp['equityTotal'], 2) }}</strong></td>
                            <td class="text-end number {{ (($data['liabilitiesTotal'] + $data['equityTotal']) - ($comp['liabilitiesTotal'] + $comp['equityTotal'])) >= 0 ? 'positive' : 'negative' }}">
                                <strong>{{ number_format(($data['liabilitiesTotal'] + $data['equityTotal']) - ($comp['liabilitiesTotal'] + $comp['equityTotal']), 2) }}</strong>
                            </td>
                        @endif
                    @endif
                </tr>
                
            </tbody>
        </table>
    @endif
</body>
</html>
