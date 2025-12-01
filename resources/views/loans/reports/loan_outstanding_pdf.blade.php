<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Outstanding Balance Report</title>
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
            margin-bottom: 10px;
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
        .summary-section {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-value {
            color: #495057;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .section-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 8px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">LOAN OUTSTANDING BALANCE REPORT</div>
        <div class="report-info">
            As of: {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}
            @if($branch)
                | Branch: {{ $branch->name }}
            @endif
            @if($loanOfficer)
                | Loan Officer: {{ $loanOfficer->name }}
            @endif
        </div>
    </div>

    @if(!empty($outstandingData))
        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-title">SUMMARY</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="summary-label">Total Principal Disbursed:</span>
                    <span class="summary-value">{{ number_format($summary['total_principal_disbursed'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Expected Interest:</span>
                    <span class="summary-value">{{ number_format($summary['total_expected_interest'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Principal Paid:</span>
                    <span class="summary-value">{{ number_format($summary['total_principal_paid'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Interest Paid:</span>
                    <span class="summary-value">{{ number_format($summary['total_paid_interest'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Outstanding Interest:</span>
                    <span class="summary-value">{{ number_format($summary['total_outstanding_interest'], 2) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Accrued Interest:</span>
                    <span class="summary-value">{{ number_format($summary['total_accrued_interest'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <table>
            <thead>
                <tr>
                    <th colspan="10" class="text-center">DISBURSEMENT</th>
                    <th colspan="2" class="text-center">REPAYMENT</th>
                    <th colspan="6" class="text-center">OUTSTANDING & INTEREST BREAKDOWN</th>
                </tr>
                <tr>
                    <th>Customer</th>
                    <th>Customer No</th>
                    <th>Phone</th>
                    <th>Loan No</th>
                    <th>Disbursed Amount</th>
                    <th>Expected Interest</th>
                    <th>Disbursed Date</th>
                    <th>Expiry</th>
                    <th>Branch</th>
                    <th>Loan Officer</th>
                    <th class="text-right">Principal Paid</th>
                    <th class="text-right">Interest Paid</th>
                    <th class="text-right">Outstanding Principal</th>
                    <th class="text-right">Outstanding Interest</th>
                    <th class="text-right">Accrued Interest</th>
                    <th class="text-right">Not Due Interest</th>
                    <th class="text-right">Outstanding Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($outstandingData as $row)
                    <tr>
                        <td>{{ $row['customer'] }}</td>
                        <td>{{ $row['customer_no'] }}</td>
                        <td>{{ $row['phone'] }}</td>
                        <td>{{ $row['loan_no'] }}</td>
                        <td class="text-right">{{ number_format($row['amount'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['interest'], 2) }}</td>
                        <td>{{ $row['disbursed_no'] }}</td>
                        <td>{{ $row['expiry'] }}</td>
                        <td>{{ $row['branch'] }}</td>
                        <td>{{ $row['loan_officer'] }}</td>
                        <td class="text-right">{{ number_format($row['principal_paid'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['interest_paid'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['amount'] - $row['principal_paid'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['outstanding_interest'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['accrued_interest'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['not_due_interest'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['outstanding_balance'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <th colspan="4" class="text-center">TOTALS</th>
                    <th class="text-right">{{ number_format(collect($outstandingData)->sum('amount'), 2) }}</th>
                    <th class="text-right">{{ number_format(collect($outstandingData)->sum('interest'), 2) }}</th>
                    <th colspan="4"></th>
                    <th class="text-right">{{ number_format(collect($outstandingData)->sum('principal_paid'), 2) }}</th>
                    <th class="text-right">{{ number_format(collect($outstandingData)->sum('interest_paid'), 2) }}</th>
                    <th class="text-right">{{ number_format(collect($outstandingData)->sum(function($row) { return $row['amount'] - $row['principal_paid']; }), 2) }}</th>
                    <th class="text-right">{{ number_format(collect($outstandingData)->sum('outstanding_interest'), 2) }}</th>
                    <th class="text-right">{{ number_format(collect($outstandingData)->sum('accrued_interest'), 2) }}</th>
                    <th class="text-right">{{ number_format(collect($outstandingData)->sum('not_due_interest'), 2) }}</th>
                    <th class="text-right">{{ number_format(collect($outstandingData)->sum('outstanding_balance'), 2) }}</th>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="text-center" style="padding: 50px;">
            <p>No outstanding data found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        Generated on {{ now()->format('F d, Y \a\t g:i A') }}
    </div>
</body>
</html>
