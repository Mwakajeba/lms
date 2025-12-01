<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
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
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-info {
            font-size: 10px;
            color: #666;
        }
        .summary-section {
            margin-bottom: 15px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }
        .summary-item {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .summary-label {
            font-size: 8px;
            color: #666;
            margin-bottom: 3px;
        }
        .summary-value {
            font-size: 12px;
            font-weight: bold;
        }
        .summary-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 8px;
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
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .page-break {
            page-break-before: always;
        }
        .filter-info {
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 3px;
            margin-bottom: 15px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name }}</div>
        <div class="report-title">CUSTOMER LIST REPORT</div>
        <div class="report-info">
            Generated: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Filter Information -->
    <div class="filter-info">
        <strong>Report Filters:</strong><br>
        Branch: {{ $branchName }} | 
        Region: {{ $regionName }} | 
        District: {{ $districtName }} | 
        Category: {{ $category === 'all' ? 'All' : ucfirst($category) }} | 
        Gender: {{ $sex === 'all' ? 'All' : ucfirst($sex) }} | 
        Has Loans: {{ $hasLoans === 'all' ? 'All' : ucfirst($hasLoans) }} | 
        Has Collateral: {{ $hasCollateral === 'all' ? 'All' : ucfirst($hasCollateral) }}
        @if($registrationDateFrom || $registrationDateTo)
            <br>Registration Period: {{ $registrationDateFrom ? \Carbon\Carbon::parse($registrationDateFrom)->format('d/m/Y') : 'Start' }} to {{ $registrationDateTo ? \Carbon\Carbon::parse($registrationDateTo)->format('d/m/Y') : 'End' }}
        @endif
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3>Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Customers</div>
                <div class="summary-value text-primary">{{ number_format($customersData['summary']['total_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Male Customers</div>
                <div class="summary-value text-info">{{ number_format($customersData['summary']['male_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Female Customers</div>
                <div class="summary-value text-warning">{{ number_format($customersData['summary']['female_customers']) }}</div>
            </div>
        </div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">With Loans</div>
                <div class="summary-value text-success">{{ number_format($customersData['summary']['customers_with_loans']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">With Collateral</div>
                <div class="summary-value text-primary">{{ number_format($customersData['summary']['customers_with_collateral']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Loans</div>
                <div class="summary-value text-danger">{{ number_format($customersData['summary']['total_loans']) }}</div>
            </div>
        </div>
        <div class="summary-grid-2">
            <div class="summary-item">
                <div class="summary-label">Total Loan Amount</div>
                <div class="summary-value text-success">{{ number_format($customersData['summary']['total_loan_amount'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Collateral Amount</div>
                <div class="summary-value text-info">{{ number_format($customersData['summary']['total_collateral_amount'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Customers Details -->
    <div class="customers-section">
        <h3>Customer Details</h3>
        @if($customersData['data']->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer No</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Region</th>
                        <th>District</th>
                        <th>Branch</th>
                        <th>Category</th>
                        <th>Gender</th>
                        <th>Date Registered</th>
                        <th class="text-center">Has Loans</th>
                        <th class="text-center">Loan Count</th>
                        <th class="text-right">Loan Amount</th>
                        <th class="text-center">Has Collateral</th>
                        <th class="text-right">Collateral Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customersData['data'] as $index => $customer)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $customer->customerNo }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->phone1 }}</td>
                            <td>{{ $customer->region->name ?? 'N/A' }}</td>
                            <td>{{ $customer->district->name ?? 'N/A' }}</td>
                            <td>{{ $customer->branch->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="{{ $customer->category === 'individual' ? 'text-primary' : 'text-success' }}">
                                    {{ ucfirst($customer->category ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="{{ $customer->sex === 'male' ? 'text-info' : 'text-warning' }}">
                                    {{ ucfirst($customer->sex) }}
                                </span>
                            </td>
                            <td>{{ $customer->dateRegistered ? $customer->dateRegistered->format('d/m/Y') : 'N/A' }}</td>
                            <td class="text-center">
                                <span class="{{ $customer->loans->count() > 0 ? 'text-success' : 'text-secondary' }}">
                                    {{ $customer->loans->count() > 0 ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="text-center">{{ $customer->loans->count() }}</td>
                            <td class="text-right">{{ number_format($customer->loans->sum('amount'), 2) }}</td>
                            <td class="text-center">
                                <span class="{{ $customer->has_cash_collateral ? 'text-success' : 'text-secondary' }}">
                                    {{ $customer->has_cash_collateral ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="text-right">{{ number_format($customer->collaterals->sum('amount'), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 20px; color: #666;">
                <p>No customers found for the selected criteria.</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $company->name }}</p>
        <p>Branch: {{ $branchName }} | Region: {{ $regionName }} | District: {{ $districtName }}</p>
    </div>
</body>
</html>
