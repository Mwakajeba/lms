<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Portfolio Report</title>
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
            background-color: #333;
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
        
        .status-active { color: #28a745; font-weight: bold; }
        .status-completed { color: #007bff; font-weight: bold; }
        .status-defaulted { color: #dc3545; font-weight: bold; }
        
        .arrears-current { color: #28a745; font-weight: bold; }
        .arrears-low { color: #ffc107; font-weight: bold; }
        .arrears-medium { color: #fd7e14; font-weight: bold; }
        .arrears-high { color: #dc3545; font-weight: bold; }
        .arrears-critical { color: #6c757d; font-weight: bold; }
        
        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .filter-row {
            margin-bottom: 5px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #000;
            padding-top: 5px;
            background: white;
        }
        
        .footer .page-number:after {
            content: "Page " counter(page) " of " counter(pages);
        }
        
        @page {
            margin: 1cm 1cm 1.5cm 1cm;
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 9px;
                color: #666;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">LOAN PORTFOLIO REPORT</div>
        <div class="report-date">Report Date: {{ now()->format('F d, Y') }} | As of: {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</div>
    </div>

    <!-- Filters Applied -->
    <div class="filters">
        <strong>Applied Filters:</strong>
        <div class="filter-row">
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}
            @if($branchId)
                | <strong>Branch:</strong> {{ $branches->find($branchId)->name ?? 'N/A' }}
            @endif
            @if($groupId)
                | <strong>Group:</strong> {{ $groups->find($groupId)->name ?? 'N/A' }}
            @endif
            @if($loanOfficerId)
                | <strong>Loan Officer:</strong> {{ $loanOfficers->find($loanOfficerId)->name ?? 'N/A' }}
            @endif
            @if($status !== 'all')
                | <strong>Status:</strong> 
                @if($status === 'active_completed')
                    Active & Completed
                @else
                    {{ ucfirst($status) }}
                @endif
            @endif
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($portfolioData['summary']['total_loans']) }}</div>
                    <div class="summary-label">Total Loans</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">TZS {{ number_format($portfolioData['summary']['total_disbursed'], 0) }}</div>
                    <div class="summary-label">Total Disbursed</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">TZS {{ number_format($portfolioData['summary']['total_outstanding'], 0) }}</div>
                    <div class="summary-label">Total Outstanding</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($portfolioData['summary']['par_ratio'], 2) }}%</div>
                    <div class="summary-label">Portfolio at Risk</div>
                </div>
            </div>
        </div>

        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($portfolioData['summary']['active_loans']) }}</div>
                    <div class="summary-label">Active Loans</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($portfolioData['summary']['completed_loans']) }}</div>
                    <div class="summary-label">Completed Loans</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($portfolioData['summary']['defaulted_loans']) }}</div>
                    <div class="summary-label">Defaulted Loans</div>
                </div>
                <div class="summary-cell">
                    <div class="summary-value">{{ number_format($portfolioData['summary']['overall_repayment_rate'], 2) }}%</div>
                    <div class="summary-label">Repayment Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Portfolio Details Table -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 12%;">Customer</th>
                <th style="width: 8%;">Customer No</th>
                <th style="width: 10%;">Branch</th>
                <th style="width: 10%;">Group</th>
                <th style="width: 10%;">Loan Officer</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 12%;">Disbursed Amount</th>
                <th style="width: 12%;">Outstanding</th>
                <th style="width: 8%;">Repayment Rate</th>
                <th style="width: 5%;">Arrears</th>
                <th style="width: 8%;">Disbursed Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($portfolioData['loans'] as $loan)
            <tr>
                <td>{{ $loan['customer'] }}</td>
                <td class="text-center">{{ $loan['customer_no'] }}</td>
                <td>{{ $loan['branch'] }}</td>
                <td>{{ $loan['group'] }}</td>
                <td>{{ $loan['loan_officer'] }}</td>
                <td class="text-center">
                    <span class="status-{{ $loan['status'] }}">
                        {{ ucfirst($loan['status']) }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($loan['disbursed_amount'], 0) }}</td>
                <td class="text-right">{{ number_format($loan['outstanding_amount'], 0) }}</td>
                <td class="text-center">{{ number_format($loan['repayment_rate'], 1) }}%</td>
                <td class="text-center">
                    <span class="
                        @if($loan['days_in_arrears'] == 0) arrears-current
                        @elseif($loan['days_in_arrears'] <= 30) arrears-low
                        @elseif($loan['days_in_arrears'] <= 60) arrears-medium
                        @elseif($loan['days_in_arrears'] <= 90) arrears-high
                        @else arrears-critical
                        @endif">
                        {{ $loan['days_in_arrears'] }}d
                    </span>
                </td>
                <td class="text-center">{{ $loan['disbursed_date'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">No loans found for the selected criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
        Generated on {{ now()->format('F d, Y \a\t H:i:s') }} | {{ $company->name ?? 'SmartFinance' }} - Loan Portfolio Report
    </div>
    
    <div class="footer">
        <div>
            Generated on {{ now()->format('F d, Y g:i A') }}
            @if(isset($company->name))
                | {{ $company->name }}
            @endif
        </div>
    </div>
     <!-- Add page numbers -->
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(
                270,  // X position (center)
                800,  // Y position (bottom of A4)
                "Page {PAGE_NUM} of {PAGE_COUNT}", 
                $fontMetrics->get_font("Helvetica", "normal"), 
                10, 
                [0,0,0]
            );
        }
    </script>
</body>
</html>
