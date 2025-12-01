<table>
    <!-- Report Header -->
    <tr>
        <td colspan="13" style="font-size: 18px; font-weight: bold; text-align: center; background-color: #f8f9fa; color: #007bff;">
            INTERNAL PORTFOLIO ANALYSIS REPORT (PAR {{ $par_days }})
        </td>
    </tr>
    <tr>
        <td colspan="13" style="font-size: 12px; text-align: center; color: #666;">
            Conservative Analysis - Only Overdue Amounts at Risk
        </td>
    </tr>
    
    <!-- Company Information -->
    @if($company)
    <tr>
        <td colspan="13" style="font-weight: bold; text-align: center;">{{ $company->name }}</td>
    </tr>
    @if($company->address)
    <tr>
        <td colspan="13" style="text-align: center;">{{ $company->address }}</td>
    </tr>
    @endif
    @if($company->phone || $company->email)
    <tr>
        <td colspan="13" style="text-align: center;">
            @if($company->phone)Phone: {{ $company->phone }}@endif
            @if($company->phone && $company->email) | @endif
            @if($company->email)Email: {{ $company->email }}@endif
        </td>
    </tr>
    @endif
    @endif
    
    <!-- Report Information -->
    <tr><td colspan="13"></td></tr>
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
        <td colspan="2"></td>
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
        <td colspan="5"></td>
    </tr>
    
    <!-- Summary Section -->
    <tr><td colspan="13"></td></tr>
    <tr>
        <td colspan="13" style="font-weight: bold; font-size: 14px; background-color: #e9ecef;">PORTFOLIO SUMMARY</td>
    </tr>
    <tr>
        <td style="font-weight: bold; background-color: #f8f9fa;">Total Portfolio:</td>
        <td style="background-color: #f8f9fa;">TZS {{ number_format($total_outstanding, 2) }}</td>
        <td></td>
        <td style="font-weight: bold; background-color: #f8f9fa;">Total Overdue:</td>
        <td style="background-color: #f8f9fa;">TZS {{ number_format($total_overdue, 2) }}</td>
        <td></td>
        <td style="font-weight: bold; background-color: #f8f9fa;">At Risk Amount:</td>
        <td style="background-color: #f8f9fa;">TZS {{ number_format($total_at_risk, 2) }}</td>
        <td colspan="5"></td>
    </tr>
    <tr>
        <td style="font-weight: bold; background-color: #f8f9fa;">Overdue Ratio:</td>
        <td style="background-color: #f8f9fa;">{{ number_format($overdue_ratio, 2) }}%</td>
        <td></td>
        <td style="font-weight: bold; background-color: #f8f9fa;">Conservative PAR {{ $par_days }}:</td>
        <td style="background-color: #f8f9fa;">{{ number_format($conservative_par_ratio, 2) }}%</td>
        <td></td>
        <td style="font-weight: bold; background-color: #f8f9fa;">Loans at Risk:</td>
        <td style="background-color: #f8f9fa;">{{ $loans_at_risk }} / {{ $total_loans }}</td>
        <td colspan="5"></td>
    </tr>
    <tr>
        <td style="font-weight: bold; background-color: #f8f9fa;">Exposure Distribution:</td>
        <td style="background-color: #f8f9fa;">Current: {{ $exposure_categories['Current'] ?? 0 }}</td>
        <td style="background-color: #f8f9fa;">Low: {{ $exposure_categories['Low Exposure'] ?? 0 }}</td>
        <td style="background-color: #f8f9fa;">Medium: {{ $exposure_categories['Medium Exposure'] ?? 0 }}</td>
        <td style="background-color: #f8f9fa;">High: {{ $exposure_categories['High Exposure'] ?? 0 }}</td>
        <td style="background-color: #f8f9fa;">Critical: {{ $exposure_categories['Critical Exposure'] ?? 0 }}</td>
        <td colspan="7"></td>
    </tr>
    
    <!-- Table Header -->
    <tr><td colspan="13"></td></tr>
    <tr style="background-color: #495057; color: white; font-weight: bold;">
        <td>#</td>
        <td>Customer Name</td>
        <td>Customer No</td>
        <td>Loan No</td>
        <td>Branch</td>
        <td>Group</td>
        <td>Outstanding Balance</td>
        <td>Overdue Amount</td>
        <td>At Risk Amount</td>
        <td>Overdue %</td>
        <td>Days in Arrears</td>
        <td>Risk Level</td>
        <td>Exposure Category</td>
    </tr>
    
    <!-- Data Rows -->
    @if(count($analysis_data) > 0)
        @foreach($analysis_data as $index => $row)
        <tr style="background-color: {{ $row['is_at_risk'] ? '#f8d7da' : ($row['overdue_amount'] > 0 ? '#fff3cd' : '') }};">
            <td>{{ $index + 1 }}</td>
            <td>{{ $row['customer'] }}</td>
            <td>{{ $row['customer_no'] }}</td>
            <td>{{ $row['loan_no'] }}</td>
            <td>{{ $row['branch'] }}</td>
            <td>{{ $row['group'] }}</td>
            <td>{{ number_format($row['outstanding_balance'], 0) }}</td>
            <td style="color: #856404; font-weight: bold;">{{ number_format($row['overdue_amount'], 0) }}</td>
            <td style="color: #dc3545; font-weight: bold;">{{ number_format($row['at_risk_amount'], 0) }}</td>
            <td>{{ $row['overdue_ratio'] }}%</td>
            <td>{{ $row['days_in_arrears'] }}</td>
            <td style="background-color: 
                @if($row['risk_level'] == 'Low') #d4edda 
                @elseif($row['risk_level'] == 'Medium') #fff3cd 
                @elseif($row['risk_level'] == 'High') #f8d7da 
                @else #f5c6cb @endif;">
                {{ $row['risk_level'] }}
            </td>
            <td style="background-color: 
                @if($row['exposure_category'] == 'Current') #d4edda 
                @elseif($row['exposure_category'] == 'Low Exposure') #cce5ff 
                @elseif($row['exposure_category'] == 'Medium Exposure') #fff3cd 
                @elseif($row['exposure_category'] == 'High Exposure') #f8d7da 
                @else #f5c6cb @endif;">
                {{ $row['exposure_category'] }}
            </td>
        </tr>
        @endforeach
        
        <!-- Totals Row -->
        <tr style="background-color: #f8f9fa; font-weight: bold;">
            <td colspan="6">TOTALS</td>
            <td>TZS {{ number_format($total_outstanding, 2) }}</td>
            <td>TZS {{ number_format($total_overdue, 2) }}</td>
            <td>TZS {{ number_format($total_at_risk, 2) }}</td>
            <td>{{ number_format($overdue_ratio, 2) }}%</td>
            <td>-</td>
            <td>-</td>
            <td>{{ $total_loans }} Loans</td>
        </tr>
    @else
        <tr>
            <td colspan="13" style="text-align: center; padding: 20px; color: #28a745; font-weight: bold;">
                No loans found matching the selected criteria.
            </td>
        </tr>
    @endif
    
    <!-- Footer -->
    <tr><td colspan="13"></td></tr>
    <tr>
        <td colspan="13" style="text-align: center; font-size: 10px; color: #666;">
            Report generated on {{ $generated_date }} | Internal Portfolio Analysis (Conservative PAR {{ $par_days }}) as of {{ \Carbon\Carbon::parse($as_of_date)->format('d-m-Y') }}
        </td>
    </tr>
    <tr>
        <td colspan="13" style="text-align: center; font-size: 10px; color: #666;">
            <strong>Note:</strong> This conservative approach shows only overdue amounts as at-risk, providing detailed exposure analysis for internal use.
        </td>
    </tr>
</table>
