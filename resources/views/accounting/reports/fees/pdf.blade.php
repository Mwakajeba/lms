<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-info {
            font-size: 12px;
            color: #666;
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
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
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

        .filter-info {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .logo-wrapper {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-wrapper img {
            max-height: 70px;
        }
    </style>
</head>

<body>
    @php
    $companyModel = isset($company) ? $company : (function_exists('current_company') ? current_company() : null);
    $logoPath = ($companyModel && !empty($companyModel->logo)) ? public_path('storage/' . $companyModel->logo) : null;
    @endphp
    @if($logoPath && file_exists($logoPath))
    <div class="logo-wrapper">
        <img src="{{ $logoPath }}" alt="Company Logo">
    </div>
    @endif
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">FEES REPORT</div>
        <div class="report-info">
            Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} |
            Generated: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Filter Information -->
    <div class="filter-info">
        <strong>Report Filters:</strong><br>
        Fee Type: {{ $feeName }} |
        Branch: {{ $branchName }}
    </div>



    <!-- Fees Details -->
    <div class="fees-section">
        <h3>Fees Transaction Details</h3>
        @if($feesData['data']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Fee Name</th>
                    <th>Chart Account</th>
                    <th>Account Code</th>
                    <th>Customer</th>
                    <th>Branch</th>
                    <th class="text-center">Nature</th>
                    <th class="text-right">Amount</th>
                    <th>Description</th>
                    <th>Reference ID</th>
                    <th>Transaction Type</th>
                </tr>
            </thead>
            <tbody>
                @foreach($feesData['data'] as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                    <td>{{ $item->fee_name }}</td>
                    <td>{{ $item->chart_account_name }}</td>
                    <td>{{ $item->account_code }}</td>
                    <td>{{ $item->customer_name ?? 'N/A' }}</td>
                    <td>{{ $item->branch_name ?? 'N/A' }}</td>
                    <td class="text-center">
                        <span class="{{ $item->nature === 'debit' ? 'text-danger' : 'text-success' }}">
                            {{ ucfirst($item->nature) }}
                        </span>
                    </td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                    <td>{{ Str::limit($item->description, 25) }}</td>
                    <td>{{ $item->reference_id }}</td>
                    <td>{{ $item->transaction_type }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="balance-row">
                    <td colspan="8"><strong>TOTAL BALANCE</strong></td>
                    <td class="text-right">
                        <strong class="{{ $feesData['summary']['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($feesData['summary']['balance'], 2) }}
                        </strong>
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
        @else
        <div style="text-align: center; padding: 20px; color: #666;">
            <p>No fee transactions found for the selected criteria.</p>
        </div>
        @endif
    </div>

    <!-- Additional Summary -->
    <div class="additional-summary">
        <h3>Additional Summary</h3>
        <table style="width: 60%;">
            <tr>
                <td><strong>Total Debit Amount:</strong></td>
                <td class="text-right text-danger">{{ number_format($feesData['summary']['total_debit'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Credit Amount:</strong></td>
                <td class="text-right text-success">{{ number_format($feesData['summary']['total_credit'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Net Balance (Credit - Debit):</strong></td>
                <td class="text-right text-primary">{{ number_format($feesData['summary']['balance'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Transactions:</strong></td>
                <td class="text-right">{{ number_format($feesData['summary']['total_transactions']) }}</td>
            </tr>
            <tr>
                <td><strong>Unique Fees:</strong></td>
                <td class="text-right">{{ number_format($feesData['summary']['unique_fees']) }}</td>
            </tr>
            <tr>
                <td><strong>Unique Customers:</strong></td>
                <td class="text-right">{{ number_format($feesData['summary']['unique_customers']) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $company->name }}</p>
        <p>Report Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Fee Type: {{ $feeName }} | Branch: {{ $branchName }}</p>
    </div>
</body>

</html>