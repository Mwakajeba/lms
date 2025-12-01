<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Loan Disbursement Report</title>
    <style>
        @page {
            size: A3 landscape;
            margin: 30px 20px 30px 20px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 22px;
            color: #222;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .header p {
            margin: 2px 0;
            color: #555;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff;
        }

        th,
        td {
            border: 1px solid #bbb;
            padding: 7px 4px;
            font-size: 10px;
        }

        th {
            background-color: #e9ecef;
            color: #222;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        td {
            vertical-align: middle;
        }

        tr:nth-child(even) td {
            background-color: #f7f7fa;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .totals-row td {
            background: #d1e7dd;
            font-weight: bold;
            color: #222;
            border-top: 2px solid #222;
        }

        .footer {
            width: 100%;
            margin-top: 20px;
            text-align: right;
        }

        .footer p {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin: 0;
        }
    </style>
</head>

<body>

    <div style="text-align:center; margin-bottom:10px;">
        <img src="{{ public_path('assets/logo.png') }}" alt="Company Logo" style="height:60px; margin-bottom:5px;">
        <div style="font-size:18px; font-weight:bold; color:#222; margin-bottom:2px;">
            {{ config('app.name', 'SmartFinance') }}</div>
    </div>
    <div class="header">
        <h1>Loan Disbursement Report</h1>
        <p><strong>FROM:</strong> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
        <p><strong>BRANCH:</strong> {{ $branch->name ?? 'ALL' }}</p>
        <p><strong>REPORT DATE:</strong> {{ \Carbon\Carbon::now()->format('M d, Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 7%">A/C NO.</th>
                <th style="width: 8%">Disbursement Date</th>
                <th style="width: 5%">Period</th>
                <th style="width: 5%">Loan Officer</th>
                <th style="width: 11%">Customer Name</th>
                <th style="width: 7%">Customer No</th>
                <th style="width:7%">Group Name</th>
                <th style="width: 8%">Loan Product</th>
                <th style="width: 7%">Loan No</th>
                <th style="width: 8%">Disbursed Amount</th>
                <th style="width: 8%">Interest Amount</th>
                <th style="width: 8%">Amount to Pay</th>
                <th style="width: 5%">Rate (%)</th>
                <th style="width: 8%">End Date</th>
                <th style="width: 8%">Loan Officer</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDisbursed = 0;
                $totalInterest = 0;
                $totalToPay = 0;
            @endphp
            @foreach ($disbursements as $disbursement)
                <tr>
                    <td class="text-center">{{ $disbursement->customer->customerNo ?? 'N/A' }} -
                        {{ $disbursement->loanNo ?? 'N/A' }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($disbursement->disbursed_on)->format('M d, Y') }}
                    </td>
                    <td class="text-center">{{ $disbursement->period }}</td>
                    <td>{{ $disbursement->loanOfficer->name ?? 'N/A' }}</td>
                    <td>{{ $disbursement->customer->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $disbursement->customer->customerNo ?? 'N/A' }}</td>
                    <td>{{ $disbursement->group->name ?? 'N/A' }}</td>
                    <td>{{ $disbursement->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $disbursement->loanNo ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($disbursement->amount, 2) }}</td>
                    <td class="text-right">{{ number_format($disbursement->interest_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($disbursement->amount_total, 2) }}</td>
                    <td class="text-right">{{ number_format($disbursement->interest, 2) }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($disbursement->last_repayment_date)->format('M d, Y') }}</td>
                    <td>{{ $disbursement->loanOfficer->name ?? 'N/A' }}</td>
                </tr>
                @php
                    $totalDisbursed += $disbursement->amount;
                    $totalInterest += $disbursement->interest_amount;
                    $totalToPay += $disbursement->amount_total;
                @endphp
            @endforeach
            <tr class="totals-row">
                <td colspan="7" class="text-right">TOTALS</td>
                <td class="text-right">{{ number_format($totalDisbursed, 2) }}</td>
                <td class="text-right">{{ number_format($totalInterest, 2) }}</td>
                <td class="text-right">{{ number_format($totalToPay, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

</body>

</html>
