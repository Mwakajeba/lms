<!DOCTYPE html>
<html>
<head>
    <title>Budget Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .summary { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .amount { text-align: right; }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        @php
        $logoData = null;
        if(isset($company) && !empty($company->logo)){
            $raw = ltrim($company->logo, '/');
            if (strpos($raw, 'storage/') === 0) {
                $raw = substr($raw, strlen('storage/'));
            }
            
            // Try multiple paths
            $paths = [
                public_path('storage/' . $raw),
                public_path($raw),
                storage_path('app/public/' . $raw),
                public_path('images/' . $raw)
            ];
            
            foreach($paths as $path) {
                if (file_exists($path)) {
                    $mime = mime_content_type($path) ?: 'image/png';
                    $logoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                    break;
                }
            }
        }
        @endphp
        @if($logoData)
            <img src="{{ $logoData }}" alt="{{ $company->name ?? 'Company' }} Logo" style="max-height: 70px; max-width: 200px; object-fit: contain; display:block; margin:0 auto 8px;"/>
        @else
            <!-- Debug: Company logo not found -->
            @if(isset($company))
                <p style="font-size: 10px; color: #666;">Debug: Company: {{ $company->name ?? 'N/A' }}, Logo: {{ $company->logo ?? 'N/A' }}</p>
            @else
                <p style="font-size: 10px; color: #666;">Debug: Company data not available</p>
            @endif
        @endif
        <h2>Budget vs Actual Report</h2>
        <p>Period: {{ $filters['date_from'] }} to {{ $filters['date_to'] }}</p>
        <p>Generated: {{ $generated_at }}</p>
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <p>Total Budgeted: {{ number_format($summary['total_budgeted'], 2) }}</p>
        <p>Total Actual: {{ number_format($summary['total_actual'], 2) }}</p>
        <p>Total Variance: <span class="{{ $summary['total_variance'] >= 0 ? 'positive' : 'negative' }}">{{ number_format($summary['total_variance'], 2) }}</span></p>
    </div>

    @if($items->count() > 0)
    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th>Account Class</th>
                <th>Account Group</th>
                <th>Budgeted</th>
                <th>Actual</th>
                <th>Variance</th>
                <th>Variance %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->account_code }}</td>
                <td>{{ $item->account_name }}</td>
                <td>{{ $item->account_class }}</td>
                <td>{{ $item->account_group }}</td>
                <td class="amount">{{ number_format($item->budgeted_amount, 2) }}</td>
                <td class="amount">{{ number_format($item->actual_amount, 2) }}</td>
                <td class="amount {{ $item->variance >= 0 ? 'positive' : 'negative' }}">{{ number_format($item->variance, 2) }}</td>
                <td class="amount {{ $item->variance_percentage >= 0 ? 'positive' : 'negative' }}">{{ $item->variance_percentage }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No data found for the selected filters.</p>
    @endif

    <div class="footer">
        <p>Generated on {{ $generated_at }} by {{ $company->name ?? 'System' }}</p>
    </div>
</body>
</html> 