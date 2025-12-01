<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Voucher #{{ $paymentVoucher->reference }}</title>
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
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .notes-section {
            margin-bottom: 10px;
        }
        .notes-section h3 {
            color: #dc3545;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
            margin-bottom: 6px;
            font-size: 11px;
        }
        .notes-content {
            background-color: #f8f9fa;
            padding: 6px;
            border-radius: 3px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @php
                $company = $paymentVoucher->user->company ?? null;
                $logoPath = $company && !empty($company->logo) ? public_path('storage/' . $company->logo) : null;
            @endphp
            @if($logoPath && file_exists($logoPath))
                <img src="file://{{ $logoPath }}" alt="Company Logo" class="logo">
            @endif
            <div class="company-info">
                <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
                <div class="document-title">PAYMENT VOUCHER</div>
            </div>
        </div>
        <div class="header-right">
            <div>Generated on {{ date('F d, Y \a\t g:i A') }}</div>
            <div>Voucher #{{ $paymentVoucher->reference }}</div>
        </div>
    </div>

    <div class="voucher-info">
        <div class="info-section">
            <div class="section-title">Voucher Details</div>
            <div class="info-row">
                <div class="info-label">Reference</div>
                <div class="info-value">{{ $paymentVoucher->reference }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Date</div>
                <div class="info-value">{{ $paymentVoucher->formatted_date }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Bank Account</div>
                <div class="info-value">
                    {{ $paymentVoucher->bankAccount->name ?? 'N/A' }}<br>
                    <small>{{ $paymentVoucher->bankAccount->account_number ?? 'N/A' }}</small>
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="section-title">Contact Information</div>
            <div class="info-row">
                <div class="info-label">Customer</div>
                <div class="info-value">
                    @if($paymentVoucher->customer)
                        {{ $paymentVoucher->customer->name ?? 'N/A' }}<br>
                        <small>{{ $paymentVoucher->customer->customerNo ?? 'N/A' }}</small>
                    @else
                        <span style="color: #999;">No customer selected</span>
                    @endif
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Branch</div>
                <div class="info-value">{{ $paymentVoucher->branch->name ?? 'N/A' }}</div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Created By</div>
                <div class="info-value">{{ $paymentVoucher->user->name ?? 'N/A' }}</div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="section-title">Notes</div>
            
            <div class="info-row">
                <div class="info-label">Description</div>
                <div class="info-value">
                    {{ $paymentVoucher->description ?: 'No description provided' }}
                </div>
            </div>
        </div>
    </div>

    <div class="amount-section">
        <div class="amount-label">Total Amount</div>
        <div class="amount-value">TZS {{ number_format($paymentVoucher->amount, 2) }}</div>
    </div>

    <div class="line-items">
        <h3>Payment Details</h3>
        <table>
            <thead>
                <tr>
                    <th width="8%">#</th>
                    <th width="50%">Account</th>
                    <th width="25%">Description</th>
                    <th width="17%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentVoucher->paymentItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->chartAccount->account_name ?? 'N/A' }}</strong><br>
                            <small>{{ $item->chartAccount->account_code ?? 'N/A' }}</small>
                        </td>
                        <td>{{ $item->description ?: 'No description' }}</td>
                        <td style="text-align: right;">TZS {{ number_format($item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">
                            No line items found
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                    <td style="text-align: right;"><strong>TZS {{ number_format($paymentVoucher->amount, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($paymentVoucher->notes)
        <div class="notes-section">
            <h3>Notes</h3>
            <div class="notes-content">
                {{ $paymentVoucher->notes }}
            </div>
        </div>
    @endif

    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="margin-top: 5px; font-size: 12px;">Prepared By</div>
                <div style="margin-top: 2px; font-size: 10px; color: #666;">
                    {{ $paymentVoucher->user->name ?? 'N/A' }}
                </div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="margin-top: 5px; font-size: 12px;">Approved By</div>
                <div style="margin-top: 2px; font-size: 10px; color: #666;">
                    @if($paymentVoucher->approved && $paymentVoucher->approvedBy)
                        {{ $paymentVoucher->approvedBy->name }}
                    @elseif($paymentVoucher->approved)
                        {{ $paymentVoucher->user->name ?? 'N/A' }}
                    @else
                        <span style="color: #999;">Pending Approval</span>
                    @endif
                </div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line"></div>
                <div style="margin-top: 5px; font-size: 12px;">Received By</div>
                <div style="margin-top: 2px; font-size: 10px; color: #666;">
                    @if($paymentVoucher->payee_type === 'customer' && $paymentVoucher->customer)
                        {{ $paymentVoucher->customer->name }}
                    @elseif($paymentVoucher->payee_type === 'supplier' && $paymentVoucher->supplier)
                        {{ $paymentVoucher->supplier->name }}
                    @elseif($paymentVoucher->payee_type === 'other')
                        {{ $paymentVoucher->payee_name ?? 'N/A' }}
                    @else
                        <span style="color: #999;">N/A</span>
                    @endif
                </div>
            </div>
        </div>
        
        <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
            <p>This is a computer generated document. No signature is required.</p>
            <p>Payment Voucher #{{ $paymentVoucher->reference }} | Generated on {{ date('F d, Y \a\t g:i A') }}</p>
        </div>
    </div>
</body>
</html>