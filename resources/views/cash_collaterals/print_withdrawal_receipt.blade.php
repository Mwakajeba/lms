<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal Receipt - {{ $payment->reference_number ?? 'WTH-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            padding: 10px;
        }
        
        .receipt {
            max-width: 300px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            color: #d9534f;
        }
        
        .receipt-info {
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 2px;
        }
        
        .label {
            font-weight: bold;
        }
        
        .amount-section {
            text-align: center;
            border: 2px solid #d9534f;
            padding: 10px;
            margin: 15px 0;
            background-color: #f9f2f2;
        }
        
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #d9534f;
        }
        
        .footer {
            text-align: center;
            border-top: 2px solid #000;
            padding-top: 10px;
            margin-top: 15px;
            font-size: 10px;
        }
        
        .signature-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature {
            width: 45%;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 30px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            .receipt {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="company-name">{{ $company_name ?? 'Smart Finance' }}</div>
            <div>{{ $branch_name ?? 'Main Branch' }}</div>
            <div class="receipt-title">WITHDRAWAL RECEIPT</div>
        </div>
        
        <div class="receipt-info">
            <div class="info-row">
                <span class="label">Receipt No:</span>
                <span>{{ $payment->reference_number ?? 'WTH-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Date:</span>
                <span>{{ $payment->date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Time:</span>
                <span>{{ $payment->created_at->format('H:i:s') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Customer:</span>
                <span>{{ $payment->customer->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Deposit Type:</span>
                <span>{{ optional($collateral)->type->name ?? 'Cash Deposit' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Bank Account:</span>
                <span>{{ $payment->bankAccount->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Account No:</span>
                <span>{{ $payment->bankAccount->account_number ?? 'N/A' }}</span>
            </div>
        </div>
        
        <div class="amount-section">
            <div>Amount Withdrawn</div>
            <div class="amount">TSHS {{ number_format($payment->amount, 2) }}</div>
        </div>
        
        @if($payment->description)
        <div class="receipt-info">
            <div class="info-row">
                <span class="label">Notes:</span>
                <span>{{ $payment->description }}</span>
            </div>
        </div>
        @endif
        
        <div class="signature-section">
            <div class="signature">
                <div>Customer</div>
            </div>
            <div class="signature">
                <div>{{ $payment->user->name ?? 'N/A' }}</div>
                <div style="font-size: 10px;">Cashier</div>
            </div>
        </div>
        
        <div class="footer">
            <div>Thank you for your business!</div>
            <div>{{ now()->format('Y-m-d H:i:s') }}</div>
        </div>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
        
        // Close window after printing
        window.onafterprint = function() {
            window.close();
        }
    </script>
</body>
</html>
