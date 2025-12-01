<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses Summary Report</title>
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
            grid-template-columns: repeat(4, 1fr);
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
        <div class="report-title">EXPENSES SUMMARY REPORT</div>
        <div class="report-info">
            Period: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }} |
            Reporting Type: {{ ucfirst($reportingType) }} |
            Generated: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3>Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Expenses</div>
                <div class="summary-value text-danger">{{ number_format($expensesData['summary']['total_expenses'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Transactions</div>
                <div class="summary-value">{{ number_format($expensesData['summary']['total_transactions']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Account Count</div>
                <div class="summary-value">{{ number_format($expensesData['summary']['account_count']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Avg per Transaction</div>
                <div class="summary-value">{{ number_format($expensesData['summary']['average_per_transaction'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Expenses Details -->
    <div class="expenses-section">
        <h3>Expenses Details</h3>
        @if($expensesData['filters']['group_by'] === 'group')
        <table>
            <thead>
                <tr>
                    <th>Account Group</th>
                    <th class="text-right">Total Debit</th>
                    <th class="text-right">Total Credit</th>
                    <th class="text-right">Net Amount</th>
                    @if(!empty($expensesData['comparative']))
                        @foreach($expensesData['comparative'] as $columnName => $compData)
                            <th class="text-right">{{ $columnName }} Amount</th>
                        @endforeach
                    @endif
                    <th class="text-center">Account Count</th>
                    <th class="text-center">Transaction Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expensesData['expenses'] as $expense)
                <tr>
                    <td>{{ $expense->group_name }}</td>
                    <td class="text-right">{{ number_format($expense->total_debit, 2) }}</td>
                    <td class="text-right">{{ number_format($expense->total_credit, 2) }}</td>
                    <td class="text-right">{{ number_format($expense->net_amount, 2) }}</td>
                    @if(!empty($expensesData['comparative']))
                        @foreach($expensesData['comparative'] as $columnName => $compData)
                            @php
                                $compGroup = collect($compData['expenses'])->firstWhere('group_name', $expense->group_name);
                                $compAmount = $compGroup ? $compGroup->net_amount : 0;
                            @endphp
                            <td class="text-right">{{ number_format($compAmount, 2) }}</td>
                        @endforeach
                    @endif
                    <td class="text-center">{{ $expense->account_count }}</td>
                    <td class="text-center">{{ $expense->transaction_count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th>Account Group</th>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                    @if(!empty($expensesData['comparative']))
                        @foreach($expensesData['comparative'] as $columnName => $compData)
                            <th class="text-right">{{ $columnName }} Amount</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($expensesData['expenses'] as $expense)
                <tr>
                    <td>{{ Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                    <td>{{ $expense->account_code }}</td>
                    <td>{{ $expense->account_name }}</td>
                    <td>{{ $expense->group_name }}</td>
                    <td>{{ Str::limit($expense->description, 30) }}</td>
                    <td class="text-right">{{ number_format($expense->amount, 2) }}</td>
                    @if(!empty($expensesData['comparative']))
                        @foreach($expensesData['comparative'] as $columnName => $compData)
                            @php
                                $compTransaction = collect($compData['expenses'])->firstWhere('transaction_id', $expense->transaction_id);
                                $compAmount = $compTransaction ? $compTransaction->amount : 0;
                            @endphp
                            <td class="text-right">{{ number_format($compAmount, 2) }}</td>
                        @endforeach
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- Additional Summary -->
    <div class="additional-summary">
        <h3>Additional Summary</h3>
        <table style="width: 50%;">
            <tr>
                <td><strong>Average per Account:</strong></td>
                <td class="text-right">{{ number_format($expensesData['summary']['average_per_account'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Transactions:</strong></td>
                <td class="text-right">{{ number_format($expensesData['summary']['total_transactions']) }}</td>
            </tr>
            <tr>
                <td><strong>Total Accounts:</strong></td>
                <td class="text-right">{{ number_format($expensesData['summary']['account_count']) }}</td>
            </tr>
            <tr>
                <td><strong>Total Expenses:</strong></td>
                <td class="text-right text-danger">{{ number_format($expensesData['summary']['total_expenses'], 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $company->name }}</p>
        <p>Report Period: {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>
</body>

</html>