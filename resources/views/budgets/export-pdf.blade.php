<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Export - {{ $budget->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .budget-info {
            margin-bottom: 30px;
        }
        .budget-info h2 {
            color: #007bff;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        .info-item {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            min-width: 120px;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .budget-lines {
            margin-top: 30px;
        }
        .budget-lines h2 {
            color: #007bff;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            color: #495057;
        }
        td {
            border: 1px solid #ddd;
            padding: 10px 8px;
            text-align: left;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .category-revenue { color: #28a745; }
        .category-expense { color: #dc3545; }
        .category-capital { color: #ffc107; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BUDGET EXPORT</h1>
        <p>Generated on {{ now()->format('d M Y, H:i') }}</p>
    </div>

    <div class="budget-info">
        <h2>Budget Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Budget Name:</span>
                <span class="info-value">{{ $budget->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Year:</span>
                <span class="info-value">{{ $budget->year }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Description:</span>
                <span class="info-value">{{ $budget->description ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Created By:</span>
                <span class="info-value">{{ $budget->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Branch:</span>
                <span class="info-value">{{ $budget->branch->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Created Date:</span>
                <span class="info-value">{{ $budget->created_at->format('d M Y, H:i') }}</span>
            </div>
        </div>
    </div>

    <div class="budget-lines">
        <h2>Budget Lines</h2>
        <table>
            <thead>
                <tr>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th>Amount (TZS)</th>
                    <th>Category</th>
                </tr>
            </thead>
            <tbody>
                @php $totalAmount = 0; @endphp
                @foreach($budget->budgetLines as $line)
                    @php $totalAmount += $line->amount; @endphp
                    <tr>
                        <td>{{ $line->account->account_code }}</td>
                        <td>{{ $line->account->account_name }}</td>
                        <td class="amount">{{ number_format($line->amount, 2) }}</td>
                        <td class="category-{{ strtolower(str_replace(' ', '-', $line->category)) }}">
                            {{ $line->category }}
                        </td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2"><strong>TOTAL</strong></td>
                    <td class="amount"><strong>{{ number_format($totalAmount, 2) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $budget->company->name ?? 'System' }}</p>
        <p>Budget Period: {{ $budget->year }}</p>
    </div>
</body>
</html> 