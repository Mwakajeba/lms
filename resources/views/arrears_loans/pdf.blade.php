<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loans in Arrears (30+ days)</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <h2>Loans in Arrears (30+ days)</h2>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Customer No</th>
                <th>Loan No</th>
                <th>Total Loan Outstanding</th>
                <th>Total Amount in Arrears</th>
                <th>Days in Arrears</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $loan)
            <tr>
                <td>{{ $loan->customer_name }}</td>
                <td>{{ $loan->customer_no }}</td>
                <td>{{ $loan->loan_no }}</td>
                <td>{{ number_format($loan->total_outstanding, 2) }}</td>
                <td>{{ number_format($loan->amount_in_arrears, 2) }}</td>
                <td>{{ $loan->days_in_arrears }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
