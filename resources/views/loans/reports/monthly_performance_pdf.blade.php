<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Loan Performance</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f2f2f2; text-align: center; }
        td { text-align: right; }
        td:first-child, th:first-child { text-align: left; }
        h3 { margin: 0 0 10px 0; }
    </style>
</head>
<body>
    <table style="width:100%; border:0; margin-bottom:8px;">
        <tr>
            <td style="width:70px; border:0;">
                @if(isset($company) && !empty($company->logo))
                    <img src="{{ public_path('storage/'.$company->logo) }}" alt="Logo" style="height:60px; width:auto;">
                @endif
            </td>
            <td style="border:0;">
                <h3 style="margin:0;">{{ $company->name ?? 'Company' }}</h3>
                <p style="margin:0;">TIN: {{ $company->tin ?? '-' }} | Phone: {{ $company->phone ?? '-' }}</p>
                <p style="margin:0;">Address: {{ $company->address ?? '-' }}</p>
            </td>
            <td style="text-align:right; border:0;">
                <strong>Monthly Loan Performance</strong><br>
                <span>Period: {{ ($startDate && $endDate) ? ($startDate.' - '.$endDate) : 'All Time' }}</span>
            </td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>MONTH</th>
                <th>LOAN GIVEN</th>
                <th>INTEREST</th>
                <th>TOTAL LOAN + INTEREST</th>
                <th>TOTAL AMOUNT COLLECTED</th>
                <th>OUTSTANDING</th>
                <th>ACTUAL INTEREST COLLECTED</th>
                <th>PERFORMANCE %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
            <tr>
                <td>{{ $r['month'] }}</td>
                <td>{{ number_format($r['loan_given'], 2) }}</td>
                <td>{{ number_format($r['interest'], 2) }}</td>
                <td>{{ number_format($r['total_loan'], 2) }}</td>
                <td>{{ number_format($r['collected'], 2) }}</td>
                <td>{{ number_format($r['outstanding'], 2) }}</td>
                <td>{{ number_format($r['actual_interest_collected'], 2) }}</td>
                <td>{{ number_format($r['performance'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>


