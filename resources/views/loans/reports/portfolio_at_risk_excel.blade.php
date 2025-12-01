<table>
    <!-- Report Header -->
    <tr>
        <td colspan="15" style="font-size: 18px; font-weight: bold; text-align: center; background-color: #f8f9fa; color: #fd7e14;">
            PORTFOLIO AT RISK (PAR {{ $par_days }}) REPORT
        </td>
    </tr>
    
    <!-- Company Information -->
    @if($company)
    <tr>
        <td colspan="15" style="font-weight: bold; text-align: center;">{{ $company->name }}</td>
    </tr>
    @if($company->address)
    <tr>
        <td colspan="15" style="text-align: center;">{{ $company->address }}</td>
    </tr>
    @endif
    @if($company->phone || $company->email)
    <tr>
        <td colspan="15" style="text-align: center;">
            @if($company->phone)Phone: {{ $company->phone }}@endif
            @if($company->phone && $company->email) | @endif
            @if($company->email)Email: {{ $company->email }}@endif
        </td>
    </tr>
    @endif
    @endif
    
    <!-- Report Information -->
    <tr><td colspan="15"></td></tr>
    <tr>
        <td style="font-weight: bold;">Report Date:</td>
        <td>{{ $generated_date }}</td>
        <td></td>
        <td style="font-weight: bold;">As of Date:</td>
        <td>{{ \Carbon\Carbon::parse($as_of_date)->format('d-m-Y') }}</td>
        <td></td>
        <td style="font-weight: bold;">PAR Days:</td>
        <td>{{ $par_days }} days</td>
        <td></td>
        <td style="font-weight: bold;">Total Loans:</td>
        <td>{{ $total_loans }}</td>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Branch:</td>
        <td>{{ $branch_name }}</td>
        <td></td>
        <td style="font-weight: bold;">Group:</td>
        <td>{{ $group_name }}</td>
        <td></td>
        <td style="font-weight: bold;">Loan Officer:</td>
        <td>{{ $loan_officer_name }}</td>
        <td colspan="7"></td>
    </tr>
    
    <!-- Summary Section -->
    <tr><td colspan="15"></td></tr>
    <tr>
        <td colspan="15" style="font-weight: bold; font-size: 14px; background-color: #e9ecef;">PORTFOLIO AT RISK SUMMARY</td>
    </tr>
    <tr>
        <td style="font-weight: bold; background-color: #f8f9fa;">Total Portfolio Amount:</td>
        <td style="background-color: #f8f9fa;">TZS {{ number_format($total_outstanding, 2) }}</td>
        <td></td>
        <td style="font-weight: bold; background-color: #f8f9fa;">Amount at Risk:</td>
        <td style="background-color: #f8f9fa;">TZS {{ number_format($total_at_risk, 2) }}</td>
        <td colspan="10"></td>
    </tr>
    <tr>
        <td style="font-weight: bold; background-color: #f8f9fa;">PAR {{ $par_days }} Ratio:</td>
        <td style="background-color: #f8f9fa;">{{ number_format($par_ratio, 1) }}%</td>
        <td></td>
        <td style="font-weight: bold; background-color: #f8f9fa;">Loans at Risk:</td>
        <td style="background-color: #f8f9fa;">{{ $loans_at_risk }} / {{ $total_loans }}</td>
        <td colspan="10"></td>
    </tr>
    <tr>
        <td style="font-weight: bold; background-color: #f8f9fa;">Risk Breakdown:</td>
        <td style="background-color: #f8f9fa;">Low: {{ $risk_levels['Low'] ?? 0 }}</td>
        <td style="background-color: #f8f9fa;">Medium: {{ $risk_levels['Medium'] ?? 0 }}</td>
        <td style="background-color: #f8f9fa;">High: {{ $risk_levels['High'] ?? 0 }}</td>
        <td style="background-color: #f8f9fa;">Critical: {{ $risk_levels['Critical'] ?? 0 }}</td>
        <td colspan="10"></td>
    </tr>
    
    <!-- Table Header -->
    <tr><td colspan="15"></td></tr>
    <tr style="background-color: #495057; color: white; font-weight: bold;">
        <td>#</td>
        <td>Customer Name</td>
        <td>Customer No</td>
        <td>Phone</td>
        <td>Loan No</td>
        <td>Loan Amount</td>
        <td>Branch</td>
        <td>Group</td>
        <td>Loan Officer</td>
        <td>Outstanding Balance</td>
        <td>At Risk Amount</td>
        <td>Risk %</td>
        <td>Days in Arrears</td>
        <td>Risk Level</td>
        <td>Status</td>
    </tr>
    
    <!-- Data Rows -->
    @if(count($par_data) > 0)
        @foreach($par_data as $index => $row)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row['customer'] }}</td>
            <td>{{ $row['customer_no'] }}</td>
            <td>{{ $row['phone'] }}</td>
            <td>{{ $row['loan_no'] }}</td>
            <td>{{ number_format($row['loan_amount'], 0) }}</td>
            <td>{{ $row['branch'] }}</td>
            <td>{{ $row['group'] }}</td>
            <td>{{ $row['loan_officer'] }}</td>
            <td>{{ number_format($row['outstanding_balance'], 2) }}</td>
            <td style="color: #dc3545; font-weight: bold;">{{ number_format($row['at_risk_amount'], 2) }}</td>
            <td>{{ $row['risk_percentage'] }}%</td>
            <td>{{ $row['days_in_arrears'] }}</td>
            <td style="background-color: 
                @if($row['risk_level'] == 'Low') #d4edda 
                @elseif($row['risk_level'] == 'Medium') #fff3cd 
                @elseif($row['risk_level'] == 'High') #f8d7da 
                @else #f5c6cb @endif;">
                {{ $row['risk_level'] }}
            </td>
            <td style="color: {{ $row['is_at_risk'] ? '#dc3545' : '#28a745' }}; font-weight: bold;">
                {{ $row['is_at_risk'] ? 'At Risk' : 'Safe' }}
            </td>
        </tr>
        @endforeach
        
        <!-- Totals Row -->
        <tr style="background-color: #f8f9fa; font-weight: bold;">
            <td colspan="9">TOTALS</td>
            <td>TZS {{ number_format($total_outstanding, 2) }}</td>
            <td>TZS {{ number_format($total_at_risk, 2) }}</td>
            <td>{{ number_format($par_ratio, 1) }}%</td>
            <td>-</td>
            <td>-</td>
            <td>{{ $total_loans }} Loans</td>
        </tr>
    @else
        <tr>
            <td colspan="15" style="text-align: center; padding: 20px; color: #28a745; font-weight: bold;">
                No loans found matching the Portfolio at Risk criteria for the selected filters.
            </td>
        </tr>
    @endif
    
    <!-- Footer -->
    <tr><td colspan="15"></td></tr>
    <tr>
        <td colspan="15" style="text-align: center; font-size: 10px; color: #666;">
            Report generated on {{ $generated_date }} | PAR {{ $par_days }} as of {{ \Carbon\Carbon::parse($as_of_date)->format('d-m-Y') }}
        </td>
    </tr>
</table>
