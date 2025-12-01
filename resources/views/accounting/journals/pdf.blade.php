<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal Entry #{{ $journal->reference }}</title>
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
            border-bottom: 2px solid #007bff;
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
            color: #007bff;
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
        .journal-info {
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
            color: #007bff;
            margin-bottom: 6px;
            padding-bottom: 3px;
            border-bottom: 1px solid #007bff;
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
            border-left: 3px solid #007bff;
        }
        .info-row:last-child {
            margin-bottom: 0;
        }
        .info-row .info-label {
            margin-bottom: 0;
            color: #333;
        }
        .info-row .info-value {
            margin-bottom: 0;
            font-weight: bold;
        }
        .journal-items {
            margin-bottom: 15px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }
        .items-table th {
            background-color: #007bff;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .debit-amount {
            color: #28a745;
            font-weight: bold;
        }
        .credit-amount {
            color: #dc3545;
            font-weight: bold;
        }
        .totals-section {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
        }
        .totals-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid #dee2e6;
            padding-top: 5px;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        .signature-section {
            margin-top: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .signature-box {
            text-align: center;
            padding: 10px;
            border-top: 1px solid #dee2e6;
        }
        .signature-line {
            width: 150px;
            height: 1px;
            background-color: #333;
            margin: 20px auto 5px;
        }
        .signature-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        .disclaimer {
            margin-top: 15px;
            padding: 10px;
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            font-size: 9px;
            color: #856404;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            @if(isset($journal->user->company) && $journal->user->company->logo)
                <img src="{{ asset('storage/' . $journal->user->company->logo) }}" alt="Company Logo" class="logo">
            @endif
            <div class="company-info">
                <div class="company-name">{{ $journal->user->company->name ?? 'Company Name' }}</div>
                <div class="document-title">JOURNAL ENTRY</div>
            </div>
        </div>
        <div class="header-right">
            <div>Reference: {{ $journal->reference }}</div>
            <div>Date: {{ $journal->date ? $journal->date->format('M d, Y') : 'N/A' }}</div>
            <div>Generated: {{ now()->format('M d, Y H:i') }}</div>
        </div>
    </div>

    <!-- Journal Information -->
    <div class="journal-info">
        <div class="info-section">
            <div class="section-title">Journal Details</div>
            <div class="info-row">
                <span class="info-label">Reference</span>
                <span class="info-value">{{ $journal->reference }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date</span>
                <span class="info-value">{{ $journal->date ? $journal->date->format('M d, Y') : 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Branch</span>
                <span class="info-value">{{ $journal->branch->name ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">Created By</div>
            <div class="info-row">
                <span class="info-label">User</span>
                <span class="info-value">{{ $journal->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Created</span>
                <span class="info-value">{{ $journal->created_at ? $journal->created_at->format('M d, Y \a\t g:i A') : 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Updated</span>
                <span class="info-value">{{ $journal->updated_at ? $journal->updated_at->format('M d, Y \a\t g:i A') : 'N/A' }}</span>
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">Summary</div>
            <div class="info-row">
                <span class="info-label">Total Debit</span>
                <span class="info-value debit-amount">TZS {{ number_format($journal->debit_total, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Credit</span>
                <span class="info-value credit-amount">TZS {{ number_format($journal->credit_total, 2) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Balance</span>
                <span class="info-value {{ $journal->balance == 0 ? 'debit-amount' : 'credit-amount' }}">
                    TZS {{ number_format($journal->balance, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Description -->
    @if($journal->description)
    <div class="info-section" style="margin-bottom: 15px;">
        <div class="section-title">Description</div>
        <div class="info-value">{{ $journal->description }}</div>
    </div>
    @endif

    <!-- Journal Items -->
    <div class="journal-items">
        <div class="section-title">Journal Entries</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Account</th>
                    <th style="width: 15%;">Nature</th>
                    <th style="width: 20%;">Amount</th>
                    <th style="width: 20%;">Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($journal->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->chartAccount->account_code ?? 'N/A' }}</strong><br>
                        <small>{{ $item->chartAccount->account_name ?? 'N/A' }}</small>
                    </td>
                    <td>
                        <span class="badge {{ $item->nature == 'debit' ? 'bg-success' : 'bg-danger' }}" 
                              style="background-color: {{ $item->nature == 'debit' ? '#28a745' : '#dc3545' }}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 9px;">
                            {{ ucfirst($item->nature) }}
                        </span>
                    </td>
                    <td class="{{ $item->nature == 'debit' ? 'debit-amount' : 'credit-amount' }}">
                        TZS {{ number_format($item->amount, 2) }}
                    </td>
                    <td>{{ $item->description ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Prepared By</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Approved By</div>
        </div>
    </div>

    <!-- Disclaimer -->
    <div class="disclaimer">
        This is a computer generated document. No signature is required.
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }} by {{ $journal->user->name ?? 'System' }}</p>
    </div>
</body>
</html> 