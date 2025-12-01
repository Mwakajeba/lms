<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delinquency Report</title>
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
            background-color: #ffc107;
            color: black;
            font-weight: bold;
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .severity-low { color: #28a745; font-weight: bold; }
        .severity-medium { color: #ffc107; font-weight: bold; }
        .severity-high { color: #dc3545; font-weight: bold; }
        .severity-critical { color: #6c757d; font-weight: bold; }
        
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
        <div class="report-title">LOAN DELINQUENCY REPORT</div>
        <div class="report-date">Report Date: {{ now()->format('F d, Y') }} | As of: {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</div>
    </div>

    <!-- Filters Applied -->
    <div class="filters">
        <strong>Applied Filters:</strong>
        <div class="filter-row">
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}
            | <strong>Min. Days Delinquent:</strong> {{ $delinquencyDays }}+
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
                    <div class="summary-value">{{ number_format($delinquencyData['summary']['delinquent_loans']) }}</div>
                    <div class="summary-label">Delinquent Loans</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($delinquencyData['summary']['delinquency_rate'], 1) }}%</div>
                    <div class="summary-label">Delinquency Rate</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">TZS {{ number_format($delinquencyData['summary']['total_delinquent_amount'], 0) }}</div>
                    <div class="summary-label">Delinquent Amount</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($delinquencyData['summary']['current_loans']) }}</div>
                    <div class="summary-label">Current Loans</div>
                </div>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-value">{{ $delinquencyData['buckets']['1-30']['count'] }}</div>
                    <div class="summary-label">1-30 Days (TZS {{ number_format($delinquencyData['buckets']['1-30']['amount'], 0) }})</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $delinquencyData['buckets']['31-60']['count'] }}</div>
                    <div class="summary-label">31-60 Days (TZS {{ number_format($delinquencyData['buckets']['31-60']['amount'], 0) }})</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $delinquencyData['buckets']['61-90']['count'] }}</div>
                    <div class="summary-label">61-90 Days (TZS {{ number_format($delinquencyData['buckets']['61-90']['amount'], 0) }})</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ $delinquencyData['buckets']['180+']['count'] }}</div>
                    <div class="summary-label">180+ Days (TZS {{ number_format($delinquencyData['buckets']['180+']['amount'], 0) }})</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delinquent Loans Table -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 15%;">Customer</th>
                <th style="width: 10%;">Customer No</th>
                <th style="width: 10%;">Phone</th>
                <th style="width: 10%;">Branch</th>
                <th style="width: 10%;">Group</th>
                <th style="width: 10%;">Loan Officer</th>
                <th style="width: 10%;">Outstanding</th>
                <th style="width: 8%;">Days Overdue</th>
                <th style="width: 8%;">Bucket</th>
                <th style="width: 8%;">Severity</th>
            </tr>
        </thead>
        <tbody>
            @forelse($delinquencyData['loans'] as $loan)
            <tr>
                <td>{{ $loan['customer'] }}</td>
                <td class="text-center">{{ $loan['customer_no'] }}</td>
                <td>{{ $loan['phone'] }}</td>
                <td>{{ $loan['branch'] }}</td>
                <td>{{ $loan['group'] }}</td>
                <td>{{ $loan['loan_officer'] }}</td>
                <td class="text-right">{{ number_format($loan['outstanding_amount'], 0) }}</td>
                <td class="text-center">{{ $loan['days_in_arrears'] }}d</td>
                <td class="text-center">{{ $loan['delinquency_bucket'] }}</td>
                <td class="text-center">
                    <span class="severity-{{ str_replace(' ', '-', strtolower($loan['severity_level'])) }}">
                        {{ $loan['severity_level'] }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">No delinquent loans found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
        Generated on {{ now()->format('F d, Y \a\t H:i:s') }} | {{ $company->name ?? 'SmartFinance' }} - Delinquency Report
    </div>
    
    <div class="footer">
        <div>
            Generated on {{ now()->format('F d, Y g:i A') }}
            @if(isset($company->name))
                | {{ $company->name }}
            @endif
        </div>
    </div>
</body>
</html>
