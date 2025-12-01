@extends('layouts.main')

@section('title', 'BOT Geographical Distribution of Branches, Employees and Loans by Age')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
            ['label' => 'BOT Reports', 'url' => route('reports.bot'), 'icon' => 'bx bx-building-house'],
            ['label' => 'Geographical Distribution', 'url' => '#', 'icon' => 'bx bx-map']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">BOT Geographical Distribution of Branches, Employees and Loans by Age</h6>
            <a href="{{ route('reports.bot.geographical-distribution.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-success">
                <i class="bx bx-download me-1"></i> Export XLS
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="as_of_date" class="form-label">As at Date</label>
                        <input type="date" class="form-control" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-search me-1"></i> Generate Report
                        </button>
                        <a href="{{ route('reports.bot.geographical-distribution') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh me-1"></i> Reset
                        </a>
                    </div>
                </form>

                <div class="bot-container">
                    <div class="bot-header">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong>NAME OF INSTITUTION:</strong> 
                                    <span class="text-muted">[Your Institution Name]</span>
                                </div>
                                <div class="mb-2">
                                    <strong>MSP CODE:</strong> 
                                    <span class="text-muted">[Your MSP Code]</span>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="text-center my-3">GEOGRAPHICAL DISTRIBUTION OF BRANCHES, EMPLOYEES AND LOANS BY AGE FOR QUARTER</h4>
                        <p class="text-center mb-2"><strong>BOT Form MSP2-10 To be submitted Quarterly</strong></p>
                        <p class="text-center text-muted">(Amount in TZS 0.00)</p>
                        <div class="text-center mt-2">
                            <strong>As at: {{ \Carbon\Carbon::parse($asOfDate)->format('d/m/Y') }}</strong>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered bot-table">
                            <thead>
                                <tr class="table-dark">
                                    <th rowspan="3" width="3%" class="text-center">Sno</th>
                                    <th rowspan="3" width="15%">Geographical Area</th>
                                    <th rowspan="3" width="5%" class="text-center">Number of Branches</th>
                                    <th rowspan="3" width="5%" class="text-center">Number of Employees</th>
                                    <th rowspan="3" width="8%" class="text-center">Compulsory savings (Amount in TZS 0.00)</th>
                                    <th colspan="6" class="text-center">Number of Borrowers</th>
                                    <th colspan="6" class="text-center">Number of Loans</th>
                                    <th colspan="6" class="text-center">Outstanding Loans (Amount in TZS)</th>
                                </tr>
                                <tr class="table-secondary">
                                    <th colspan="3" class="text-center">Up to 35 Years</th>
                                    <th colspan="3" class="text-center">Above 35 Years</th>
                                    <th colspan="3" class="text-center">Up to 35 Years</th>
                                    <th colspan="3" class="text-center">Above 35 Years</th>
                                    <th colspan="3" class="text-center">Up to 35 Years</th>
                                    <th colspan="3" class="text-center">Above 35 Years</th>
                                </tr>
                                <tr class="table-secondary">
                                    <th class="text-center">Female</th>
                                    <th class="text-center">Male</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Female</th>
                                    <th class="text-center">Male</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Female</th>
                                    <th class="text-center">Male</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Female</th>
                                    <th class="text-center">Male</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Female</th>
                                    <th class="text-center">Male</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Female</th>
                                    <th class="text-center">Male</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rowNumber = 1; @endphp
                                
                                <!-- Areas with Data -->
                                @foreach($areaData as $areaName => $data)
                                <tr>
                                    <td class="text-center">{{ $rowNumber++ }}</td>
                                    <td>{{ $areaName }}</td>
                                    <td class="text-center">{{ $data['branches'] }}</td>
                                    <td class="text-center">{{ $data['employees'] }}</td>
                                    <td class="text-end">{{ number_format($data['compulsory_savings'], 0) }}</td>
                                    
                                    <!-- Number of Borrowers - Up to 35 Years -->
                                    <td class="text-center">{{ $data['borrowers_up35_female'] }}</td>
                                    <td class="text-center">{{ $data['borrowers_up35_male'] }}</td>
                                    <td class="text-center fw-bold">{{ $data['borrowers_up35_female'] + $data['borrowers_up35_male'] }}</td>
                                    
                                    <!-- Number of Borrowers - Above 35 Years -->
                                    <td class="text-center">{{ $data['borrowers_above35_female'] }}</td>
                                    <td class="text-center">{{ $data['borrowers_above35_male'] }}</td>
                                    <td class="text-center fw-bold">{{ $data['borrowers_above35_female'] + $data['borrowers_above35_male'] }}</td>
                                    
                                    <!-- Number of Loans - Up to 35 Years -->
                                    <td class="text-center">{{ $data['loans_up35_female'] }}</td>
                                    <td class="text-center">{{ $data['loans_up35_male'] }}</td>
                                    <td class="text-center fw-bold">{{ $data['loans_up35_female'] + $data['loans_up35_male'] }}</td>
                                    
                                    <!-- Number of Loans - Above 35 Years -->
                                    <td class="text-center">{{ $data['loans_above35_female'] }}</td>
                                    <td class="text-center">{{ $data['loans_above35_male'] }}</td>
                                    <td class="text-center fw-bold">{{ $data['loans_above35_female'] + $data['loans_above35_male'] }}</td>
                                    
                                    <!-- Outstanding Loans - Up to 35 Years -->
                                    <td class="text-end">TZS {{ number_format($data['outstanding_up35_female'], 2) }}</td>
                                    <td class="text-end">TZS {{ number_format($data['outstanding_up35_male'], 2) }}</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($data['outstanding_up35_female'] + $data['outstanding_up35_male'], 2) }}</td>
                                    
                                    <!-- Outstanding Loans - Above 35 Years -->
                                    <td class="text-end">TZS {{ number_format($data['outstanding_above35_female'], 2) }}</td>
                                    <td class="text-end">TZS {{ number_format($data['outstanding_above35_male'], 2) }}</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($data['outstanding_above35_female'] + $data['outstanding_above35_male'], 2) }}</td>
                                </tr>
                                @endforeach
                                
                                <!-- Empty Areas (for display purposes) -->
                                @foreach($geographicalAreas as $area)
                                    @if(!isset($areaData[$area]))
                                    <tr>
                                        <td class="text-center">{{ $rowNumber++ }}</td>
                                        <td>{{ $area }}</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-end">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center fw-bold">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center fw-bold">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center fw-bold">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center">0</td>
                                        <td class="text-center fw-bold">0</td>
                                        <td class="text-end">TZS 0.00</td>
                                        <td class="text-end">TZS 0.00</td>
                                        <td class="text-end fw-bold">TZS 0.00</td>
                                        <td class="text-end">TZS 0.00</td>
                                        <td class="text-end">TZS 0.00</td>
                                        <td class="text-end fw-bold">TZS 0.00</td>
                                    </tr>
                                    @endif
                                @endforeach

                                <!-- Total Mainland Row -->
                                <tr class="table-info">
                                    <td class="text-center fw-bold text-white">{{ count($geographicalAreas) + 1 }}</td>
                                    <td class="fw-bold text-white">Total Mainland</td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'branches')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'employees')) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'compulsory_savings')), 0) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_up35_female')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_up35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_up35_female')) + array_sum(array_column($areaData, 'borrowers_up35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_above35_female')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_above35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_above35_female')) + array_sum(array_column($areaData, 'borrowers_above35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_up35_female')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_up35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_up35_female')) + array_sum(array_column($areaData, 'loans_up35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_above35_female')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_above35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_above35_female')) + array_sum(array_column($areaData, 'loans_above35_male')) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_up35_female')), 0) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_up35_male')), 0) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_up35_female')) + array_sum(array_column($areaData, 'outstanding_up35_male')), 0) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_above35_female')), 0) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_above35_male')), 0) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_above35_female')) + array_sum(array_column($areaData, 'outstanding_above35_male')), 0) }}
                                    </td>
                                </tr>



                                <!-- Grand Total Row -->
                                <tr class="table-dark">
                                    <td class="text-center fw-bold text-white">{{ $rowNumber++ }}</td>
                                    <td class="fw-bold text-white">Grand Total</td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'branches')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'employees')) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'compulsory_savings')), 0) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_up35_female')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_up35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_up35_female')) + array_sum(array_column($areaData, 'borrowers_up35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_above35_female')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_above35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'borrowers_above35_female')) + array_sum(array_column($areaData, 'borrowers_above35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_up35_female')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_up35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_up35_female')) + array_sum(array_column($areaData, 'loans_up35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_above35_female')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_above35_male')) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($areaData, 'loans_above35_female')) + array_sum(array_column($areaData, 'loans_above35_male')) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_up35_female')), 0) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_up35_male')), 0) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_up35_female')) + array_sum(array_column($areaData, 'outstanding_up35_male')), 0) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_above35_female')), 0) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_above35_male')), 0) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        {{ number_format(array_sum(array_column($areaData, 'outstanding_above35_female')) + array_sum(array_column($areaData, 'outstanding_above35_male')), 0) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Note:</strong> This report shows geographical distribution of branches, employees, and loans categorized by age groups (Up to 35 Years and Above 35 Years) and gender (Female and Male). 
                            All amounts are in Tanzanian Shillings (TZS).
                        </small>
                        <div class="mt-2">
                            <small class="text-info">
                                <strong>Current Data:</strong> 
                                @if(count($areaData) > 0)
                                    {{ count($areaData) }} areas with data loaded successfully. 
                                    Sample: {{ array_keys($areaData)[0] ?? 'None' }}
                                @else
                                    No data loaded yet.
                                @endif
                            </small>
                        </div>
                        @if(count($areaData) > 0)
                        <div class="mt-2">
                            <small class="text-success">
                                <strong>Sample Outstanding Amounts:</strong><br>
                                @foreach(array_slice($areaData, 0, 2) as $areaName => $data)
                                    <strong>{{ $areaName }}:</strong> 
                                    Up35 Male: TZS {{ number_format($data['outstanding_up35_male'], 2) }} | 
                                    Up35 Female: TZS {{ number_format($data['outstanding_up35_female'], 2) }}<br>
                                @endforeach
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bot-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.bot-header {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
}

.bot-table {
    margin-bottom: 0;
    font-size: 11px;
}

.bot-table th {
    background: #6c757d !important;
    color: white;
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
    padding: 6px 3px;
}

.bot-table .table-secondary th {
    background: #495057 !important;
    color: white;
}

.bot-table td {
    padding: 4px 3px;
    vertical-align: middle;
}

.bot-table .table-dark {
    background-color: #343a40 !important;
}

.bot-table .table-info {
    background-color: #17a2b8 !important;
}

.bot-table .table-warning {
    background-color: #ffc107 !important;
}

.text-end {
    text-align: right;
}

.fw-bold {
    font-weight: bold;
}

.text-white {
    color: white !important;
}

.text-warning {
    color: #856404 !important;
}

.bot-table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.bot-table tbody tr:hover {
    background-color: #e9ecef;
}
</style>
@endsection 