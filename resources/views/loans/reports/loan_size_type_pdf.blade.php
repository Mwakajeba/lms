<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Size Type Report</title>
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
                @if(!empty($company->logo))
                    <img src="{{ public_path('storage/'.$company->logo) }}" alt="Logo" style="height:60px; width:auto;">
                @endif
            </td>
            <td style="border:0;">
                <h3 style="margin:0;">{{ $company->name ?? 'Company' }}</h3>
                <p style="margin:0;">TIN: {{ $company->tin ?? '-' }} | Phone: {{ $company->phone ?? '-' }}</p>
                <p style="margin:0;">Address: {{ $company->address ?? '-' }}</p>
            </td>
            <td style="text-align:right; border:0;">
                <strong>Loan Size Type Report</strong><br>
                <span>Period: {{ ($startDate && $endDate) ? ($startDate.' - '.$endDate) : 'All Time' }}</span>
            </td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>LOAN SIZE TYPE</th>
                <th>NO. OF LOAN</th>
                <th>LOAN AMOUNT</th>
                <th>INTEREST</th>
                <th>TOTAL LOAN</th>
                <th>TOTAL LOAN OUTSTANDING</th>
                <th>NO. OF LOANS IN ARREARS</th>
                <th>TOTAL ARREARS AMOUNT</th>
                <th>NO. OF LOANS IN DELAYED</th>
                <th>DELAYED AMOUNT</th>
                <th>OUTSTANDING IN DELAYED</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
            <tr>
                <td>{{ $r['label'] }}</td>
                <td>{{ number_format($r['count']) }}</td>
                <td>{{ number_format($r['loan_amount'], 2) }}</td>
                <td>{{ number_format($r['interest'], 2) }}</td>
                <td>{{ number_format($r['total_loan'], 2) }}</td>
                <td>{{ number_format($r['total_outstanding'], 2) }}</td>
                <td>{{ number_format($r['arrears_count']) }}</td>
                <td>{{ number_format($r['arrears_amount'], 2) }}</td>
                <td>{{ number_format($r['delayed_count']) }}</td>
                <td>{{ number_format($r['delayed_amount'], 2) }}</td>
                <td>{{ number_format($r['outstanding_in_delayed'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>GRAND TOTAL</th>
                <th>{{ number_format($grand['count']) }}</th>
                <th>{{ number_format($grand['loan_amount'], 2) }}</th>
                <th>{{ number_format($grand['interest'], 2) }}</th>
                <th>{{ number_format($grand['total_loan'], 2) }}</th>
                <th>{{ number_format($grand['total_outstanding'], 2) }}</th>
                <th>{{ number_format($grand['arrears_count']) }}</th>
                <th>{{ number_format($grand['arrears_amount'], 2) }}</th>
                <th>{{ number_format($grand['delayed_count']) }}</th>
                <th>{{ number_format($grand['delayed_amount'], 2) }}</th>
                <th>{{ number_format($grand['outstanding_in_delayed'], 2) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>


