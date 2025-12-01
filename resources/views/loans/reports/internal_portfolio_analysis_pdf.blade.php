<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Internal Portfolio Analysis Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 20px 30px;
            padding: 0;
        }
        
        @page {
            margin: 20mm 15mm;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .company-logo {
            max-height: 70px;
            margin-bottom: 8px;
        }
        
        .company-info h1 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #000;
        }
        
        .company-info p {
            margin: 1px 0;
            color: #666;
            font-size: 10px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            color: #007bff;
        }
        
        .report-info {
            margin-bottom: 15px;
        }
        
        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .report-info td {
            padding: 4px;
            border: 1px solid #000;
            font-size: 10px;
        }
        
        .report-info .label {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 120px;
        }
        
        .summary-section {
            margin-bottom: 20px;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 8px;
            text-align: center;
            border: 2px solid #000;
            vertical-align: top;
        }
        
        .summary-card h3 {
            font-size: 11px;
            margin-bottom: 3px;
            color: #007bff;
        }
        
        .summary-card .value {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }
        
        .analysis-section {
            margin-bottom: 15px;
        }
        
        .analysis-cards {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .analysis-card {
            display: table-cell;
            width: 50%;
            padding: 6px;
            border: 1px solid #000;
            vertical-align: top;
        }
        
        .analysis-card h4 {
            font-size: 11px;
            margin-bottom: 4px;
            color: #007bff;
        }
        
        .analysis-card p {
            font-size: 9px;
            margin: 2px 0;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 8px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: left;
        }
        
        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .risk-low { background-color: #d4edda; color: #155724; }
        .risk-medium { background-color: #fff3cd; color: #856404; }
        .risk-high { background-color: #f8d7da; color: #721c24; }
        .risk-critical { background-color: #f5c6cb; color: #721c24; }
        
        .exposure-current { background-color: #d4edda; color: #155724; }
        .exposure-low { background-color: #cce5ff; color: #004085; }
        .exposure-medium { background-color: #fff3cd; color: #856404; }
        .exposure-high { background-color: #f8d7da; color: #721c24; }
        .exposure-critical { background-color: #f5c6cb; color: #721c24; }
        
        .at-risk { color: #dc3545; font-weight: bold; }
        .overdue { color: #ffc107; font-weight: bold; }
        .current { color: #28a745; }
        
        .footer {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 8px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            @if($company && $company->logo)
                <img src="{{ public_path('storage/' . $company->logo) }}" alt="Company Logo" class="company-logo">
            @endif
            
            <div class="company-info">
                @if($company)
                    <h1>{{ $company->name }}</h1>
                    @if($company->address)
                        <p>{{ $company->address }}</p>
                    @endif
                    @if($company->phone)
                        <p>Phone: {{ $company->phone }}</p>
                    @endif
                    @if($company->email)
                        <p>Email: {{ $company->email }}</p>
                    @endif
                @else
                    <h1>LOAN MANAGEMENT SYSTEM</h1>
                @endif
            </div>
            
            <h2 class="report-title">INTERNAL PORTFOLIO ANALYSIS REPORT (PAR {{ $par_days }})</h2>
            <p style="font-size: 10px; color: #666; margin-top: 5px;">Conservative Analysis - Only Overdue Amounts at Risk</p>
        </div>

        <!-- Report Information -->
        <div class="report-info">
            <table>
                <tr>
                    <td class="label">Report Date:</td>
                    <td>{{ $generated_date }}</td>
                    <td class="label">As of Date:</td>
                    <td>{{ \Carbon\Carbon::parse($as_of_date)->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td class="label">PAR Days:</td>
                    <td>{{ $par_days }} days</td>
                    <td class="label">Branch:</td>
                    <td>{{ $branch_name }}</td>
                </tr>
                <tr>
                    <td class="label">Group:</td>
                    <td>{{ $group_name }}</td>
                    <td class="label">Loan Officer:</td>
                    <td>{{ $loan_officer_name }}</td>
                </tr>
                <tr>
                    <td class="label">Total Loans:</td>
                    <td colspan="3"><strong>{{ count($analysis_data) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Summary Section -->
        @if(count($analysis_data) > 0)
        <div class="summary-section">
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Portfolio</h3>
                    <div class="value">TZS {{ number_format(array_sum(array_column($analysis_data, 'outstanding_balance')), 0) }}</div>
                </div>
                <div class="summary-card">
                    <h3>Total Overdue</h3>
                    <div class="value">TZS {{ number_format(array_sum(array_column($analysis_data, 'overdue_amount')), 0) }}</div>
                </div>
                <div class="summary-card">
                    <h3>At Risk Amount</h3>
                    <div class="value">TZS {{ number_format(array_sum(array_column($analysis_data, 'at_risk_amount')), 0) }}</div>
                </div>
                <div class="summary-card">
                    <h3>Loans at Risk</h3>
                    <div class="value">{{ count(array_filter($analysis_data, function($item) { return $item['is_at_risk']; })) }} / {{ count($analysis_data) }}</div>
                </div>
            </div>
        </div>

        <!-- Analysis Section -->
        <div class="analysis-section">
            <div class="analysis-cards">
                <div class="analysis-card">
                    <h4>Portfolio Ratios</h4>
                    @php
                        $totalOutstanding = array_sum(array_column($analysis_data, 'outstanding_balance'));
                        $totalOverdue = array_sum(array_column($analysis_data, 'overdue_amount'));
                        $totalAtRisk = array_sum(array_column($analysis_data, 'at_risk_amount'));
                        $overdueRatio = $totalOutstanding > 0 ? ($totalOverdue / $totalOutstanding) * 100 : 0;
                        $conservativeParRatio = $totalOutstanding > 0 ? ($totalAtRisk / $totalOutstanding) * 100 : 0;
                    @endphp
                    <p><strong>Overdue Ratio:</strong> {{ number_format($overdueRatio, 2) }}%</p>
                    <p><strong>Conservative PAR {{ $par_days }}:</strong> {{ number_format($conservativeParRatio, 2) }}%</p>
                    <p><strong>Current Ratio:</strong> {{ number_format(100 - $overdueRatio, 2) }}%</p>
                </div>
                <div class="analysis-card">
                    <h4>Exposure Distribution</h4>
                    @php
                        $exposureCategories = ['Current' => 0, 'Low Exposure' => 0, 'Medium Exposure' => 0, 'High Exposure' => 0, 'Critical Exposure' => 0];
                        foreach ($analysis_data as $loan) {
                            if (isset($exposureCategories[$loan['exposure_category']])) {
                                $exposureCategories[$loan['exposure_category']]++;
                            }
                        }
                    @endphp
                    @foreach($exposureCategories as $category => $count)
                        <p><strong>{{ $category }}:</strong> {{ $count }} loans</p>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 12%;">Customer</th>
                    <th style="width: 7%;">Customer No</th>
                    <th style="width: 7%;">Loan No</th>
                    <th style="width: 8%;">Branch</th>
                    <th style="width: 8%;">Group</th>
                    <th style="width: 8%;">Outstanding</th>
                    <th style="width: 8%;">Overdue</th>
                    <th style="width: 8%;">At Risk</th>
                    <th style="width: 6%;">Overdue %</th>
                    <th style="width: 5%;">Days</th>
                    <th style="width: 8%;">Risk Level</th>
                    <th style="width: 12%;">Exposure Category</th>
                </tr>
            </thead>
            <tbody>
                @if(count($analysis_data) > 0)
                    @foreach($analysis_data as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['customer'] }}</td>
                        <td class="text-center">{{ $row['customer_no'] }}</td>
                        <td class="text-center">{{ $row['loan_no'] }}</td>
                        <td>{{ $row['branch'] }}</td>
                        <td>{{ $row['group'] }}</td>
                        <td class="text-right">{{ number_format($row['outstanding_balance'], 0) }}</td>
                        <td class="text-right overdue">{{ number_format($row['overdue_amount'], 0) }}</td>
                        <td class="text-right at-risk">{{ number_format($row['at_risk_amount'], 0) }}</td>
                        <td class="text-center">{{ $row['overdue_ratio'] }}%</td>
                        <td class="text-center">{{ $row['days_in_arrears'] }}</td>
                        <td class="text-center">
                            <span class="risk-{{ strtolower($row['risk_level']) }}">
                                {{ $row['risk_level'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="exposure-{{ strtolower(str_replace(' ', '-', $row['exposure_category'])) }}">
                                {{ $row['exposure_category'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    
                    <!-- Summary Row -->
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="6" class="text-center">TOTALS</td>
                        <td class="text-right">TZS {{ number_format($totalOutstanding, 0) }}</td>
                        <td class="text-right">TZS {{ number_format($totalOverdue, 0) }}</td>
                        <td class="text-right">TZS {{ number_format($totalAtRisk, 0) }}</td>
                        <td class="text-center">{{ number_format($overdueRatio, 2) }}%</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">{{ count($analysis_data) }} Loans</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="13" class="text-center" style="padding: 20px; color: #28a745; font-weight: bold;">
                            No loans found matching the selected criteria.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>This report was generated on {{ $generated_date }} | System: Loan Management System</p>
            <p>Internal Portfolio Analysis (Conservative PAR {{ $par_days }}) as of {{ \Carbon\Carbon::parse($as_of_date)->format('d-m-Y') }}</p>
            <p><strong>Note:</strong> This conservative approach shows only overdue amounts as at-risk, providing detailed exposure analysis for internal use.</p>
        </div>
    </div>
</body>
</html>
