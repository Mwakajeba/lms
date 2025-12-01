<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #{{ $billPurchase->reference }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            color: #333;
            font-size: 11px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-left {
            display: flex;
            align-items: center;
        }
        .logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            margin-right: 12px;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 2px;
        }
        .document-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .header-right {
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .voucher-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .info-section {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 1px solid #dc3545;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 11px;
            margin-bottom: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            padding: 4px 8px;
            background-color: #f8f9fa;
            border-radius: 3px;
            border-left: 3px solid #dc3545;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-row .info-label {
            flex: 0 0 35%;
            margin-bottom: 0;
            font-weight: bold;
            color: #495057;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .info-row .info-value {
            flex: 0 0 63%;
            margin-bottom: 0;
            text-align: right;
            font-size: 10px;
            font-weight: 500;
            color: #212529;
        }
        .amount-section {
            text-align: right;
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .amount-label {
            font-size: 12px;
            font-weight: bold;
            color: #dc3545;
        }
        .amount-value {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
        }
        .line-items {
            margin-bottom: 15px;
        }
        .line-items h3 {
            color: #dc3545;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            margin-bottom: 8px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            font-size: 10px;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .footer {
            margin-top: 15px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        .signature-box {
            text-align: center;
            width: 120px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 20px;
            padding-top: 2px;
        }
        .page-break {
            page-break-before: always;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
        .notes-section {
            margin-top: 15px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 3px solid #dc3545;
        }
        .notes-title {
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 4px;
            font-size: 10px;
            text-transform: uppercase;
        }
        .notes-content {
            font-size: 10px;
            color: #666;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @if($billPurchase->user->company->logo)
                <img src="{{ asset('storage/' . $billPurchase->user->company->logo) }}" alt="Company Logo" class="logo">
            @endif
            <div class="company-info">
                <div class="company-name">{{ $billPurchase->user->company->name ?? 'Company Name' }}</div>
                <div class="document-title">BILL PURCHASE</div>
            </div>
        </div>
        <div class="header-right">
            <div>Generated on: {{ now()->format('M d, Y \a\t g:i A') }}</div>
            <div>Page 1 of 1</div>
        </div>
    </div>

    <div class="voucher-info">
        <div class="info-section">
            <div class="section-title">Bill Details</div>
            <div class="info-row">
                <span class="info-label">Bill Reference</span>
                <span class="info-value">{{ $billPurchase->reference }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Bill Date</span>
                <span class="info-value">{{ $billPurchase->date->format('M d, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Due Date</span>
                <span class="info-value">{{ $billPurchase->due_date ? $billPurchase->due_date->format('M d, Y') : 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="status-badge status-{{ strtolower($billPurchase->status) }}">
                        {{ ucfirst($billPurchase->status) }}
                    </span>
                </span>
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">Contact Information</div>
            <div class="info-row">
                <span class="info-label">Supplier</span>
                <span class="info-value">{{ $billPurchase->supplier->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Branch</span>
                <span class="info-value">{{ $billPurchase->branch->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Credit Account</span>
                <span class="info-value">
                    {{ $billPurchase->creditAccount->account_code ?? 'N/A' }} - 
                    {{ $billPurchase->creditAccount->account_name ?? 'N/A' }}
                </span>
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">Status & Notes</div>
            <div class="info-row">
                <span class="info-label">Created By</span>
                <span class="info-value">{{ $billPurchase->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Amount</span>
                <span class="info-value">TZS {{ number_format($billPurchase->total_amount, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Paid Amount</span>
                <span class="info-value">TZS {{ number_format($billPurchase->paid ?? 0, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Balance</span>
                <span class="info-value">TZS {{ number_format($billPurchase->balance, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="amount-section">
        <div class="amount-label">Total Bill Amount</div>
        <div class="amount-value">TZS {{ number_format($billPurchase->total_amount, 2) }}</div>
    </div>

    <div class="line-items">
        <h3>Bill Line Items</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">#</th>
                    <th style="width: 50%;">Account</th>
                    <th style="width: 25%;">Description</th>
                    <th style="width: 17%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($billPurchase->billItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $item->debitAccount->account_code ?? 'N/A' }} - 
                            {{ $item->debitAccount->account_name ?? 'N/A' }}
                        </td>
                        <td>{{ $item->description ?: 'No description' }}</td>
                        <td style="text-align: right;">TZS {{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                    <td style="text-align: right;"><strong>TZS {{ number_format($billPurchase->total_amount, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($billPurchase->note)
        <div class="notes-section">
            <div class="notes-title">Notes</div>
            <div class="notes-content">{{ $billPurchase->note }}</div>
        </div>
    @endif

    <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">

    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="font-size: 9px; margin-top: 4px;">Prepared By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="font-size: 9px; margin-top: 4px;">Approved By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="font-size: 9px; margin-top: 4px;">Received By</div>
            </div>
        </div>
        
        <div style="text-align: center; font-size: 10px; color: #666; margin-top: 15px;">
            This is a computer generated document. No signature is required.
        </div>
    </div>
</body>
</html> 