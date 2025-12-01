<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Reconciliation - {{ $bankReconciliation->bankAccount->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
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
        .report-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .info-value {
            flex: 1;
        }
        .balances-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .balance-box {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: center;
            flex: 1;
            margin: 0 5px;
        }
        .balance-value {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .balance-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .balanced { color: #28a745; }
        .unbalanced { color: #dc3545; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-draft { background-color: #6c757d; color: white; }
        .status-in_progress { background-color: #ffc107; color: black; }
        .status-completed { background-color: #28a745; color: white; }
        .status-cancelled { background-color: #dc3545; color: white; }
        .nature-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .nature-debit { background-color: #dc3545; color: white; }
        .nature-credit { background-color: #28a745; color: white; }
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
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .reconciled-item {
            background-color: #f8f9fa;
        }
        .unreconciled-item {
            background-color: #fff3cd;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">Bank Reconciliation Report</div>
        <div class="report-subtitle">{{ $bankReconciliation->bankAccount->name }} - {{ $bankReconciliation->reconciliation_date->format('M d, Y') }}</div>
    </div>

    <!-- Reconciliation Information -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Bank Account:</span>
            <span class="info-value">{{ $bankReconciliation->bankAccount->name }} ({{ $bankReconciliation->bankAccount->account_number }})</span>
        </div>
        <div class="info-row">
            <span class="info-label">Reconciliation Date:</span>
            <span class="info-value">{{ $bankReconciliation->reconciliation_date->format('M d, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Period:</span>
            <span class="info-value">{{ $bankReconciliation->start_date->format('M d, Y') }} - {{ $bankReconciliation->end_date->format('M d, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                <span class="status-badge status-{{ $bankReconciliation->status }}">
                    {{ ucfirst(str_replace('_', ' ', $bankReconciliation->status)) }}
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Created By:</span>
            <span class="info-value">{{ $bankReconciliation->user->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Branch:</span>
            <span class="info-value">{{ $bankReconciliation->branch->name }}</span>
        </div>
    </div>

    <!-- Balance Summary -->
    <div class="balances-section">
        <div class="balance-box">
            <div class="balance-value">{{ number_format($bankReconciliation->bank_statement_balance, 2) }}</div>
            <div class="balance-label">Bank Statement Balance</div>
        </div>
        <div class="balance-box">
            <div class="balance-value">{{ number_format($bankReconciliation->book_balance, 2) }}</div>
            <div class="balance-label">Book Balance</div>
        </div>
        <div class="balance-box">
            <div class="balance-value {{ $bankReconciliation->difference == 0 ? 'balanced' : 'unbalanced' }}">
                {{ number_format($bankReconciliation->difference, 2) }}
            </div>
            <div class="balance-label">Difference</div>
        </div>
        <div class="balance-box">
            <div class="balance-value {{ $bankReconciliation->isBalanced() ? 'balanced' : 'unbalanced' }}">
                {{ $bankReconciliation->isBalanced() ? 'Balanced' : 'Unbalanced' }}
            </div>
            <div class="balance-label">Status</div>
        </div>
    </div>

    <!-- Bank Statement Items -->
    @if($unreconciledBankItems->count() > 0)
    <div class="section-title">Unreconciled Bank Statement Items</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th class="text-center">Nature</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unreconciledBankItems as $item)
            <tr class="unreconciled-item">
                <td>{{ $item->transaction_date->format('M d, Y') }}</td>
                <td>{{ $item->reference ?? 'N/A' }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-center">
                    <span class="nature-badge nature-{{ $item->nature }}">
                        {{ strtoupper($item->nature) }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Book Entry Items -->
    @if($unreconciledBookItems->count() > 0)
    <div class="section-title">Unreconciled Book Entry Items</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th class="text-center">Nature</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unreconciledBookItems as $item)
            <tr class="unreconciled-item">
                <td>{{ $item->transaction_date->format('M d, Y') }}</td>
                <td>{{ $item->reference ?? 'N/A' }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-center">
                    <span class="nature-badge nature-{{ $item->nature }}">
                        {{ strtoupper($item->nature) }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Reconciled Items -->
    @if($reconciledItems->count() > 0)
    <div class="page-break"></div>
    <div class="section-title">Reconciled Items</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th class="text-center">Type</th>
                <th class="text-center">Nature</th>
                <th class="text-right">Amount</th>
                <th>Matched With</th>
                <th>Reconciled By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reconciledItems as $item)
            <tr class="reconciled-item">
                <td>{{ $item->transaction_date->format('M d, Y') }}</td>
                <td>{{ $item->reference ?? 'N/A' }}</td>
                <td>{{ $item->description }}</td>
                <td class="text-center">
                    @if($item->is_bank_statement_item)
                        <span class="status-badge status-completed">Bank</span>
                    @else
                        <span class="status-badge status-draft">Book</span>
                    @endif
                </td>
                <td class="text-center">
                    <span class="nature-badge nature-{{ $item->nature }}">
                        {{ strtoupper($item->nature) }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                <td>
                    @if($item->matchedWithItem)
                        {{ $item->matchedWithItem->description }}
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($item->reconciledBy)
                        {{ $item->reconciledBy->name }}<br>
                        <small>{{ $item->reconciled_at->format('M d, Y H:i') }}</small>
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Summary -->
    <div class="page-break"></div>
    <div class="section-title">Reconciliation Summary</div>
    <table>
        <thead>
            <tr>
                <th>Item Type</th>
                <th class="text-center">Count</th>
                <th class="text-right">Total Amount</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Bank Statement Items</td>
                <td class="text-center">{{ $unreconciledBankItems->count() + $reconciledItems->where('is_bank_statement_item', true)->count() }}</td>
                <td class="text-right">{{ number_format($unreconciledBankItems->sum('amount') + $reconciledItems->where('is_bank_statement_item', true)->sum('amount'), 2) }}</td>
                <td class="text-center">
                    @if($unreconciledBankItems->count() > 0)
                        <span class="status-badge status-in_progress">Unreconciled</span>
                    @else
                        <span class="status-badge status-completed">All Reconciled</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Book Entry Items</td>
                <td class="text-center">{{ $unreconciledBookItems->count() + $reconciledItems->where('is_book_entry', true)->count() }}</td>
                <td class="text-right">{{ number_format($unreconciledBookItems->sum('amount') + $reconciledItems->where('is_book_entry', true)->sum('amount'), 2) }}</td>
                <td class="text-center">
                    @if($unreconciledBookItems->count() > 0)
                        <span class="status-badge status-in_progress">Unreconciled</span>
                    @else
                        <span class="status-badge status-completed">All Reconciled</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Reconciled Items</td>
                <td class="text-center">{{ $reconciledItems->count() }}</td>
                <td class="text-right">{{ number_format($reconciledItems->sum('amount'), 2) }}</td>
                <td class="text-center">
                    <span class="status-badge status-completed">Reconciled</span>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Notes -->
    @if($bankReconciliation->notes || $bankReconciliation->bank_statement_notes)
    <div class="section-title">Notes</div>
    @if($bankReconciliation->notes)
    <div style="margin-bottom: 15px;">
        <strong>General Notes:</strong><br>
        {{ $bankReconciliation->notes }}
    </div>
    @endif
    @if($bankReconciliation->bank_statement_notes)
    <div style="margin-bottom: 15px;">
        <strong>Bank Statement Notes:</strong><br>
        {{ $bankReconciliation->bank_statement_notes }}
    </div>
    @endif
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This reconciliation report was generated by {{ $user->name }} on {{ $generated_at->format('M d, Y \a\t H:i:s') }}</p>
        <p>SmartFinance - Bank Reconciliation Report</p>
    </div>
</body>
</html> 