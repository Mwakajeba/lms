use App\Models\Company;

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Trial Balance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
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

        .report-date {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .report-details {
            font-size: 10px;
            color: #666;
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

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .debit {
            color: #dc3545;
        }

        .credit {
            color: #28a745;
        }

        .page-break {
            page-break-before: always;
        }

        .logo-wrapper {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px 0;
        }

        .logo-wrapper img {
            max-height: 80px;
            max-width: 200px;
            height: auto;
            width: auto;
            object-fit: contain;
        }

    </style>
</head>

<body>
    @php
    $companyModel = isset($company) ? $company : (function_exists('current_company') ? current_company() : null);
    $logoData = null;

    if ($companyModel && !empty($companyModel->logo)) {
    // Check if logo exists in storage and encode as base64 for DomPDF
    $logoPath = storage_path('app/public/' . $companyModel->logo);
    if (file_exists($logoPath)) {
    $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }
    }
    @endphp

    @if($logoData)
    <div class="logo-wrapper">
        <img src="{{ $logoData }}" alt="{{ $companyModel->name ?? 'Company' }} Logo" style="max-height: 70px; max-width: 200px;">
    </div>
    @endif
    <!-- Report Header -->
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'Company Name' }}</div>
        <div class="report-title">TRIAL BALANCE</div>
        @if($startDate == $endDate)
        <div class="report-date">As at {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }}</div>
        @else
        <div class="report-date">From {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</div>
        @endif
        @if(isset($branchId) && $branchId != 'all')
        <div class="report-details">Branch: {{ collect($branches)->where('id', $branchId)->first()['name'] ?? 'N/A' }}</div>
        @endif
        <div class="report-details">
            {{ ucfirst($reportingType) }} Basis |
            {{ ucfirst(str_replace('_',' ', ($trialBalanceData['layout'] ?? 'single_column'))) }} Layout |
            Generated on {{ now()->format('F d, Y \a\t g:i A') }}
        </div>
    </div>

    <div class="panel-body" style="background: #fff">
        <div id="printingArea">
            @php
            $comparatives = $trialBalanceData['comparative'] ?? [];
            $comparativesCount = is_array($comparatives) ? count($comparatives) : 0;
            $layoutKey = $trialBalanceData['layout'] ?? 'single';
            if ($layoutKey === 'multiple' || $layoutKey === 'multi_column') {
            $colspan = 9; // keep as-designed for multi-column layout
            } elseif ($layoutKey === 'double' || $layoutKey === 'double_column') {
            $colspan = 4 + ($comparativesCount * 2); // name, code, debit, credit + 2 per comparative
            } else { // single or default
            $colspan = 3 + $comparativesCount; // name, code, balance + 1 per comparative
            }
            @endphp
            <table id="myTable">
                @php $layoutKey = $trialBalanceData['layout'] ?? $layout ?? 'single_column'; @endphp
                @if($layoutKey === 'double_column')
                <tr style="font-weight:bold">
                    <th>ACCOUNT NAME</th>
                    <th>ACCOUNT CODE</th>
                    <th>DEBIT</th>
                    <th>CREDIT</th>
                    @if(isset($trialBalanceData['comparative']) && count($trialBalanceData['comparative']) > 0)
                    @foreach($trialBalanceData['comparative'] as $columnName => $comparativeData)
                    <th>DEBIT ({{ $columnName }})</th>
                    <th>CREDIT ({{ $columnName }})</th>
                    @endforeach
                    @endif
                </tr>
                @php
                $totalDebit = 0;
                $totalCredit = 0;
                $comparativeTotals = [];
                @endphp
                @foreach($trialBalanceData['data'] as $class => $accounts)
                @foreach($accounts as $account)
                @if(isset($account->sum) && floatval($account->sum) != 0)
                @php
                $sumVal = floatval($account->sum ?? 0);
                $isCredit = isset($account->nature) ? ($account->nature === 'credit') : ($sumVal < 0); $currentDebit=$isCredit ? 0 : abs($sumVal); $currentCredit=$isCredit ? abs($sumVal) : 0; $totalDebit +=$currentDebit; $totalCredit +=$currentCredit; @endphp <tr>
                    <td>{{ $account->account }}</td>
                    <td class="account-code">{{ $account->account_code }}</td>
                    <td class="text-end">{{ $currentDebit ? number_format($currentDebit, 2) : '-' }}</td>
                    <td class="text-end">{{ $currentCredit ? number_format($currentCredit, 2) : '-' }}</td>
                    @if(isset($trialBalanceData['comparative']) && count($trialBalanceData['comparative']) > 0)
                    @foreach($trialBalanceData['comparative'] as $columnName => $compData)
                    @php
                    $compAccount = collect($compData[$class] ?? [])->first(function($a) use ($account) {
                    return isset($a->account_code) && $a->account_code == $account->account_code;
                    });
                    $compDebit = 0; $compCredit = 0;
                    if ($compAccount) {
                    $compSum = floatval($compAccount->sum ?? 0);
                    $compIsCredit = isset($compAccount->nature) ? ($compAccount->nature === 'credit') : ($compSum < 0); $compDebit=$compIsCredit ? 0 : abs($compSum); $compCredit=$compIsCredit ? abs($compSum) : 0; } $comparativeTotals[$columnName]['debit']=($comparativeTotals[$columnName]['debit'] ?? 0) + $compDebit; $comparativeTotals[$columnName]['credit']=($comparativeTotals[$columnName]['credit'] ?? 0) + $compCredit; @endphp <td class="text-end">{{ $compDebit ? number_format($compDebit, 2) : '-' }}</td>
                        <td class="text-end">{{ $compCredit ? number_format($compCredit, 2) : '-' }}</td>
                        @endforeach
                        @endif
                        </tr>
                        @endif
                        @endforeach
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2" class="text-end">TOTAL</td>
                            <td class="text-end">{{ number_format($totalDebit, 2) }}</td>
                            <td class="text-end">{{ number_format($totalCredit, 2) }}</td>
                            @if(isset($trialBalanceData['comparative']) && count($trialBalanceData['comparative']) > 0)
                            @foreach($trialBalanceData['comparative'] as $columnName => $ignored)
                            <td class="text-end">{{ number_format($comparativeTotals[$columnName]['debit'] ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($comparativeTotals[$columnName]['credit'] ?? 0, 2) }}</td>
                            @endforeach
                            @endif
                        </tr>
                        <tr class="total-row">
                            <td colspan="{{ 2 + ($comparativesCount ? ($comparativesCount*2) : 0) }}" class="text-end">Net Balance (Debit - Credit)</td>
                            <td class="text-end" style="color: {{ ($totalDebit - $totalCredit) == 0 ? 'green' : 'red' }};">
                                {{ number_format($totalDebit - $totalCredit, 2) }}
                            </td>
                        </tr>
                        @elseif($layoutKey === 'single_column')
                        <tr style="font-weight:bold">
                            <th>ACCOUNT NAME</th>
                            <th>ACCOUNT CODE</th>
                            <th style="text-align: right">BALANCE</th>
                            @if(isset($trialBalanceData['comparative']) && count($trialBalanceData['comparative']) > 0)
                            @foreach($trialBalanceData['comparative'] as $columnName => $comparativeData)
                            <th style="text-align: right">BAL ({{ $columnName }})</th>
                            @endforeach
                            @endif
                        </tr>
                        @php
                        $totalDebit = 0;
                        $totalCredit = 0;
                        $comparativeTotals = [];
                        @endphp
                        @foreach($trialBalanceData['data'] as $class => $accounts)
                        @foreach($accounts as $account)
                        @if(isset($account->sum) && floatval($account->sum) != 0)
                        @php
                        $sumVal = floatval($account->sum ?? 0);
                        $balance = $sumVal; // sum is signed; negative means credit
                        if ($balance < 0) { $totalCredit +=abs($balance); } else { $totalDebit +=$balance; } @endphp <tr>
                            <td>{{ $account->account }}</td>
                            <td class="account-code">{{ $account->account_code }}</td>
                            <td class="text-end">{{ $balance < 0 ? '('.number_format(abs($balance), 2).')' : number_format($balance, 2) }}</td>
                            @if(isset($trialBalanceData['comparative']) && count($trialBalanceData['comparative']) > 0)
                            @foreach($trialBalanceData['comparative'] as $columnName => $compData)
                            @php
                            $compAccount = collect($compData[$class] ?? [])->first(function($a) use ($account) {
                            return isset($a->account_code) && $a->account_code == $account->account_code;
                            });
                            $compBal = 0;
                            if ($compAccount) {
                            $compBal = floatval($compAccount->sum ?? 0);
                            }
                            $comparativeTotals[$columnName] = ($comparativeTotals[$columnName] ?? 0) + $compBal;
                            @endphp
                            <td class="text-end">{{ $compBal < 0 ? '('.number_format(abs($compBal), 2).')' : number_format($compBal, 2) }}</td>
                            @endforeach
                            @endif
                            </tr>
                            @endif
                            @endforeach
                            @endforeach
                            <tr class="total-row">
                                <td colspan="2" class="text-end">Net Balance (Debit - Credit)</td>
                                <td class="text-end" style="color: {{ ($totalDebit - $totalCredit) == 0 ? 'green' : 'red' }};">
                                    {{ number_format($totalDebit - $totalCredit, 2) }}
                                </td>
                                @if(isset($trialBalanceData['comparative']) && count($trialBalanceData['comparative']) > 0)
                                @foreach($trialBalanceData['comparative'] as $columnName => $ignored)
                                @php $tv = $comparativeTotals[$columnName] ?? 0; @endphp
                                <td class="text-end" style="color: {{ $tv == 0 ? 'green' : 'red' }};">
                                    {{ $tv < 0 ? '('.number_format(abs($tv), 2).')' : number_format($tv, 2) }}
                                </td>
                                @endforeach
                                @endif
                            </tr>
                            @elseif($layoutKey === 'multi_column')
                            <tr style="font-weight:bold">
                                <th rowspan="2">ACCOUNT NAME</th>
                                <th rowspan="2">ACCOUNT CODE</th>
                                <th colspan="2">OPENING BALANCES</th>
                                <th colspan="2">CURRENT PERIOD</th>
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
                            // Prefer explicit opening/change/closing fields if present
                            $openingDr = isset($account->opening_debit) ? floatval($account->opening_debit) : 0;
                            $openingCr = isset($account->opening_credit) ? floatval($account->opening_credit) : 0;
                            $changeDr = isset($account->change_debit) ? floatval($account->change_debit) : 0;
                            $changeCr = isset($account->change_credit) ? floatval($account->change_credit) : 0;
                            $closingDr = isset($account->closing_debit) ? floatval($account->closing_debit) : 0;
                            $closingCr = isset($account->closing_credit) ? floatval($account->closing_credit) : 0;

                            // If fields are missing and only sum is present, derive from sum as current period
                            if (($openingDr + $openingCr + $changeDr + $changeCr + $closingDr + $closingCr) == 0 && isset($account->sum)) {
                            $sumVal = floatval($account->sum);
                            $changeDr = $sumVal > 0 ? $sumVal : 0;
                            $changeCr = $sumVal < 0 ? abs($sumVal) : 0; $closingDr=$changeDr; $closingCr=$changeCr; } $openingDiff=$openingDr - $openingCr; $changeDiff=$changeDr - $changeCr; $closingDiff=$closingDr - $closingCr; $difference=$closingDiff; $totalOpeningDr +=$openingDiff> 0 ? $openingDiff : 0;
                                $totalOpeningCr += $openingDiff < 0 ? abs($openingDiff) : 0; $totalChangeDr +=$changeDiff> 0 ? $changeDiff : 0;
                                    $totalChangeCr += $changeDiff < 0 ? abs($changeDiff) : 0; $totalClosingDr +=$closingDiff> 0 ? $closingDiff : 0;
                                        $totalClosingCr += $closingDiff < 0 ? abs($closingDiff) : 0; $totalDiff +=$difference; @endphp @if($openingDiff !=0 || $changeDiff !=0 || $closingDiff !=0) <tr>
                                            <td>{{ $account->account ?? $account->account_name ?? '' }}</td>
                                            <td class="account-code">{{ $account->account_code ?? '' }}</td>
                                            <td class="text-end">{{ $openingDiff > 0 ? number_format($openingDiff, 2) : '-' }}</td>
                                            <td class="text-end">{{ $openingDiff < 0 ? number_format(abs($openingDiff), 2) : '-' }}</td>
                                            <td class="text-end">{{ $changeDiff > 0 ? number_format($changeDiff, 2) : '-' }}</td>
                                            <td class="text-end">{{ $changeDiff < 0 ? number_format(abs($changeDiff), 2) : '-' }}</td>
                                            <td class="text-end">{{ $closingDiff > 0 ? number_format($closingDiff, 2) : '-' }}</td>
                                            <td class="text-end">{{ $closingDiff < 0 ? number_format(abs($closingDiff), 2) : '-' }}</td>
                                            <td class="text-end">{{ $difference != 0 ? number_format(abs($difference), 2) : '-' }}</td>
                                            </tr>
                                            @endif
                                            @endforeach
                                            @endforeach

                                            <tr class="total-row">
                                                <td colspan="2" class="text-end">TOTAL</td>
                                                <td class="text-end">{{ number_format($totalOpeningDr, 2) }}</td>
                                                <td class="text-end">{{ number_format($totalOpeningCr, 2) }}</td>
                                                <td class="text-end">{{ number_format($totalChangeDr, 2) }}</td>
                                                <td class="text-end">{{ number_format($totalChangeCr, 2) }}</td>
                                                <td class="text-end">{{ number_format($totalClosingDr, 2) }}</td>
                                                <td class="text-end">{{ number_format($totalClosingCr, 2) }}</td>
                                                <td class="text-end">{{ $totalDiff != 0 ? number_format(abs($totalDiff), 2) : '-' }}</td>
                                            </tr>
                                            @endif
            </table>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer generated document. No signature is required.</p>
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
    </div>
</body>

</html>
