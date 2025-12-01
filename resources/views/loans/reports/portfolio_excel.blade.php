<table>
    <thead>
        <tr>
            <th colspan="13" style="font-size: 16px; font-weight: bold; text-align: center; background-color: #4472C4; color: white;">
                LOAN PORTFOLIO REPORT
            </th>
        </tr>
        <tr>
            <th colspan="13" style="font-size: 12px; text-align: center; background-color: #E7E6E6;">
                Report Date: {{ now()->format('F d, Y') }} | As of: {{ \Carbon\Carbon::parse($portfolioData['summary']['as_of_date'] ?? now())->format('F d, Y') }}
                @if(isset($status) && $status !== 'all')
                    | Status: {{ $status === 'active_completed' ? 'Active & Completed' : ucfirst($status) }}
                @endif
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th colspan="13" style="font-weight: bold; background-color: #D9D9D9;">PORTFOLIO SUMMARY</th>
        </tr>
        <tr>
            <th style="background-color: #E7E6E6; font-weight: bold;">Total Loans</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Active Loans</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Completed Loans</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Defaulted Loans</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Total Disbursed</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Total Outstanding</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Total Paid</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Repayment Rate</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">Portfolio at Risk</th>
            <th style="background-color: #E7E6E6; font-weight: bold;">PAR Ratio</th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <td>{{ number_format($portfolioData['summary']['total_loans']) }}</td>
            <td>{{ number_format($portfolioData['summary']['active_loans']) }}</td>
            <td>{{ number_format($portfolioData['summary']['completed_loans']) }}</td>
            <td>{{ number_format($portfolioData['summary']['defaulted_loans']) }}</td>
            <td>{{ number_format($portfolioData['summary']['total_disbursed'], 2) }}</td>
            <td>{{ number_format($portfolioData['summary']['total_outstanding'], 2) }}</td>
            <td>{{ number_format($portfolioData['summary']['total_paid'], 2) }}</td>
            <td>{{ number_format($portfolioData['summary']['overall_repayment_rate'], 2) }}%</td>
            <td>{{ number_format($portfolioData['summary']['portfolio_at_risk'], 2) }}</td>
            <td>{{ number_format($portfolioData['summary']['par_ratio'], 2) }}%</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr></tr>
        <tr>
            <th colspan="13" style="font-weight: bold; background-color: #D9D9D9;">PORTFOLIO DETAILS</th>
        </tr>
        <tr>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Customer</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Customer No</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Phone</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Branch</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Group</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Loan Officer</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Status</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Disbursed Amount</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Outstanding Amount</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Repayment Rate</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Days in Arrears</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Disbursed Date</th>
            <th style="background-color: #4472C4; color: white; font-weight: bold;">Maturity Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($portfolioData['loans'] as $loan)
        <tr>
            <td>{{ $loan['customer'] }}</td>
            <td>{{ $loan['customer_no'] }}</td>
            <td>{{ $loan['phone'] }}</td>
            <td>{{ $loan['branch'] }}</td>
            <td>{{ $loan['group'] }}</td>
            <td>{{ $loan['loan_officer'] }}</td>
            <td>{{ ucfirst($loan['status']) }}</td>
            <td>{{ number_format($loan['disbursed_amount'], 2) }}</td>
            <td>{{ number_format($loan['outstanding_amount'], 2) }}</td>
            <td>{{ number_format($loan['repayment_rate'], 2) }}%</td>
            <td>{{ $loan['days_in_arrears'] }}</td>
            <td>{{ $loan['disbursed_date'] }}</td>
            <td>{{ $loan['maturity_date'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="13" style="text-align: center;">No loans found for the selected criteria.</td>
        </tr>
        @endforelse
    </tbody>
</table>
