<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Loan Repayment Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header p {
            margin: 0;
            padding: 0;
            color: #666;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            word-wrap: break-word;
            font-size: 9px;
        }

        th {
            background-color: #f2f2f2;
            color: #555;
            font-weight: bold;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            width: 100%;
            margin-top: 20px;
            text-align: right;
            border-top: 2px solid #333;
            padding-top: 10px;
        }

        .footer p {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Loan Repayment Report</h1>
        <p><strong>BRANCH:</strong> {{ $branch->name ?? 'ALL' }}</p>
        <p><strong>FROM:</strong> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
        <p><strong>REPORT DATE:</strong> {{ \Carbon\Carbon::now()->format('M d, Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%">Repayment Date</th>
                <th style="width: 8%">Amount Paid</th>
                <th style="width: 8%">Payment Method</th>
                 <th scope="col">Loan Officer</th>
                <th style="width: 12%">Customer Name</th>
                <th style="width: 8%">Loan No</th>
                <th style="width: 10%">Loan Product</th>
                <th style="width: 8%">Principal</th>
                <th style="width: 8%">Interest</th>
                <th style="width: 8%">Fees</th>
                <th style="width: 8%">Penalties</th>
                <th style="width: 8%">Balance</th>
                <th style="width: 8%">Branch</th>
                 <th scope="col">Group Name</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPaid = 0;
                $totalPrincipal = 0;
                $totalInterest = 0;
                $totalFees = 0;
                $totalPenalties = 0;
            @endphp
            @foreach($repayments as $repayment)
            <tr>
                <td>{{ \Carbon\Carbon::parse($repayment->repayment_date)->format('M d, Y') }}</td>
                <td class="text-right">{{ number_format($repayment->amount, 2) }}</td>
                <td>{{ $repayment->chartAccount->account_name ?? 'N/A' }}</td>
                 <td>{{ $repayment->loan->loanOfficer->name ?? 'N/A' }}</td>
                <td>{{ $repayment->loan->customer->name ?? 'N/A' }}</td>
                <td>{{ $repayment->loan->loanNo ?? 'N/A'}}</td>
                <td>{{ $repayment->loan->product->name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($repayment->principal, 2) }}</td>
                <td class="text-right">{{ number_format($repayment->interest, 2) }}</td>
                <td class="text-right">{{ number_format($repayment->fees_amount, 2) }}</td>
                <td class="text-right">{{ number_format($repayment->penalt_amount, 2) }}</td>
                <td class="text-right">{{ number_format($repayment->loan->balance, 2) }}</td>
                <td>{{ $repayment->loan->branch->name ?? 'N/A' }}</td>
                <td>{{ $repayment->loan->group->name ?? 'N/A' }}</td>
            </tr>
            @php
                $totalPaid += $summary['total_paid'];
                $totalPrincipal += $repayment->principal;
                $totalInterest += $repayment->interest;
                $totalFees += $repayment->fees_amount;
                $totalPenalties += $repayment->penalt_amount;
            @endphp
            @endforeach
            <tr>
                <td colspan="1" class="text-right"><strong>TOTALS</strong></td>
                <td class="text-right"><strong>{{ number_format($totalPaid, 2) }}</strong></td>
                <td colspan="4" class="text-right"></td>
                <td class="text-right"><strong>{{ number_format($totalPrincipal, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totalInterest, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totalFees, 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totalPenalties, 2) }}</strong></td>
                <td colspan="2" class="text-right"></td>
            </tr>
        </tbody>
    </table>
</body>

</html>
