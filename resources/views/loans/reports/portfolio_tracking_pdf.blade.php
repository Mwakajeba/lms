<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Portfolio Tracking Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-name { font-size: 18px; font-weight: bold; }
        .report-title { font-size: 14px; margin: 10px 0; }
        .report-info { font-size: 10px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .summary-row { background-color: #fff3cd; font-weight: bold; }
        .page-break { page-break-before: always; }
        .no-data { text-align: center; padding: 20px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">Loan Portfolio Tracking Report</div>
        <div class="report-info">
            Period: {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}
            @if($groupBy !== 'day')
            | Grouped by: {{ ucfirst($groupBy) }}
            @endif
        </div>
    </div>

    @if(count($rows) > 0)
    <table>
        <thead>
            <tr>
                <th>Group</th>
                @if($groupBy !== 'day')
                <th>Date Range</th>
                @endif
                <th>Customer Name</th>
                <th>Loan Officer</th>
                <th>Loan Product</th>
                <th>Loan Account No.</th>
                <th>Disbursement Date</th>
                <th>Maturity Date</th>
                <th class="text-right">Amount Disbursed</th>
                <th class="text-right">Interest</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Principal Paid</th>
                <th class="text-right">Interest Paid</th>
                <th class="text-right">Penalties Paid</th>
                <th class="text-right">Outstanding Principal</th>
                <th class="text-right">Outstanding Interest</th>
                <th class="text-right">Amount Overdue</th>
                <th class="text-right">Days in Arrears</th>
                <th>Loan Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
            <tr class="{{ isset($r['is_summary']) && $r['is_summary'] ? 'summary-row' : '' }}">
                <td>{{ $r['group'] }}</td>
                @if($groupBy !== 'day')
                <td>{{ $r['date_range'] ?? '' }}</td>
                @endif
                <td>{{ $r['customer_name'] }}</td>
                <td>{{ $r['loan_officer'] }}</td>
                <td>{{ $r['loan_product'] }}</td>
                <td>{{ $r['loan_account_no'] }}</td>
                <td>{{ $r['disbursement_date'] }}</td>
                <td>{{ $r['maturity_date'] }}</td>
                <td class="text-right">{{ number_format($r['amount_disbursed'], 2) }}</td>
                <td class="text-right">{{ number_format($r['interest'], 2) }}</td>
                <td class="text-right">{{ number_format($r['total_amount'], 2) }}</td>
                <td class="text-right">{{ number_format($r['principal_paid'], 2) }}</td>
                <td class="text-right">{{ number_format($r['interest_paid'], 2) }}</td>
                <td class="text-right">{{ number_format($r['penalties_paid'], 2) }}</td>
                <td class="text-right">{{ number_format($r['outstanding_principal'], 2) }}</td>
                <td class="text-right">{{ number_format($r['outstanding_interest'], 2) }}</td>
                <td class="text-right">{{ number_format($r['amount_overdue'], 2) }}</td>
                <td class="text-right">{{ $r['days_in_arrears'] }}</td>
                <td>{{ ucfirst($r['loan_status']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">No data found for selected filters.</div>
    @endif
</body>
</html>
