<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expected vs Collected Report</title>
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
            color: #000000;
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
        
        .status-excellent { background-color: #d1edff; color: #0066cc; }
        .status-good { background-color: #d4edda; color: #155724; }
        .status-fair { background-color: #fff3cd; color: #856404; }
        .status-poor { background-color: #f8d7da; color: #721c24; }
        .status-critical { background-color: #f5c6cb; color: #721c24; }
        
        .variance-positive { color: #28a745; font-weight: bold; }
        .variance-negative { color: #dc3545; font-weight: bold; }
        .variance-zero { color: #6c757d; }
        
        .footer {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 8px;
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
                        <h2>{{ $company->address }}</h2>
                    @endif
                    @if($company->phone)
                        <h2>Phone: {{ $company->phone }}</h2>
                    @endif
                    @if($company->email)
                        <h2>Email: {{ $company->email }}</h2>
                    @endif
                @else
                @endif
            </div>
            
            <h2 class="report-title">EXPECTED VS COLLECTED REPORT</h2>
        </div>

        <!-- Report Information -->
        <div class="report-info">
            <table>
                <tr>
                    <td class="label">Report Date:</td>
                    <td>{{ $generated_date }}</td>
                    <td class="label">Period:</td>
                    <td>{{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Branch:</td>
                    <td>{{ $branch_name }}</td>
                    <td class="label">Group:</td>
                    <td>{{ $group_name }}</td>
                </tr>
                <tr>
                    <td class="label">Loan Officer:</td>
                    <td>{{ $loan_officer_name }}</td>
                    <td class="label">Total Records:</td>
                    <td><strong>{{ count($report_data) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Summary Section -->
        @if(count($report_data) > 0)
        <div class="summary-section">
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Expected</h3>
                    <div class="value">TZS {{ number_format(array_sum(array_column($report_data, 'expected_total')), 2) }}</div>
                </div>
                <div class="summary-card">
                    <h3>Total Collected</h3>
                    <div class="value">TZS {{ number_format(array_sum(array_column($report_data, 'collected_total')), 2) }}</div>
                </div>
                <div class="summary-card">
                    <h3>Variance</h3>
                    <div class="value">TZS {{ number_format(array_sum(array_column($report_data, 'variance')), 2) }}</div>
                </div>
                <div class="summary-card">
                    <h3>Collection Rate</h3>
                    <div class="value">
                        {{ array_sum(array_column($report_data, 'expected_total')) > 0 ? 
                           number_format((array_sum(array_column($report_data, 'collected_total')) / array_sum(array_column($report_data, 'expected_total'))) * 100, 1) : '0' }}%
                    </div>
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
                    <th style="width: 8%;">Expected</th>
                    <th style="width: 8%;">Collected</th>
                    <th style="width: 7%;">Variance</th>
                    <th style="width: 5%;">Rate</th>
                    <th style="width: 6%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @if(count($report_data) > 0)
                    @foreach($report_data as $index => $row)
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
                        <td class="text-right">{{ number_format($row['expected_total'], 2) }}</td>
                        <td class="text-right">{{ number_format($row['collected_total'], 2) }}</td>
                        <td class="text-right 
                            @if($row['variance'] > 0) variance-positive 
                            @elseif($row['variance'] < 0) variance-negative 
                            @else variance-zero @endif">
                            {{ number_format($row['variance'], 2) }}
                        </td>
                        <td class="text-center">{{ $row['collection_rate'] }}%</td>
                        <td class="text-center">
                            <span class="status-{{ strtolower($row['collection_status']) }}">
                                {{ $row['collection_status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    
                    <!-- Summary Row -->
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="9" class="text-center">TOTALS</td>
                        <td class="text-right">TZS {{ number_format(array_sum(array_column($report_data, 'expected_total')), 2) }}</td>
                        <td class="text-right">TZS {{ number_format(array_sum(array_column($report_data, 'collected_total')), 2) }}</td>
                        <td class="text-right">TZS {{ number_format(array_sum(array_column($report_data, 'variance')), 2) }}</td>
                        <td class="text-center">
                            {{ array_sum(array_column($report_data, 'expected_total')) > 0 ? 
                               number_format((array_sum(array_column($report_data, 'collected_total')) / array_sum(array_column($report_data, 'expected_total'))) * 100, 1) : '0' }}%
                        </td>
                        <td class="text-center">{{ count($report_data) }} Loans</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="14" class="text-center" style="padding: 20px; color: #28a745; font-weight: bold;">
                            No data found for the selected period and filters.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>This report was generated on {{ $generated_date }} | System: Loan Management System</p>
            <p>Period: {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
        </div>
    </div>
</body>
</html>
