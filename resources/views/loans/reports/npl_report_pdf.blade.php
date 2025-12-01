<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Non Performing Loan Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .header-left {
            display: flex;
            align-items: center;
        }
        .logo {
            max-height: 80px;
            max-width: 120px;
            margin-right: 20px;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.3;
        }
        .header-right {
            text-align: right;
            font-size: 11px;
            color: #666;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
            text-align: center;
            margin-bottom: 5px;
        }
        .report-date {
            font-size: 14px;
            color: #666;
            text-align: center;
        }
        .summary-section {
            margin-bottom: 30px;
        }
        .summary-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-card {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            background-color: #f8f9fa;
        }
        .summary-card.danger {
            background-color: #f8d7da;
            border-color: #dc3545;
        }
        .summary-card.warning {
            background-color: #fff3cd;
            border-color: #ffc107;
        }
        .summary-card.info {
            background-color: #d1ecf1;
            border-color: #17a2b8;
        }
        .summary-card.dark {
            background-color: #d1d3d4;
            border-color: #343a40;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-label {
            font-size: 12px;
            color: #666;
        }
        .filters-section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        .filters-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        .filter-label {
            font-weight: bold;
            color: #333;
        }
        .filter-value {
            color: #666;
        }
        .table-section {
            margin-top: 20px;
        }
        .table-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .badge-danger {
            background-color: #dc3545;
            color: #fff;
        }
        .badge-dark {
            background-color: #343a40;
            color: #fff;
        }
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .page-break {
            page-break-before: always;
        }
        .classification-legend {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .legend-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .legend-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        .legend-color {
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-right: 5px;
            border-radius: 2px;
        }
        .legend-standard { background-color: #28a745; }
        .legend-substandard { background-color: #ffc107; }
        .legend-doubtful { background-color: #fd7e14; }
        .legend-loss { background-color: #dc3545; }
    </style>
</head>
<body>
    @php
        $companyModel = isset($company) ? $company : \App\Models\Company::first();
        $logoPath = ($companyModel && !empty($companyModel->logo)) ? public_path('storage/' . $companyModel->logo) : null;
    @endphp
    
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                @if($logoPath && file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Company Logo" class="logo">
                @endif
                <div class="company-info">
                    <div class="company-name">{{ $companyModel->name ?? 'SmartFinance' }}</div>
                    <div class="company-details">
                        @if($companyModel->address)
                            {{ $companyModel->address }}<br>
                        @endif
                        @if($companyModel->phone)
                            Tel: {{ $companyModel->phone }}<br>
                        @endif
                        @if($companyModel->email)
                            Email: {{ $companyModel->email }}<br>
                        @endif
                        @if($companyModel->company_id)
                            Company ID: {{ $companyModel->company_id }}<br>
                        @endif
                        @if($companyModel->license_number)
                            License: {{ $companyModel->license_number }}
                        @endif
                    </div>
                </div>
            </div>
            <div class="header-right">
                <div><strong>Report Generated:</strong></div>
                <div>{{ date('d-m-Y H:i:s') }}</div>
                @if(isset($asOfDate))
                    <div style="margin-top: 10px;"><strong>As of Date:</strong></div>
                    <div>{{ \Carbon\Carbon::parse($asOfDate)->format('d-m-Y') }}</div>
                @endif
            </div>
        </div>
        <div class="report-title">NON PERFORMING LOAN REPORT</div>
        <div class="report-date">Risk Assessment & Provision Analysis</div>
    </div>

    @if(isset($nplData) && count($nplData) > 0)
        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-title">Summary</div>
            <div class="summary-grid">
                <div class="summary-card danger">
                    <div class="summary-value">{{ number_format(count($nplData)) }}</div>
                    <div class="summary-label">NPL Loans</div>
                </div>
                <div class="summary-card warning">
                    <div class="summary-value">TZS {{ number_format(collect($nplData)->sum('outstanding'), 2) }}</div>
                    <div class="summary-label">NPL Amount</div>
                </div>
                <div class="summary-card info">
                    <div class="summary-value">{{ number_format(collect($nplData)->avg('dpd'), 1) }}</div>
                    <div class="summary-label">Avg DPD</div>
                </div>
                <div class="summary-card dark">
                    <div class="summary-value">TZS {{ number_format(collect($nplData)->sum('provision_amount'), 2) }}</div>
                    <div class="summary-label">Total Provision</div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <div class="filters-title">Report Filters</div>
            <div class="filter-item">
                <span class="filter-label">As of Date:</span>
                <span class="filter-value">{{ $asOfDate ?? 'N/A' }}</span>
            </div>
            @if(isset($branchId) && $branchId)
                <div class="filter-item">
                    <span class="filter-label">Branch:</span>
                    <span class="filter-value">{{ \App\Models\Branch::find($branchId)->name ?? 'N/A' }}</span>
                </div>
            @endif
            @if(isset($loanOfficerId) && $loanOfficerId)
                <div class="filter-item">
                    <span class="filter-label">Loan Officer:</span>
                    <span class="filter-value">{{ \App\Models\User::find($loanOfficerId)->name ?? 'N/A' }}</span>
                </div>
            @endif
        </div>

        <!-- Classification Legend -->
        <div class="classification-legend">
            <div class="legend-title">Loan Classification & Provision Guidelines</div>
            <div class="legend-item">
                <span class="legend-color legend-standard"></span>
                <strong>Standard:</strong> 0-90 days past due (0% provision)
            </div>
            <div class="legend-item">
                <span class="legend-color legend-substandard"></span>
                <strong>Substandard:</strong> 91-180 days past due (20% provision)
            </div>
            <div class="legend-item">
                <span class="legend-color legend-doubtful"></span>
                <strong>Doubtful:</strong> 181-360 days past due (50% provision)
            </div>
            <div class="legend-item">
                <span class="legend-color legend-loss"></span>
                <strong>Loss:</strong> 360+ days past due (100% provision)
            </div>
        </div>

        <!-- NPL Details Table -->
        <div class="table-section">
            <div class="table-title">NPL Loans Details</div>
            <table>
                <thead>
                    <tr>
                        <th>Date Of</th>
                        <th>Branch</th>
                        <th>Loan Officer</th>
                        <th>Loan ID</th>
                        <th>Borrower</th>
                        <th class="text-end">Outstanding (TZS)</th>
                        <th class="text-center">DPD</th>
                        <th>Classification</th>
                        <th>Provision %</th>
                        <th class="text-end">Provision (TZS)</th>
                        <th>Collateral</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nplData as $row)
                        <tr>
                            <td>{{ $row['date_of'] }}</td>
                            <td>{{ $row['branch'] }}</td>
                            <td>{{ $row['loan_officer'] }}</td>
                            <td>{{ $row['loan_id'] }}</td>
                            <td>{{ $row['borrower'] }}</td>
                            <td class="text-end">{{ number_format($row['outstanding']) }}</td>
                            <td class="text-center">
                                <span class="badge 
                                    @if($row['dpd'] <= 30) badge-warning
                                    @elseif($row['dpd'] <= 60) badge-warning
                                    @elseif($row['dpd'] <= 90) badge-danger
                                    @else badge-dark
                                    @endif">
                                    {{ $row['dpd'] }}
                                </span>
                            </td>
                            <td>{{ $row['classification'] }}</td>
                            <td>{{ $row['provision_percent'] }}</td>
                            <td class="text-end">{{ number_format($row['provision_amount']) }}</td>
                            <td>{{ $row['collateral'] }}</td>
                            <td>{{ $row['status'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="no-data">
            No NPL loans found for the selected criteria.
        </div>
    @endif

    <div class="footer">
        <p><strong>{{ $companyModel->name ?? 'SmartFinance' }} - Non Performing Loan Report</strong></p>
        <p>This report was generated automatically by SmartFinance System on {{ date('d-m-Y H:i:s') }}</p>
        <p>For any queries regarding this report, please contact the system administrator</p>
        @if($companyModel->email)
            <p>Email: {{ $companyModel->email }} | 
            @if($companyModel->phone)
                Phone: {{ $companyModel->phone }}
            @endif
            </p>
        @endif
        <p style="margin-top: 10px; font-size: 9px; color: #999;">
            This report contains confidential information and is intended for internal use only.
        </p>
    </div>
</body>
</html>
