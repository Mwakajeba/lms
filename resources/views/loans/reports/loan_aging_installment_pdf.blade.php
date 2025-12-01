<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Aging Installment Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5in;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .company-logo {
            max-height: 80px;
            max-width: 200px;
            margin-bottom: 10px;
        }
        .company-details {
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin: 5px 0;
        }
        .company-info {
            font-size: 12px;
            color: #666;
            margin: 2px 0;
        }
        .header h1 {
            margin: 10px 0 5px 0;
            color: #333;
            font-size: 18px;
            font-weight: bold;
        }
        .filter-info {
            margin-bottom: 15px;
            background-color: #f8f9fa;
            padding: 8px;
            border-left: 4px solid #000;
            border: 1px solid #000;
            font-size: 9px;
        }
        .filter-info h3 {
            margin: 0 0 8px 0;
            color: #000;
            font-size: 11px;
        }
        .filter-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .filter-item {
            flex: 1;
            margin-right: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-overdue {
            color: #dc3545;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .totals-row {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 9px;
        }
        /* Responsive column widths for landscape */
        .col-customer { width: 8%; }
        .col-customer-no { width: 6%; }
        .col-phone { width: 7%; }
        .col-loan-no { width: 7%; }
        .col-amount { width: 6%; }
        .col-installment { width: 6%; }
        .col-date { width: 6%; }
        .col-branch { width: 8%; }
        .col-officer { width: 8%; }
        .col-aging { width: 5%; }
    </style>
</head>
<body>
    <div class="header">
        @if($company)
            <div class="company-details">
                @if($company->logo)
                    <div>
                        <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="company-logo">
                    </div>
                @endif
                <div class="company-name">{{ $company->name }}</div>
                @if($company->address)
                    <div class="company-info">{{ $company->address }}</div>
                @endif
                <div class="company-info">
                    @if($company->phone)
                        Phone: {{ $company->phone }}
                    @endif
                    @if($company->phone && $company->email) | @endif
                    @if($company->email)
                        Email: {{ $company->email }}
                    @endif
                </div>
            </div>
        @endif
        <h1>LOAN AGING INSTALLMENT REPORT</h1>
        <p style="margin: 0; font-size: 10px; color: #666;">Outstanding Installment Principal Analysis</p>
    </div>

    <div class="filter-info">
        <h3>Report Parameters</h3>
        <div class="filter-row">
            <div class="filter-item"><strong>As of Date:</strong> {{ $asOfDate }}</div>
            <div class="filter-item">
                <strong>Branch:</strong> 
                @if($branch)
                    {{ $branch->name }}
                @else
                    All Branches
                @endif
            </div>
            <div class="filter-item">
                <strong>Loan Officer:</strong> 
                @if($loanOfficer)
                    {{ $loanOfficer->name }}
                @else
                    All Loan Officers
                @endif
            </div>
            <div class="filter-item"><strong>Generated:</strong> {{ now()->format('Y-m-d H:i:s') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-customer">Customer</th>
                <th class="col-customer-no">Customer No</th>
                <th class="col-phone">Phone</th>
                <th class="col-loan-no">Loan No</th>
                <th class="col-amount">Loan Amount</th>
                <th class="col-installment">Installment Amount</th>
                <th class="col-date">Disbursed Date</th>
                <th class="col-date">Expiry</th>
                <th class="col-branch">Branch</th>
                <th class="col-officer">Loan Officer</th>
                <th class="col-aging">Current</th>
                <th class="col-aging">1-30 Days</th>
                <th class="col-aging">31-60 Days</th>
                <th class="col-aging">61-90 Days</th>
                <th class="col-aging">91+ Days</th>
                <th class="col-aging">Total Due Principal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agingData as $row)
                <tr>
                    <td class="col-customer">{{ $row['customer'] }}</td>
                    <td class="col-customer-no text-center">{{ $row['customer_no'] }}</td>
                    <td class="col-phone text-center">{{ $row['phone'] }}</td>
                    <td class="col-loan-no text-center">{{ $row['loan_no'] }}</td>
                    <td class="col-amount text-right">{{ number_format($row['amount'], 0) }}</td>
                    <td class="col-installment text-right">{{ number_format($row['installment_amount'], 0) }}</td>
                    <td class="col-date text-center">{{ $row['disbursed_no'] }}</td>
                    <td class="col-date text-center">{{ $row['expiry'] }}</td>
                    <td class="col-branch">{{ $row['branch'] }}</td>
                    <td class="col-officer">{{ $row['loan_officer'] }}</td>
                    <td class="col-aging text-right">{{ number_format($row['current'], 0) }}</td>
                    <td class="col-aging text-right">{{ number_format($row['bucket_1_30'], 0) }}</td>
                    <td class="col-aging text-right">{{ number_format($row['bucket_31_60'], 0) }}</td>
                    <td class="col-aging text-right">{{ number_format($row['bucket_61_90'], 0) }}</td>
                    <td class="col-aging text-right">{{ number_format($row['bucket_91_plus'], 0) }}</td>
                    <td class="col-aging text-right" style="font-weight: bold; color: #007bff;">{{ number_format($row['total_overdue'], 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" class="text-center">No aging data found for the selected criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="4" class="text-center">TOTALS</td>
                <td class="text-right">{{ number_format(collect($agingData)->sum('amount'), 0) }}</td>
                <td class="text-right">{{ number_format(collect($agingData)->sum('installment_amount'), 0) }}</td>
                <td colspan="4"></td>
                <td class="text-right">{{ number_format(collect($agingData)->sum('current'), 0) }}</td>
                <td class="text-right">{{ number_format(collect($agingData)->sum('bucket_1_30'), 0) }}</td>
                <td class="text-right">{{ number_format(collect($agingData)->sum('bucket_31_60'), 0) }}</td>
                <td class="text-right">{{ number_format(collect($agingData)->sum('bucket_61_90'), 0) }}</td>
                <td class="text-right">{{ number_format(collect($agingData)->sum('bucket_91_plus'), 0) }}</td>
                <td class="text-right" style="font-weight: bold; color: #007bff;">{{ number_format(collect($agingData)->sum('total_overdue'), 0) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        @if($company)
            <p><strong>{{ $company->name }}</strong> - Loan Aging Installment Report</p>
        @endif
        <p>This report shows outstanding installment principal amounts as of {{ $asOfDate }}</p>
        <p>Generated automatically on {{ now()->format('Y-m-d H:i:s') }} | Page 1 of 1 | Confidential Document</p>
    </div>
</body>
</html>
