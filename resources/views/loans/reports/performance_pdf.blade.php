<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Performance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            line-height: 1.4;
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
        
        .report-date {
            font-size: 12px;
            color: #666;
        }
        
        .summary-section {
            margin-bottom: 20px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .summary-row {
            display: table-row;
        }
        
        .summary-cell {
            display: table-cell;
            width: 25%;
            padding: 8px;
            text-align: center;
            border: 2px solid #000;
            background-color: #f8f9fa;
        }
        
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        
        .summary-label {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border: 2px solid #000;
        }
        
        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        
        .table th {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .grade-excellent { color: #28a745; font-weight: bold; }
        .grade-good { color: #007bff; font-weight: bold; }
        .grade-fair { color: #ffc107; font-weight: bold; }
        .grade-poor { color: #fd7e14; font-weight: bold; }
        .grade-critical { color: #dc3545; font-weight: bold; }
        
        .risk-low { color: #28a745; font-weight: bold; }
        .risk-medium { color: #ffc107; font-weight: bold; }
        .risk-high { color: #dc3545; font-weight: bold; }
        .risk-critical { color: #6c757d; font-weight: bold; }
        
        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .filter-row {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">LOAN PERFORMANCE REPORT</div>
        <div class="report-date">Report Date: {{ now()->format('F d, Y') }} | Period: {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}</div>
    </div>

    <!-- Filters Applied -->
    <div class="filters">
        <strong>Applied Filters:</strong>
        <div class="filter-row">
            <strong>Period:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('F d, Y') }}
            @if($branchId)
                | <strong>Branch:</strong> {{ $branches->find($branchId)->name ?? 'N/A' }}
            @endif
            @if($groupId)
                | <strong>Group:</strong> {{ $groups->find($groupId)->name ?? 'N/A' }}
            @endif
            @if($loanOfficerId)
                | <strong>Loan Officer:</strong> {{ $loanOfficers->find($loanOfficerId)->name ?? 'N/A' }}
            @endif
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($performanceData['summary']['on_time_payment_rate'], 1) }}%</div>
                    <div class="summary-label">On-Time Payment Rate</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($performanceData['summary']['overall_repayment_rate'], 1) }}%</div>
                    <div class="summary-label">Overall Repayment Rate</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($performanceData['summary']['arrears_rate'], 1) }}%</div>
                    <div class="summary-label">Arrears Rate</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($performanceData['summary']['average_days_in_arrears'], 0) }}</div>
                    <div class="summary-label">Avg Days in Arrears</div>
                </div>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($performanceData['summary']['total_loans']) }}</div>
                    <div class="summary-label">Total Active Loans</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($performanceData['summary']['on_time_payments']) }}</div>
                    <div class="summary-label">On-Time Payments</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($performanceData['summary']['late_payments']) }}</div>
                    <div class="summary-label">Late Payments</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">TZS {{ number_format($performanceData['summary']['periodic_repayments'], 0) }}</div>
                    <div class="summary-label">Period Collections</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Details Table -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">Customer</th>
                <th style="width: 10%;">Customer No</th>
                <th style="width: 12%;">Branch</th>
                <th style="width: 12%;">Group</th>
                <th style="width: 12%;">Loan Officer</th>
                <th style="width: 12%;">Outstanding</th>
                <th style="width: 8%;">Repayment Rate</th>
                <th style="width: 6%;">Arrears</th>
                <th style="width: 8%;">Grade</th>
                <th style="width: 8%;">Risk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($performanceData['loans'] as $loan)
            <tr>
                <td>{{ $loan['customer'] }}</td>
                <td class="text-center">{{ $loan['customer_no'] }}</td>
                <td>{{ $loan['branch'] }}</td>
                <td>{{ $loan['group'] }}</td>
                <td>{{ $loan['loan_officer'] }}</td>
                <td class="text-right">{{ number_format($loan['outstanding_amount'], 0) }}</td>
                <td class="text-center">{{ number_format($loan['repayment_rate'], 1) }}%</td>
                <td class="text-center">{{ $loan['days_in_arrears'] }}d</td>
                <td class="text-center">
                    <span class="grade-{{ strtolower($loan['performance_grade']) }}">
                        {{ $loan['performance_grade'] }}
                    </span>
                </td>
                <td class="text-center">
                    <span class="risk-{{ str_replace(' ', '-', strtolower($loan['risk_category'])) }}">
                        {{ $loan['risk_category'] }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">No loans found for the selected criteria.</td>
            </tr>
            @endforelse
