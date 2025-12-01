<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penalties Report</title>
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
            margin-bottom: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .summary-item {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            background-color: #f9f9f9;
        }

        .summary-label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
        }

        .summary-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        th,
        td {
            border: 1px solid #e6e6e6;
            padding: 8px;
            font-size: 10px;
        }

        th {
            background-color: #f7f7f7;
            font-weight: bold;
            text-align: center;
        }

        tbody tr:nth-child(even) {
            background-color: #fafafa;
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

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        .balance-row {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .badges {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            background: #f8f9fa;
            border: 1px solid #e6e6e6;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
        }

        .filter-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 11px;
        }
 
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
                <div style="margin-top: 10px;"><strong>Period:</strong></div>
                <div>{{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</div>
            </div>
        </div>
        <div class="report-title">PENALTIES REPORT</div>
        <div class="report-date">Financial Penalties & Charges Analysis</div>
    </div>

 

    <!-- Filter Information -->
    <div class="filter-info">
        <table style="width:100%; border: 0; margin-bottom: 0;">
            <tr>
                <td style="border:0; padding: 2px 0;"><strong>Penalty:</strong> <span class="badges">{{ $penaltyName }}</span></td>
                <td style="border:0; padding: 2px 0;"><strong>Account Type:</strong> <span class="badges">{{ $penaltyTypeName }}</span></td>
                <td style="border:0; padding: 2px 0;"><strong>Branch:</strong> <span class="badges">{{ $branchName }}</span></td>
            </tr>
        </table>
    </div>


    <!-- Penalties Details -->
    <div class="penalties-section">
        <div class="section-title">Penalties Transaction Details</div>
        @if($penaltiesData['data']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="text-right">Amount</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penaltiesData['data'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                    <td>{{ $item->customer_name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    <td>{{ $item->description }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="balance-row">
                    <td colspan="4" class="text-right"><strong>TOTAL BALANCE</strong></td>
                    <td class="text-right">
                        <strong class="{{ $penaltiesData['summary']['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($penaltiesData['summary']['balance'], 2) }}
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>
        @else
        <div style="text-align: center; padding: 20px; color: #666;">
            <p>No penalty transactions found for the selected criteria.</p>
        </div>
        @endif
    </div>



    <div class="footer">
        <p><strong>{{ $companyModel->name ?? 'SmartFinance' }} - Penalties Report</strong></p>
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