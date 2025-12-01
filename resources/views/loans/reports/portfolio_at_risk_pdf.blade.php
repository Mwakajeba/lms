<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Portfolio at Risk (PAR) Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
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
            font-size: 11px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            color: #fd7e14;
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
            font-size: 11px;
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
            font-size: 12px;
            margin-bottom: 3px;
            color: #fd7e14;
        }
        
        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 3px 2px;
            text-align: left;
            font-size: 8px;
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
        
        .at-risk { color: #dc3545; font-weight: bold; }
        .not-at-risk { color: #28a745; }
        
        .footer {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 8px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .par-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 3px;
        }
        
        .par-safe { background-color: #28a745; }
        .par-warning { background-color: #ffc107; }
        .par-danger { background-color: #dc3545; }
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
            
            <h2 class="report-title">PORTFOLIO AT RISK (PAR {{ $par_days }}) REPORT</h2>
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
                    <td colspan="3"><strong>{{ count($par_data) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Summary Section -->
        @if(count($par_data) > 0)
        <div class="summary-section">
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Portfolio</h3>
                    <div class="value">TZS {{ number_format(array_sum(array_column($par_data, 'outstanding_balance')), 2) }}</div>
                </div>
                <div class="summary-card">
                    <h3>At Risk Amount</h3>
                    <div class="value">TZS {{ number_format(array_sum(array_column($par_data, 'at_risk_amount')), 2) }}</div>
                </div>
                <div class="summary-card">
                    <h3>PAR {{ $par_days }} Ratio</h3>
                    <div class="value">
                        {{ array_sum(array_column($par_data, 'outstanding_balance')) > 0 ? 
                           number_format((array_sum(array_column($par_data, 'at_risk_amount')) / array_sum(array_column($par_data, 'outstanding_balance'))) * 100, 1) : '0' }}%
                    </div>
                </div>
                <div class="summary-card">
                    <h3>Loans at Risk</h3>
                    <div class="value">{{ count(array_filter($par_data, function($item) { return $item['is_at_risk']; })) }} / {{ count($par_data) }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 10%;">Customer</th>
                    <th style="width: 6%;">Customer No</th>
                    <th style="width: 6%;">Phone</th>
                    <th style="width: 6%;">Loan No</th>
                    <th style="width: 7%;">Loan Amount</th>
                    <th style="width: 6%;">Branch</th>
                    <th style="width: 6%;">Group</th>
                    <th style="width: 8%;">Officer</th>
                    <th style="width: 8%;">Outstanding</th>
                    <th style="width: 8%;">At Risk</th>
                    <th style="width: 5%;">Risk %</th>
                    <th style="width: 4%;">Days</th>
                    <th style="width: 6%;">Risk Level</th>
                    <th style="width: 4%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @if(count($par_data) > 0)
                    @foreach($par_data as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['customer'] }}</td>
                        <td class="text-center">{{ $row['customer_no'] }}</td>
                        <td class="text-center">{{ $row['phone'] }}</td>
                        <td class="text-center">{{ $row['loan_no'] }}</td>
                        <td class="text-right">{{ number_format($row['loan_amount'], 0) }}</td>
                        <td>{{ $row['branch'] }}</td>
                        <td>{{ $row['group'] }}</td>
                        <td>{{ $row['loan_officer'] }}</td>
                        <td class="text-right">{{ number_format($row['outstanding_balance'], 2) }}</td>
                        <td class="text-right at-risk">{{ number_format($row['at_risk_amount'], 2) }}</td>
                        <td class="text-center">
                            <span class="par-indicator 
                                @if($row['risk_percentage'] == 0) par-safe 
                                @elseif($row['risk_percentage'] < 50) par-warning 
                                @else par-danger @endif">
                            </span>
                            {{ $row['risk_percentage'] }}%
                        </td>
                        <td class="text-center">{{ $row['days_in_arrears'] }}</td>
                        <td class="text-center">
                            <span class="risk-{{ strtolower($row['risk_level']) }}">
                                {{ $row['risk_level'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($row['is_at_risk'])
                                Risk
                            @else
                                Safe
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    
                    <!-- Summary Row -->
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="9" class="text-center">TOTALS</td>
                        <td class="text-right">TZS {{ number_format(array_sum(array_column($par_data, 'outstanding_balance')), 2) }}</td>
                        <td class="text-right">TZS {{ number_format(array_sum(array_column($par_data, 'at_risk_amount')), 2) }}</td>
                        <td class="text-center">
                            {{ array_sum(array_column($par_data, 'outstanding_balance')) > 0 ? 
                               number_format((array_sum(array_column($par_data, 'at_risk_amount')) / array_sum(array_column($par_data, 'outstanding_balance'))) * 100, 1) : '0' }}%
                        </td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">{{ count($par_data) }} Loans</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="15" class="text-center" style="padding: 20px; color: #28a745; font-weight: bold;">
                            No data found for the selected filters and PAR criteria.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>This report was generated on {{ $generated_date }} | System: Loan Management System</p>
            <p>PAR {{ $par_days }} Report as of {{ \Carbon\Carbon::parse($as_of_date)->format('d-m-Y') }}</p>
        </div>
    </div>
</body>
</html>
