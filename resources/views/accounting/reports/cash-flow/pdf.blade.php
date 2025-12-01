<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cash Flow Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .report-date {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .report-details {
            font-size: 10px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .cash-flow-header {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }

        .category-header {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        .positive {
            color: #28a745;
            font-weight: bold;
        }

        .negative {
            color: #dc3545;
            font-weight: bold;
        }

        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .grand-total-row {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        .summary-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid #007bff;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .summary-title {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 15px;
            text-align: center;
            border-bottom: 1px solid #007bff;
            padding-bottom: 10px;
        }

        .summary-grid {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .summary-item {
            flex: 1;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
        }

        .summary-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .summary-value.positive {
            color: #28a745;
        }

        .summary-value.negative {
            color: #dc3545;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
            margin: 20px 0 10px 0;
            padding: 8px 12px;
            background-color: #e3f2fd;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }

        .category-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .category-title {
            background-color: #007bff;
            color: white;
            padding: 12px 15px;
            font-weight: bold;
            font-size: 13px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .category-total {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
        }

        .transaction-row:hover {
            background-color: #f8f9fa;
        }

        .nature-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .nature-credit {
            background-color: #d4edda;
            color: #155724;
        }

        .nature-debit {
            background-color: #f8d7da;
            color: #721c24;
        }

        .account-info {
            line-height: 1.3;
        }

        .account-name {
            font-weight: bold;
            color: #333;
        }

        .account-code {
            font-size: 10px;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .no-data {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .no-data h4 {
            margin-bottom: 10px;
            color: #999;
        }

        .no-data p {
            color: #bbb;
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
    <!-- Report Header -->
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'Company Name' }}</div>
        <div class="report-title">CASH FLOW STATEMENT</div>
        <div class="report-date">For the period from {{ \Carbon\Carbon::parse($fromDate)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('F d, Y') }}</div>
        @php
            $showBranch = isset($cashFlowData['filters']['branch_id']);
        @endphp
        @if($showBranch)
        <div class="report-details">Branch: {{ $branchName ?? 'All Branches' }}</div>
        @endif
        <div class="report-details">
            Generated on {{ now()->format('F d, Y \a\t g:i A') }}
        </div>
    </div>


    <!-- Cash Flow by Category -->
    @if(count($cashFlowData['grouped_data']) > 0)
    <div class="section-title">DETAILED CASH FLOWS BY ACTIVITY</div>

    @foreach($cashFlowData['grouped_data'] as $categoryName => $transactions)
    <div class="category-section">
        <div class="category-title">
            <span>{{ $categoryName }}</span>
            <span class="category-total">
                Net Flow: {{ $cashFlowData['category_totals'][$categoryName]['net_change'] >= 0 ? '+' : '' }}{{ number_format($cashFlowData['category_totals'][$categoryName]['net_change'], 2) }}
            </span>
        </div>

        <table>
            <thead>
                <tr class="cash-flow-header">
                    <th>Date</th>
                    <th>Account Details</th>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Cash Impact</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr class="transaction-row">
                    <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</td>
                    <td class="account-info">
                        <div class="account-name">{{ $transaction['account_name'] }}</div>
                        <div class="account-code">{{ $transaction['account_code'] }}</div>
                    </td>
                    <td>{{ $transaction['description'] ?: 'No description provided' }}</td>
                    <td class="text-end">{{ number_format($transaction['amount'], 2) }}</td>
                    <td class="text-end {{ $transaction['impact'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $transaction['impact'] >= 0 ? '+' : '' }}{{ number_format($transaction['impact'], 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"><strong>Total for {{ $categoryName }}</strong></td>
                    <td class="text-end">
                        <strong>
                            {{ number_format($cashFlowData['category_totals'][$categoryName]['credit_total'], 2) }} /
                            {{ number_format($cashFlowData['category_totals'][$categoryName]['debit_total'], 2) }}
                        </strong>
                    </td>
                    <td class="text-end">
                        <strong class="{{ $cashFlowData['category_totals'][$categoryName]['net_change'] >= 0 ? 'positive' : 'negative' }}">
                            {{ $cashFlowData['category_totals'][$categoryName]['net_change'] >= 0 ? '+' : '' }}{{ number_format($cashFlowData['category_totals'][$categoryName]['net_change'], 2) }}
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endforeach
    @else
    <div class="no-data">
        <h4>No Cash Flow Data Found</h4>
        <p>No cash flow transactions were recorded for the selected period and filters.</p>
    </div>
    @endif

    <!-- Category Summary -->
    @if(count($cashFlowData['grouped_data']) > 0)
    <div style="margin-top: 40px;">
        <div class="section-title">CASH FLOW ACTIVITY SUMMARY</div>

        <table>
            <thead>
                <tr class="cash-flow-header">
                    <th>Cash Flow Activity</th>
                    <th class="text-end">Cash Inflows</th>
                    <th class="text-end">Cash Outflows</th>
                    <th class="text-end">Net Flow</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cashFlowData['category_totals'] as $categoryName => $totals)
                <tr>
                    <td><strong>{{ $categoryName }}</strong></td>
                    <td class="text-end">{{ number_format($totals['credit_total'], 2) }}</td>
                    <td class="text-end">{{ number_format($totals['debit_total'], 2) }}</td>
                    <td class="text-end {{ $totals['net_change'] >= 0 ? 'positive' : 'negative' }}">
                        {{ $totals['net_change'] >= 0 ? '+' : '' }}{{ number_format($totals['net_change'], 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total-row">
                    <td><strong>GRAND TOTAL</strong></td>
                    <td class="text-end"><strong>{{ number_format(collect($cashFlowData['category_totals'])->sum('credit_total'), 2) }}</strong></td>
                    <td class="text-end"><strong>{{ number_format(collect($cashFlowData['category_totals'])->sum('debit_total'), 2) }}</strong></td>
                    <td class="text-end"><strong>{{ $cashFlowData['overall_total'] >= 0 ? '+' : '' }}{{ number_format($cashFlowData['overall_total'], 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>This is a computer generated document. No signature is required.</p>
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
    </div>
</body>

</html>