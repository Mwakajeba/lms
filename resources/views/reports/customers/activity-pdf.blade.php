<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Activity Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
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
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-info {
            font-size: 10px;
            color: #666;
        }
        .summary-section {
            margin-bottom: 15px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }
        .summary-item {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .summary-label {
            font-size: 8px;
            color: #666;
            margin-bottom: 3px;
        }
        .summary-value {
            font-size: 12px;
            font-weight: bold;
        }
        .summary-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 8px;
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
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .page-break {
            page-break-before: always;
        }
        .filter-info {
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 3px;
            margin-bottom: 15px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">CUSTOMER ACTIVITY REPORT</div>
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
        Activity Type: {{ $activityTypeName }} | 
        Transaction Type: {{ $transactionTypeName }}
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3>Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Activities</div>
                <div class="summary-value text-primary">{{ number_format($activityData['summary']['total_activities']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Loan Applications</div>
                <div class="summary-value text-success">{{ number_format($activityData['summary']['loan_applications']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Loan Repayments</div>
                <div class="summary-value text-info">{{ number_format($activityData['summary']['loan_repayments']) }}</div>
            </div>
        </div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Collateral Deposits</div>
                <div class="summary-value text-warning">{{ number_format($activityData['summary']['collateral_deposits']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">GL Transactions</div>
                <div class="summary-value text-secondary">{{ number_format($activityData['summary']['gl_transactions']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Amount</div>
                <div class="summary-value text-danger">{{ number_format($activityData['summary']['total_amount'], 2) }}</div>
            </div>
        </div>
        <div class="summary-grid-2">
            <div class="summary-item">
                <div class="summary-label">Unique Customers</div>
                <div class="summary-value text-primary">{{ number_format($activityData['summary']['unique_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Unique Branches</div>
                <div class="summary-value text-info">{{ number_format($activityData['summary']['unique_branches']) }}</div>
            </div>
        </div>
    </div>

    <!-- Activity Details -->
    <div class="activity-section">
        <h3>Activity Details</h3>
        @if($activityData['data']->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date & Time</th>
                        <th>Customer No</th>
                        <th>Customer Name</th>
                        <th>Branch</th>
                        <th>Activity Type</th>
                        <th>Transaction Type</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                        <th>Reference ID</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activityData['data'] as $index => $activity)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($activity['date'])->format('d/m/Y H:i') }}</td>
                            <td>{{ $activity['customer_no'] }}</td>
                            <td>{{ $activity['customer_name'] }}</td>
                            <td>{{ $activity['branch_name'] }}</td>
                            <td class="text-center">
                                <span class="{{ 
                                    $activity['activity_type'] === 'Loan Application' ? 'text-success' : 
                                    ($activity['activity_type'] === 'Loan Repayment' ? 'text-info' : 
                                    ($activity['activity_type'] === 'Collateral Deposit' ? 'text-warning' : 'text-secondary')) 
                                }}">
                                    {{ $activity['activity_type'] }}
                                </span>
                            </td>
                            <td class="text-center">{{ ucfirst(str_replace('_', ' ', $activity['transaction_type'])) }}</td>
                            <td>{{ Str::limit($activity['description'], 30) }}</td>
                            <td class="text-right">{{ number_format($activity['amount'], 2) }}</td>
                            <td class="text-center">
                                <span class="{{ 
                                    $activity['status'] === 'active' || $activity['status'] === 'completed' ? 'text-success' : 
                                    ($activity['status'] === 'pending' ? 'text-warning' : 
                                    ($activity['status'] === 'credit' ? 'text-info' : 'text-danger')) 
                                }}">
                                    {{ ucfirst($activity['status']) }}
                                </span>
                            </td>
                            <td>{{ $activity['reference_id'] }}</td>
                            <td>{{ $activity['created_by'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px; color: #666;">
                <p>No customer activities found for the selected criteria.</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $company->name }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Branch: {{ $branchName }} | Customer: {{ $customerName }} | Activity Type: {{ $activityTypeName }}</p>
    </div>
</body>
</html>
