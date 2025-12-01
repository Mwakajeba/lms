<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>General Ledger Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
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

        .report-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
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

        .opening-balance {
            background-color: #e3f2fd;
        }

        .account-total {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
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
        <div class="report-title">GENERAL LEDGER REPORT</div>
        <div class="report-info">
            @if($startDate === $endDate)
            As at: {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} |
            @else
            Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} |
            @endif
            Basis: {{ ucfirst($reportType) }}
            @if(isset($groupBy))
            | Group By: {{ ucfirst($groupBy) }}
            @endif
            @if(isset($branchName))
            | Branch: {{ $branchName }}
            @endif
        </div>
    </div>

    @if(isset($generalLedgerData) && count($generalLedgerData['transactions']) > 0)
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Account Code</th>
                <th>Account Name</th>
                <th>Customer</th>
                <th>Transaction ID</th>
                <th>Description</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
                <th class="text-end">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
            $currentAccount = null;
            $accountTotalDebit = 0;
            $accountTotalCredit = 0;
            @endphp

            @foreach($generalLedgerData['transactions'] as $transaction)
            @if($currentAccount !== $transaction->chart_account_id)
            @if($currentAccount !== null)
            <!-- Account Total Row -->
            <tr class="account-total">
                <td colspan="6"><strong>Total for {{ $transaction->account_code }}</strong></td>
                <td class="text-end"><strong>{{ number_format($accountTotalDebit, 2) }}</strong></td>
                <td class="text-end"><strong>{{ number_format($accountTotalCredit, 2) }}</strong></td>
                <td class="text-end"><strong>{{ number_format($accountTotalDebit - $accountTotalCredit, 2) }}</strong></td>
            </tr>
            @endif

            @if($generalLedgerData['filters']['show_opening_balance'] && isset($generalLedgerData['opening_balances'][$transaction->chart_account_id]))
            @php
            $openingBalance = $generalLedgerData['opening_balances'][$transaction->chart_account_id];
            $openingAmount = $openingBalance->total_debit - $openingBalance->total_credit;
            @endphp
            <tr class="opening-balance">
                <td>{{ \Carbon\Carbon::parse($startDate)->subDay()->format('M d, Y') }}</td>
                <td>{{ $transaction->account_code }}</td>
                <td>{{ $transaction->account_name }}</td>
                <td>N/A</td>
                <td>OPENING BALANCE</td>
                <td>Balance brought forward</td>
                <td class="text-end">{{ $openingAmount >= 0 ? number_format($openingAmount, 2) : '' }}</td>
                <td class="text-end">{{ $openingAmount < 0 ? number_format(abs($openingAmount), 2) : '' }}</td>
                <td class="text-end">{{ number_format($openingAmount, 2) }}</td>
            </tr>
            @endif

            @php
            $currentAccount = $transaction->chart_account_id;
            $accountTotalDebit = 0;
            $accountTotalCredit = 0;
            @endphp
            @endif

            <tr>
                <td>{{ \Carbon\Carbon::parse($transaction->date)->format('M d, Y') }}</td>
                <td>{{ $transaction->account_code }}</td>
                <td>{{ $transaction->account_name }}</td>
                <td>{{ $transaction->customer_name ?? 'N/A' }}</td>
                <td>{{ $transaction->transaction_id }}</td>
                <td>{{ $transaction->description }}</td>
                <td class="text-end">{{ $transaction->nature === 'debit' ? number_format($transaction->amount, 2) : '' }}</td>
                <td class="text-end">{{ $transaction->nature === 'credit' ? number_format($transaction->amount, 2) : '' }}</td>
                <td class="text-end">{{ number_format($transaction->running_balance, 2) }}</td>
            </tr>

            @php
            if ($transaction->nature === 'debit') {
            $accountTotalDebit += $transaction->amount;
            } else {
            $accountTotalCredit += $transaction->amount;
            }
            @endphp
            @endforeach

            @if($currentAccount !== null)
            <!-- Final Account Total Row -->
            @php
            $lastTransaction = end($generalLedgerData['transactions']);
            @endphp
            <tr class="account-total">
                <td colspan="6"><strong>Total for {{ $lastTransaction->account_code }} - {{ $lastTransaction->account_name }}</strong></td>
                <td class="text-end"><strong>{{ number_format($accountTotalDebit, 2) }}</strong></td>
                <td class="text-end"><strong>{{ number_format($accountTotalCredit, 2) }}</strong></td>
                <td class="text-end"><strong>{{ number_format($accountTotalDebit - $accountTotalCredit, 2) }}</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="summary">
        <h4>Report Summary</h4>
        <p><strong>Total Debit:</strong> {{ number_format($generalLedgerData['summary']['total_debit'], 2) }}</p>
        <p><strong>Total Credit:</strong> {{ number_format($generalLedgerData['summary']['total_credit'], 2) }}</p>
        <p><strong>Net Movement:</strong> {{ number_format($generalLedgerData['summary']['net_movement'], 2) }}</p>
        <p><strong>Transaction Count:</strong> {{ $generalLedgerData['summary']['transaction_count'] }}</p>
        <p><strong>Account Count:</strong> {{ $generalLedgerData['summary']['account_count'] }}</p>
    </div>
    @else
    <p>No general ledger data found for the selected criteria.</p>
    @endif
</body>

</html>