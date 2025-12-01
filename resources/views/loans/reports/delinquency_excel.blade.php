<table>
    <thead>
        <tr>
            <th colspan="12" style="font-size: 16px; font-weight: bold; text-align: center; background-color: #ffc107; color: black;">
                DELINQUENCY REPORT
            </th>
        </tr>
        <tr>
            <th colspan="12" style="font-size: 12px; text-align: center; background-color: #E7E6E6;">
                Report Date: {{ now()->format('F d, Y') }} | As of: {{ \Carbon\Carbon::parse($delinquencyData['summary']['as_of_date'] ?? now())->format('F d, Y') }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th colspan="12" style="font-weight: bold; background-color: #D9D9D9;">DELINQUENCY SUMMARY</th>
        </tr>
        <tr>
            <th style="background-color: #E7E6E6; font-weight: bold;">Delinquent Loans</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Delinquency Rate</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Delinquent Amount</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Current Loans</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">1-30 Days</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">31-60 Days</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">61-90 Days</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">91-180 Days</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">180+ Days</th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <td>{{ number_format($delinquencyData['summary']['delinquent_loans']) }}</td>
            <td>{{ number_format($delinquencyData['summary']['delinquency_rate'], 2) }}%</td>
            <td>{{ number_format($delinquencyData['summary']['total_delinquent_amount'], 2) }}</td>
            <td>{{ number_format($delinquencyData['summary']['current_loans']) }}</td>
            <td>{{ $delinquencyData['buckets']['1-30']['count'] }}</td>
            <td>{{ $delinquencyData['buckets']['31-60']['count'] }}</td>
            <td>{{ $delinquencyData['buckets']['61-90']['count'] }}</td>
            <td>{{ $delinquencyData['buckets']['91-180']['count'] }}</td>
            <td>{{ $delinquencyData['buckets']['180+']['count'] }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr></tr>
        <tr>
            <th colspan="12" style="font-weight: bold; background-color: #D9D9D9;">DELINQUENT LOANS DETAILS</th>
        </tr>
        <tr>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Customer</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Customer No</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Phone</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Branch</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Group</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Loan Officer</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Outstanding Amount</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Days in Arrears</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Delinquency Bucket</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Severity Level</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Last Payment Date</th>
            <th style="background-color: #ffc107; color: black; font-weight: bold;">Next Due Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($delinquencyData['loans'] as $loan)
        <tr>
            <td>{{ $loan['customer'] }}</td>
            <td>{{ $loan['customer_no'] }}</td>
            <td>{{ $loan['phone'] }}</td>
            <td>{{ $loan['branch'] }}</td>
            <td>{{ $loan['group'] }}</td>
            <td>{{ $loan['loan_officer'] }}</td>
            <td>{{ number_format($loan['outstanding_amount'], 2) }}</td>
            <td>{{ $loan['days_in_arrears'] }}</td>
            <td>{{ $loan['delinquency_bucket'] }}</td>
            <td>{{ $loan['severity_level'] }}</td>
            <td>{{ $loan['last_payment_date'] }}</td>
            <td>{{ $loan['next_due_date'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="12" style="text-align: center;">No delinquent loans found for the selected criteria.</td>
        </tr>
        @endforelse
    </tbody>
</table>
