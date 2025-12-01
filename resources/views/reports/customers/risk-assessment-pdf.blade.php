<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Risk Assessment Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            line-height: 1.1;
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
            padding: 2px;
            text-align: left;
            font-size: 6px;
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
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">CUSTOMER RISK ASSESSMENT REPORT</div>
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
        Risk Level: {{ $riskLevelName }} | 
        Assessment Type: {{ $assessmentTypeName }}
    </div>

    <!-- Risk Summary -->
    <div class="summary-section">
        <div class="section-title">Risk Level Distribution</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Customers</div>
                <div class="summary-value text-primary">{{ number_format($riskData['summary']['total_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Low Risk</div>
                <div class="summary-value text-success">{{ number_format($riskData['summary']['low_risk_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Medium Risk</div>
                <div class="summary-value text-warning">{{ number_format($riskData['summary']['medium_risk_customers']) }}</div>
            </div>
        </div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">High Risk</div>
                <div class="summary-value text-danger">{{ number_format($riskData['summary']['high_risk_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Average Risk Score</div>
                <div class="summary-value text-info">{{ number_format($riskData['summary']['average_risk_score'], 1) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">With Overdue</div>
                <div class="summary-value text-secondary">{{ number_format($riskData['summary']['customers_with_overdue']) }}</div>
            </div>
        </div>
    </div>

    <!-- Financial Risk Summary -->
    <div class="summary-section">
        <div class="section-title">Financial Risk Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Loan Amount</div>
                <div class="summary-value text-primary">{{ number_format($riskData['summary']['total_loan_amount'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Outstanding Amount</div>
                <div class="summary-value text-danger">{{ number_format($riskData['summary']['total_outstanding_amount'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Collateral</div>
                <div class="summary-value text-success">{{ number_format($riskData['summary']['total_collateral_value'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Risk Assessment Details -->
    <div class="risk-assessment-section">
        <div class="section-title">Customer Risk Assessment Details</div>
        @if($riskData['data']->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer No</th>
                        <th>Customer Name</th>
                        <th>Branch</th>
                        <th>Age</th>
                        <th>Category</th>
                        <th class="text-center">Loans</th>
                        <th class="text-right">Loan Amount</th>
                        <th class="text-right">Outstanding</th>
                        <th class="text-right">Collateral</th>
                        <th class="text-center">Repayment Rate (%)</th>
                        <th class="text-center">Days Overdue</th>
                        <th class="text-center">Risk Score</th>
                        <th>Risk Level</th>
                        <th class="text-center">Overdue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($riskData['data'] as $index => $customer)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $customer['customer_no'] }}</td>
                            <td>{{ $customer['customer_name'] }}</td>
                            <td>{{ $customer['branch_name'] }}</td>
                            <td class="text-center">{{ $customer['age'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ ucfirst($customer['category']) }}</td>
                            <td class="text-center">{{ $customer['total_loans'] }}</td>
                            <td class="text-right">{{ number_format($customer['total_loan_amount'], 2) }}</td>
                            <td class="text-right">
                                <span class="{{ $customer['outstanding_amount'] > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($customer['outstanding_amount'], 2) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="{{ $customer['has_collateral'] ? 'text-success' : 'text-warning' }}">
                                    {{ number_format($customer['total_collateral'], 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ 
                                    $customer['repayment_rate'] >= 80 ? 'text-success' : 
                                    ($customer['repayment_rate'] >= 60 ? 'text-warning' : 'text-danger') 
                                }}">
                                    {{ number_format($customer['repayment_rate'], 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ 
                                    $customer['average_days_overdue'] <= 7 ? 'text-success' : 
                                    ($customer['average_days_overdue'] <= 30 ? 'text-warning' : 'text-danger') 
                                }}">
                                    {{ number_format($customer['average_days_overdue'], 0) }}d
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ 
                                    $customer['risk_score'] >= 80 ? 'text-success' : 
                                    ($customer['risk_score'] >= 50 ? 'text-warning' : 'text-danger') 
                                }}">
                                    {{ number_format($customer['risk_score'], 1) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ 
                                    $customer['risk_level'] === 'low' ? 'text-success' : 
                                    ($customer['risk_level'] === 'medium' ? 'text-warning' : 'text-danger') 
                                }}">
                                    {{ ucfirst($customer['risk_level']) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ $customer['has_overdue'] ? 'text-danger' : 'text-success' }}">
                                    {{ $customer['has_overdue'] ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px; color: #666;">
                <p>No customer risk assessment data found for the selected criteria.</p>
            </div>
        @endif
    </div>

    <!-- Risk Factors Summary -->
    <div class="summary-section">
        <div class="section-title">Risk Factors Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Risk Factor</th>
                    <th class="text-center">Count</th>
                    <th class="text-center">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $riskFactors = [];
                    foreach($riskData['data'] as $customer) {
                        foreach($customer['risk_factors'] as $factor) {
                            $riskFactors[$factor] = ($riskFactors[$factor] ?? 0) + 1;
                        }
                    }
                    $totalCustomers = $riskData['data']->count();
                @endphp
                @foreach($riskFactors as $factor => $count)
                    <tr>
                        <td>{{ $factor }}</td>
                        <td class="text-center">{{ $count }}</td>
                        <td class="text-center">{{ $totalCustomers > 0 ? number_format(($count / $totalCustomers) * 100, 1) : 0 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $company->name }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Branch: {{ $branchName }} | Customer: {{ $customerName }} | Risk Level: {{ $riskLevelName }} | Assessment Type: {{ $assessmentTypeName }}</p>
    </div>
</body>
</html>
