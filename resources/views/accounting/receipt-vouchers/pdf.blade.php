<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt Voucher - RCP-{{ $receiptVoucher->id }}</title>
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
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .receipt-info {
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px 10px;
            border: 1px solid #ddd;
        }
        .info-table .label {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 30%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th,
        .items-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .items-table .amount {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            text-align: right;
        }
        .total-row {
            margin: 5px 0;
        }
        .total-label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }
        .total-amount {
            display: inline-block;
            width: 100px;
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .signature-section {
            margin-top: 30px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 20px 0 5px 0;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $invoice->company->name ?? config('app.name') }}</div>
        <div>{{ $invoice->branch->name ?? 'Main Branch' }}</div>
                <div class="document-title">RECEIPT VOUCHER</div>
    </div>

    <div class="receipt-info">
        <table class="info-table">
            <tr>
                <td class="label">Receipt Number:</td>
                <td>RCP-{{ $receiptVoucher->id }}</td>
                <td class="label">Date:</td>
                <td>{{ $receiptVoucher->date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">Invoice Number:</td>
                <td>{{ $invoice->invoice_number }}</td>
                <td class="label">Customer:</td>
                <td>{{ $invoice->customer->name ?? 'Walk-in Customer' }}</td>
            </tr>
            <tr>
                <td class="label">Payment Method:</td>
                <td>{{ $receiptVoucher->bankAccount ? $receiptVoucher->bankAccount->name : 'Cash' }}</td>
                <td class="label">Amount:</td>
                <td class="text-bold">TZS {{ number_format($receiptVoucher->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Description:</td>
                <td colspan="3">{{ $receiptVoucher->description ?? 'Payment for Invoice #' . $invoice->invoice_number }}</td>
            </tr>
        </table>
    </div>

    @if($receiptVoucher->receiptItems->count() > 0)
    <div class="items-section">
        <h3>Receipt Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Description</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receiptVoucher->receiptItems as $item)
                <tr>
                    <td>{{ $item->chartAccount->name ?? 'N/A' }}</td>
                    <td>{{ $item->description ?? 'N/A' }}</td>
                    <td class="amount">TZS {{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="totals">
        <div class="total-row">
            <span class="total-label">Total Amount:</span>
            <span class="total-amount text-bold">TZS {{ number_format($receiptVoucher->amount, 2) }}</span>
        </div>
    </div>

    <div class="footer">
        <div class="signature-section">
                <div class="signature-line"></div>
            <div class="text-center">Prepared by: {{ $receiptVoucher->user->name ?? 'System' }}</div>
        </div>
        
        <div style="margin-top: 20px;">
            <div class="text-center">
                <strong>Invoice Summary:</strong><br>
                Total Invoice: TZS {{ number_format($invoice->total_amount, 2) }}<br>
                Total Paid: TZS {{ number_format($invoice->paid_amount, 2) }}<br>
                Balance Due: TZS {{ number_format($invoice->balance_due, 2) }}
            </div>
        </div>
    </div>
</body>
</html>