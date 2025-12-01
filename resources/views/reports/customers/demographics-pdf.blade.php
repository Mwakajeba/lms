<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Demographics Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            line-height: 1.2;
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
            padding: 3px;
            text-align: left;
            font-size: 7px;
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
        <div class="report-title">CUSTOMER DEMOGRAPHICS REPORT</div>
        <div class="report-info">
            Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} | 
            Generated: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Filter Information -->
    <div class="filter-info">
        <strong>Report Filters:</strong><br>
        Branch: {{ $branchName }} | 
        Region: {{ $regionName }} | 
        District: {{ $districtName }} | 
        Gender: {{ $genderName }} | 
        Category: {{ $categoryName }} | 
        Age Group: {{ $ageGroupName }}
    </div>

    <!-- Summary Statistics -->
    <div class="summary-section">
        <div class="section-title">Summary Statistics</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Customers</div>
                <div class="summary-value text-primary">{{ number_format($demographicsData['statistics']['total_customers']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Average Age</div>
                <div class="summary-value text-info">{{ $demographicsData['statistics']['average_age'] }} years</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">With Loans</div>
                <div class="summary-value text-success">{{ number_format($demographicsData['statistics']['customers_with_loans']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">With Collateral</div>
                <div class="summary-value text-warning">{{ number_format($demographicsData['statistics']['customers_with_collateral']) }}</div>
            </div>
        </div>
    </div>

    <!-- Gender Distribution -->
    <div class="summary-section">
        <div class="section-title">Gender Distribution</div>
        <table>
            <thead>
                <tr>
                    <th>Gender</th>
                    <th class="text-center">Count</th>
                    <th class="text-center">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demographicsData['statistics']['gender_distribution'] as $gender => $data)
                    <tr>
                        <td>{{ ucfirst($gender) }}</td>
                        <td class="text-center">{{ number_format($data['count']) }}</td>
                        <td class="text-center">{{ $data['percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Age Group Distribution -->
    <div class="summary-section">
        <div class="section-title">Age Group Distribution</div>
        <table>
            <thead>
                <tr>
                    <th>Age Group</th>
                    <th class="text-center">Count</th>
                    <th class="text-center">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demographicsData['statistics']['age_group_distribution'] as $ageGroup => $data)
                    <tr>
                        <td>{{ $ageGroup }}</td>
                        <td class="text-center">{{ number_format($data['count']) }}</td>
                        <td class="text-center">{{ $data['percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Category Distribution -->
    <div class="summary-section">
        <div class="section-title">Category Distribution</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-center">Count</th>
                    <th class="text-center">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demographicsData['statistics']['category_distribution'] as $category => $data)
                    <tr>
                        <td>{{ ucfirst($category) }}</td>
                        <td class="text-center">{{ number_format($data['count']) }}</td>
                        <td class="text-center">{{ $data['percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Region Distribution -->
    <div class="summary-section">
        <div class="section-title">Region Distribution</div>
        <table>
            <thead>
                <tr>
                    <th>Region</th>
                    <th class="text-center">Count</th>
                    <th class="text-center">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demographicsData['statistics']['region_distribution'] as $region => $data)
                    <tr>
                        <td>{{ $region }}</td>
                        <td class="text-center">{{ number_format($data['count']) }}</td>
                        <td class="text-center">{{ $data['percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Branch Distribution -->
    <div class="summary-section">
        <div class="section-title">Branch Distribution</div>
        <table>
            <thead>
                <tr>
                    <th>Branch</th>
                    <th class="text-center">Count</th>
                    <th class="text-center">Percentage</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demographicsData['statistics']['branch_distribution'] as $branch => $data)
                    <tr>
                        <td>{{ $branch }}</td>
                        <td class="text-center">{{ number_format($data['count']) }}</td>
                        <td class="text-center">{{ $data['percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Monthly Registration Trends -->
    <div class="summary-section">
        <div class="section-title">Monthly Registration Trends</div>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-center">New Registrations</th>
                </tr>
            </thead>
            <tbody>
                @foreach($demographicsData['statistics']['monthly_registrations'] as $month => $count)
                    <tr>
                        <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}</td>
                        <td class="text-center">{{ number_format($count) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>This report was generated on {{ now()->format('d/m/Y H:i:s') }} by {{ $company->name }}</p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        <p>Branch: {{ $branchName }} | Region: {{ $regionName }} | District: {{ $districtName }} | Gender: {{ $genderName }} | Category: {{ $categoryName }} | Age Group: {{ $ageGroupName }}</p>
    </div>
</body>
</html>
