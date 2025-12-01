<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Calculation Report</title>
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
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 18px;
            color: #666;
        }
        .summary-section {
            margin-bottom: 30px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-item {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .summary-label {
            font-size: 12px;
            color: #666;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .loan-details {
            margin-bottom: 30px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }
        .detail-label {
            width: 150px;
            font-weight: bold;
        }
        .detail-value {
            flex: 1;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .schedule-table th,
        .schedule-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        .schedule-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .schedule-table th:first-child,
        .schedule-table td:first-child {
            text-align: center;
        }
        .totals-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">SmartFinance</div>
        <div class="report-title">Loan Calculation Report</div>
        <div>Generated on: {{ $generated_at->format('d/m/Y H:i:s') }}</div>
    </div>

    @if($calculation['success'])
        @php
            $product = $calculation['product'];
            $totals = $calculation['totals'];
            $schedule = $calculation['schedule'];
            $fees = $calculation['fees'];
            $summary = $calculation['summary'];
        @endphp

        <!-- Loan Summary -->
        <div class="summary-section">
            <div class="section-title">Loan Summary</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value">{{ number_format($totals['principal'], 2) }}</div>
                    <div class="summary-label">Loan Amount</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ number_format($totals['total_interest'], 2) }}</div>
                    <div class="summary-label">Total Interest</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ number_format($totals['total_fees'], 2) }}</div>
                    <div class="summary-label">Total Fees</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ number_format($totals['total_amount'], 2) }}</div>
                    <div class="summary-label">Total Amount</div>
                </div>
            </div>
        </div>

        <!-- Loan Details -->
        <div class="loan-details">
            <div class="section-title">Loan Details</div>
            <div class="detail-row">
                <div class="detail-label">Product:</div>
                <div class="detail-value">{{ $product['name'] }} ({{ ucfirst($product['product_type']) }})</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Interest Method:</div>
                <div class="detail-value">{{ ucfirst(str_replace('_', ' ', $product['interest_method'])) }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Interest Cycle:</div>
                <div class="detail-value">{{ ucfirst($product['interest_cycle']) }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Grace Period:</div>
                <div class="detail-value">{{ $product['grace_period'] }} days</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Monthly Payment:</div>
                <div class="detail-value"><strong>{{ number_format($totals['monthly_payment'], 2) }}</strong></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Interest Percentage:</div>
                <div class="detail-value">{{ $summary['interest_percentage'] }}%</div>
            </div>
        </div>

        @if(count($fees) > 0)
        <!-- Fees Breakdown -->
        <div class="fees-section">
            <div class="section-title">Fees Breakdown</div>
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Fee Name</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Application</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fees as $fee)
                    <tr>
                        <td>{{ $fee['name'] }}</td>
                        <td>{{ ucfirst($fee['type']) }}</td>
                        <td>{{ number_format($fee['amount'], 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $fee['criteria'])) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Repayment Schedule -->
        <div class="schedule-section">
            <div class="section-title">Repayment Schedule</div>
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Due Date</th>
                        <th>Principal</th>
                        <th>Interest</th>
                        <th>Fees</th>
                        <th>Total</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedule as $installment)
                    <tr>
                        <td>{{ $installment['installment_number'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($installment['due_date'])->format('d/m/Y') }}</td>
                        <td>{{ number_format($installment['principal'], 2) }}</td>
                        <td>{{ number_format($installment['interest'], 2) }}</td>
                        <td>{{ number_format($installment['fee_amount'], 2) }}</td>
                        <td><strong>{{ number_format($installment['total_amount'], 2) }}</strong></td>
                        <td>{{ number_format($installment['remaining_balance'] ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="totals-row">
                        <td colspan="2"><strong>Total</strong></td>
                        <td><strong>{{ number_format(array_sum(array_column($schedule, 'principal')), 2) }}</strong></td>
                        <td><strong>{{ number_format(array_sum(array_column($schedule, 'interest')), 2) }}</strong></td>
                        <td><strong>{{ number_format(array_sum(array_column($schedule, 'fee_amount')), 2) }}</strong></td>
                        <td><strong>{{ number_format(array_sum(array_column($schedule, 'total_amount')), 2) }}</strong></td>
                        <td><strong>{{ number_format(end($schedule)['remaining_balance'] ?? 0, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

    @else
        <div class="error-section">
            <div class="section-title">Error</div>
            <p>{{ $calculation['error'] ?? 'An error occurred during calculation' }}</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by SmartFinance Loan Calculator</p>
        <p>For any queries, please contact your loan officer</p>
    </div>
</body>
</html>
