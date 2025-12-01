<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Arrears Report</title>
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
            color: #d32f2f;
        }
        
        .report-info {
            margin-bottom: 15px;
        }
        
        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .report-info td {
            padding: 5px;
            border: 1px solid #000;
        }
        
        .report-info .label {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 150px;
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
            color: #d32f2f;
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
            padding: 4px 3px;
            text-align: left;
            font-size: 9px;
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
        
        .severity-low { background-color: #e8f5e8; }
        .severity-medium { background-color: #fff3cd; }
        .severity-high { background-color: #f8d7da; }
        .severity-critical { background-color: #d1ecf1; }
        
        .amount {
            font-weight: bold;
            color: #d32f2f;
        }
        
        .footer {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .page-break {
            page-break-before: always;
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
                    <h3>{{ $company->address }}</h3>
                @endif
                @if($company->phone)
                    <h3>Phone: {{ $company->phone }}</h3>
                @endif
                @if($company->email)
                    <h3>Email: {{ $company->email }}</h3>
                @endif
            @else
            @endif
        </div>
        
        <h2>LOAN ARREARS REPORT</h2>
    </div>

    <!-- Report Information -->
    <div class="report-info">
        <table>
            <tr>
                <td class="label">Report Date:</td>
                <td>{{ $generated_date }}</td>
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
                <td class="label">Total Loans in Arrears:</td>
                <td colspan="3"><strong>{{ count($arrears_data) }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Summary Section -->
    @if(count($arrears_data) > 0)
    <div class="summary-section">
        <div class="summary-cards">
            <div class="summary-card">
                <h3>Total Loans</h3>
                <div class="value">{{ count($arrears_data) }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Arrears</h3>
                <div class="value">TZS {{ number_format(array_sum(array_column($arrears_data, 'arrears_amount')), 2) }}</div>
            </div>
            <div class="summary-card">
                <h3>Avg Days</h3>
                <div class="value">{{ round(array_sum(array_column($arrears_data, 'days_in_arrears')) / count($arrears_data)) }} days</div>
            </div>
            <div class="summary-card">
                <h3>Critical Cases</h3>
                <div class="value">{{ count(array_filter($arrears_data, function($item) { return $item['days_in_arrears'] > 90; })) }}</div>
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
                <th style="width: 7%;">Phone</th>
                <th style="width: 7%;">Loan No</th>
                <th style="width: 9%;">Loan Amount</th>
                <th style="width: 7%;">Disbursed</th>
                <th style="width: 8%;">Branch</th>
                <th style="width: 8%;">Group</th>
                <th style="width: 9%;">Officer</th>
                <th style="width: 10%;">Arrears Amount</th>
                <th style="width: 5%;">Days</th>
                <th style="width: 4%;">Items</th>
                <th style="width: 5%;">Severity</th>
            </tr>
        </thead>
        <tbody>
            @if(count($arrears_data) > 0)
                @foreach($arrears_data as $index => $row)
                <tr class="severity-{{ strtolower($row['arrears_severity']) }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['customer'] }}</td>
                    <td class="text-center">{{ $row['customer_no'] }}</td>
                    <td class="text-center">{{ $row['phone'] }}</td>
                    <td class="text-center">{{ $row['loan_no'] }}</td>
                    <td class="text-right">{{ number_format($row['loan_amount'], 0) }}</td>
                    <td class="text-center">{{ $row['disbursed_date'] }}</td>
                    <td>{{ $row['branch'] }}</td>
                    <td>{{ $row['group'] }}</td>
                    <td>{{ $row['loan_officer'] }}</td>
                    <td class="text-right amount">{{ number_format($row['arrears_amount'], 2) }}</td>
                    <td class="text-center">{{ $row['days_in_arrears'] }}</td>
                    <td class="text-center">{{ $row['overdue_schedules_count'] }}</td>
                    <td class="text-center">{{ $row['arrears_severity'] }}</td>
                </tr>
                @endforeach
                
                <!-- Summary Row -->
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="10" class="text-center">TOTAL</td>
                    <td class="text-right amount">TZS {{ number_format(array_sum(array_column($arrears_data, 'arrears_amount')), 2) }}</td>
                    <td colspan="3" class="text-center">{{ count($arrears_data) }} Loans</td>
                </tr>
            @else
                <tr>
                    <td colspan="14" class="text-center" style="padding: 20px; color: #28a745; font-weight: bold;">
                        No loans in arrears found. All loans are current with their payments.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>This report was generated on {{ $generated_date }} | System: Loan Management System</p>
        <p>Page 1 of 1</p>
    </div>
    </div>
</body>
</html>
