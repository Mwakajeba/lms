<table>
    <thead>
        <tr>
            <th colspan="12" style="font-size: 16px; font-weight: bold; text-align: center; background-color: #28a745; color: white;">
                LOAN PERFORMANCE REPORT
            </th>
        </tr>
        <tr>
            <th colspan="12" style="font-size: 12px; text-align: center; background-color: #E7E6E6;">
                Period: {{ \Carbon\Carbon::parse($performanceData['summary']['from_date'] ?? now()->subMonth())->format('F d, Y') }} to {{ \Carbon\Carbon::parse($performanceData['summary']['to_date'] ?? now())->format('F d, Y') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th colspan="12" style="font-weight: bold; background-color: #D9D9D9;">PERFORMANCE SUMMARY</th>
        </tr>
        <tr>
            <th style="background-color: #E7E6E6; font-weight: bold;">On-Time Payment Rate</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Overall Repayment Rate</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Arrears Rate</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Avg Days in Arrears</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Total Active Loans</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">On-Time Payments</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Late Payments</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Period Collections</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <td>{{ number_format($performanceData['summary']['on_time_payment_rate'], 2) }}%</td>
            <td>{{ number_format($performanceData['summary']['overall_repayment_rate'], 2) }}%</td>
            <td>{{ number_format($performanceData['summary']['arrears_rate'], 2) }}%</td>
            <td>{{ number_format($performanceData['summary']['average_days_in_arrears'], 1) }}</td>
            <td>{{ number_format($performanceData['summary']['total_loans']) }}</td>
            <td>{{ number_format($performanceData['summary']['on_time_payments']) }}</td>
            <td>{{ number_format($performanceData['summary']['late_payments']) }}</td>
            <td>{{ number_format($performanceData['summary']['periodic_repayments'], 2) }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr></tr>
        <tr>
            <th colspan="12" style="font-weight: bold; background-color: #D9D9D9;">PERFORMANCE DETAILS</th>
        </tr>
        <tr>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Customer</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Customer No</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Branch</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Group</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Loan Officer</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Disbursed Amount</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Outstanding Amount</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Total Paid</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Repayment Rate</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Days in Arrears</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Performance Grade</th>
            <th style="background-color: #28a745; color: white; font-weight: bold;">Risk Category</th>
        </tr>
    </thead>
    <tbody>
        @forelse($performanceData['loans'] as $loan)
        <tr>
            <td>{{ $loan['customer'] }}</td>
            <td>{{ $loan['customer_no'] }}</td>
            <td>{{ $loan['branch'] }}</td>
            <td>{{ $loan['group'] }}</td>
            <td>{{ $loan['loan_officer'] }}</td>
            <td>{{ number_format($loan['disbursed_amount'], 2) }}</td>
            <td>{{ number_format($loan['outstanding_amount'], 2) }}</td>
            <td>{{ number_format($loan['total_paid'], 2) }}</td>
            <td>{{ number_format($loan['repayment_rate'], 2) }}%</td>
            <td>{{ $loan['days_in_arrears'] }}</td>
            <td>{{ $loan['performance_grade'] }}</td>
            <td>{{ $loan['risk_category'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="12" style="text-align: center;">No loans found for the selected criteria.</td>
        </tr>
        @endforelse
    </tbody>
</table>
