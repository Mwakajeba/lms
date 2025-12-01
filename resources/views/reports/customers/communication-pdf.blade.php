<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Communication Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            line-height: 1.1;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-info {
            font-size: 9px;
            color: #666;
        }
        .summary-section {
            margin-bottom: 15px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px;
            margin-bottom: 10px;
        }
        .summary-item {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .summary-label {
            font-size: 7px;
            color: #666;
            margin-bottom: 2px;
        }
        .summary-value {
            font-size: 10px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 2px;
            text-align: left;
            font-size: 6px;
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
        .text-warning {
            color: #ffc107;
        }
        .text-info {
            color: #17a2b8;
        }
        .text-secondary {
            color: #6c757d;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 7px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .page-break {
            page-break-before: always;
        }
        .filter-info {
            background-color: #f8f9fa;
            padding: 6px;
            border-radius: 3px;
            margin-bottom: 15px;
            font-size: 8px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">CUSTOMER COMMUNICATION REPORT</div>
        <div class="report-info">
            Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} | 
            Generated: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Filter Information -->
    <div class="filter-info">
        <strong>Report Filters:</strong><br>
        Branch: {{ $branchName }} | 
        Customer: {{ $customerName }} | 
        Communication Type: {{ $communicationTypeName }} | 
        Status: {{ $statusName }}
    </div>

    <!-- Summary Statistics -->
    <div class="summary-section">
        <div class="section-title">Communication Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Communications</div>
                <div class="summary-value text-primary">{{ number_format($communicationData['summary']['total_communications']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Unique Customers</div>
                <div class="summary-value text-info">{{ number_format($communicationData['summary']['unique_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Amount</div>
                <div class="summary-value text-success">{{ number_format($communicationData['summary']['total_amount'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Avg per Customer</div>
                <div class="summary-value text-warning">{{ $communicationData['summary']['unique_customers'] > 0 ? number_format($communicationData['summary']['total_communications'] / $communicationData['summary']['unique_customers'], 1) : 0 }}</div>
            </div>
        </div>
    </div>

    <!-- Communication Type Distribution -->
    <div class="summary-section">
        <div class="section-title">Communication Type Distribution</div>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th class="text-center">Count</th>
                    <th class="text-center">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($communicationData['summary']['type_distribution'] as $type => $data)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                        <td class="text-center">{{ number_format($data['count']) }}</td>
                        <td class="text-center">{{ $data['percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Status Distribution -->
    <div class="summary-section">
        <div class="section-title">Status Distribution</div>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th class="text-center">Count</th>
                    <th class="text-center">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($communicationData['summary']['status_distribution'] as $status => $data)
                    <tr>
                        <td>{{ ucfirst($status) }}</td>
                        <td class="text-center">{{ number_format($data['count']) }}</td>
                        <td class="text-center">{{ $data['percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Communication Details -->
    <div class="communication-section">
        <div class="section-title">Communication Details</div>
        @if($communicationData['data']->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date & Time</th>
                        <th>Customer No</th>
                        <th>Customer Name</th>
                        <th>Branch</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                        <th>User</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($communicationData['data'] as $index => $communication)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $communication['date']->format('d/m/Y H:i') }}</td>
                            <td>{{ $communication['customer_no'] }}</td>
                            <td>{{ $communication['customer_name'] }}</td>
                            <td>{{ $communication['branch_name'] }}</td>
                            <td class="text-center">{{ $communication['type_label'] }}</td>
                            <td>{{ $communication['reference'] }}</td>
                            <td>{{ $communication['description'] }}</td>
                            <td class="text-right">
                                @if($communication['amount'])
                                    {{ number_format($communication['amount'], 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">{{ $communication['status_label'] }}</td>
                            <td>{{ $communication['user_name'] }}</td>
                            <td>{{ $communication['communication_method'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px; color: #666;">
                <p>No customer communication data found for the selected criteria.</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $company->name }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Branch: {{ $branchName }} | Customer: {{ $customerName }} | Type: {{ $communicationTypeName }} | Status: {{ $statusName }}</p>
    </div>
</body>
</html>
