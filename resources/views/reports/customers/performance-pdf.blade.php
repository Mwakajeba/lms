<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Performance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-info {
            font-size: 9px;
            color: #666;
        }
        .summary-section {
            margin-bottom: 15px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin-bottom: 10px;
        }
        .summary-item {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .summary-label {
            font-size: 7px;
            color: #666;
            margin-bottom: 2px;
        }
        .summary-value {
            font-size: 10px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 3px;
            text-align: left;
            font-size: 7px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-success {
            color: #28a745;
        }
        .text-primary {
            color: #007bff;
        }
        .text-warning {
            color: #ffc107;
        }
        .text-info {
            color: #17a2b8;
        }
        .text-secondary {
            color: #6c757d;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .page-break {
            page-break-before: always;
        }
        .filter-info {
            background-color: #f8f9fa;
            padding: 6px;
            border-radius: 3px;
            margin-bottom: 15px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">CUSTOMER PERFORMANCE REPORT</div>
        <div class="report-info">
            Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} |
            Generated: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Filter Information -->
    <div class="filter-info">
        <strong>Report Filters:</strong><br>
        Branch: {{ $branchName }} |
        Customer: {{ $customerName }} |
        Performance Level: {{ $performanceMetricName }} |
        Risk Level: {{ $riskLevelName }}
    </div>


    <!-- Performance Details -->
    <div class="performance-section">
        <h3>Customer Performance Details</h3>
        @if($performanceData['data']->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer No</th>
                        <th>Customer Name</th>
                        <th>Branch</th>
                        <th>Region</th>
                        <th>Date Registered</th>
                        <th class="text-center">Total Loans</th>
                        <th class="text-right">Loan Amount</th>
                        <th class="text-right">Repayments</th>
                        <th class="text-right">Collateral</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalLoans = 0;
                        $totalLoanAmount = 0;
                        $totalRepayments = 0;
                        $totalCollateral = 0;
                    @endphp
                    @foreach($performanceData['data'] as $index => $customer)
                        @php
                            $totalLoans += $customer['total_loans'];
                            $totalLoanAmount += $customer['total_loan_amount'];
                            $totalRepayments += $customer['total_repayments'];
                            $totalCollateral += $customer['total_collateral'];
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $customer['customer_no'] }}</td>
                            <td>{{ $customer['customer_name'] }}</td>
                            <td>{{ $customer['branch_name'] }}</td>
                            <td>{{ $customer['region_name'] }}</td>
                            <td>{{ $customer['date_registered'] ? $customer['date_registered']->format('d/m/Y') : 'N/A' }}</td>
                            <td class="text-center">{{ $customer['total_loans'] }}</td>
                            <td class="text-right">{{ number_format($customer['total_loan_amount'], 2) }}</td>
                            <td class="text-right">{{ number_format($customer['total_repayments'], 2) }}</td>
                            <td class="text-right">{{ number_format($customer['total_collateral'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="6" class="text-right" style="font-weight: bold;">TOTAL</td>
                        <td class="text-center" style="font-weight: bold;">{{ $totalLoans }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ number_format($totalLoanAmount, 2) }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ number_format($totalRepayments, 2) }}</td>
                        <td class="text-right" style="font-weight: bold;">{{ number_format($totalCollateral, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px; color: #666;">
                <p>No customer performance data found for the selected criteria.</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $company->name }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Branch: {{ $branchName }} | Customer: {{ $customerName }} | Performance: {{ $performanceMetricName }} | Risk: {{ $riskLevelName }}</p>
    </div>
</body>
</html>
