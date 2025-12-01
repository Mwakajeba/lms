<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Book Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-period {
            font-size: 10px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 9px;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .section-header {
            background-color: #e9ecef;
            font-weight: bold;
            text-align: center;
        }
        .opening-balance {
            background-color: #fff3cd;
            font-weight: bold;
        }
        .total-row {
            background-color: #fff3cd;
            font-weight: bold;
        }
        .closing-balance {
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
        .text-left {
            text-align: left;
        }
        .logo-wrapper { text-align: center; margin-bottom: 10px; }
        .logo-wrapper img { max-height: 70px; }
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
        <div class="report-title">CASH BOOK</div>
        <div class="report-period">
            @if($cashBookData['start_date'] === $cashBookData['end_date'])
                AS AT {{ \Carbon\Carbon::parse($cashBookData['end_date'])->format('d-m-Y') }}
            @else
                FROM {{ \Carbon\Carbon::parse($cashBookData['start_date'])->format('d-m-Y') }} TO {{ \Carbon\Carbon::parse($cashBookData['end_date'])->format('d-m-Y') }}
            @endif
            @if(isset($branchName))
                <br>BRANCH: {{ $branchName }}
            @endif
        </div>
    </div>

    <table>
        <tr class="section-header">
            <th>DATE</th>
            <th>DESCRIPTION</th>
            <th>CUSTOMER</th>
            <th>BANK ACCOUNT</th>
            <th>TRANSACTION NO</th>
            <th>REFERENCE NO.</th>
            <th>DEBIT</th>
            <th>CREDIT</th>
            <th>BALANCE</th>
        </tr>
        
        <tr class="opening-balance">
            <td colspan="7" class="text-right">Opening Balance</td>
            <td></td>
            <td class="text-right">{{ number_format($cashBookData['opening_balance'], 2) }}</td>
        </tr>
        
        @php
            $running_balance = $cashBookData['opening_balance'];
            $total_receipts = 0;
            $total_payments = 0;
        @endphp
        
        @foreach($cashBookData['transactions'] as $transaction)
            @php
                $debit = $transaction['debit'];
                $credit = $transaction['credit'];

                $total_receipts += $debit;
                $total_payments += $credit;

                $running_balance += $debit - $credit;
            @endphp
            
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}</td>
                <td class="text-left">{{ $transaction['description'] }}</td>
                <td class="text-left">{{ $transaction['customer_name'] }}</td>
                <td class="text-left">{{ $transaction['bank_account'] }}</td>
                <td>{{ $transaction['transaction_no'] }}</td>
                <td>{{ $transaction['reference_no'] }}</td>
                <td class="text-right">{{ $debit > 0 ? number_format($debit, 2) : '' }}</td>
                <td class="text-right">{{ $credit > 0 ? number_format($credit, 2) : '' }}</td>
                <td class="text-right">{{ number_format($running_balance, 2) }}</td>
            </tr>
        @endforeach
        
        <tr class="total-row">
            <td colspan="6" class="text-right">Total Debit</td>
            <td class="text-right">{{ number_format($total_receipts, 2) }}</td>
            <td></td>
            <td></td>
        </tr>
        
        <tr class="total-row">
            <td colspan="6" class="text-right">Total Credit</td>
            <td></td>
            <td class="text-right">{{ number_format($total_payments, 2) }}</td>
            <td></td>
        </tr>
        
        <tr class="total-row">
            <td colspan="6" class="text-right">Final Balance</td>
            <td></td>
            <td></td>
            <td class="text-right">{{ number_format($running_balance, 2) }}</td>
        </tr>
        
        <tr class="closing-balance">
            <td colspan="8" class="text-right">Closing Balance</td>
            <td class="text-right">{{ number_format($running_balance, 2) }}</td>
        </tr>
    </table>

    <div style="margin-top: 30px; font-size: 8px; color: #666;">
        <p><strong>Report Generated:</strong> {{ now()->format('d-m-Y H:i:s') }}</p>
        <p><strong>Generated By:</strong> {{ auth()->user()->name ?? 'System' }}</p>
    </div>
</body>
</html> 