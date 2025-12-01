@extends('layouts.main')

@section('title', 'BOT Loans Disbursed by Sector, Gender and Amount')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
            ['label' => 'BOT Reports', 'url' => route('reports.bot'), 'icon' => 'bx bx-building-house'],
            ['label' => 'Loans Disbursed by Sector, Gender and Amount', 'url' => '#', 'icon' => 'bx bx-chart']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">BOT Loans Disbursed by Sector, Gender and Amount</h6>
            <a href="{{ route('reports.bot.loans-disbursed.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-success">
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
                        <a href="{{ route('reports.bot.loans-disbursed') }}" class="btn btn-secondary">
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
                                    <span class="text-muted">{{ $company->name ?? 'Company Name Not Set' }}</span>
                                </div>
                                <div class="mb-2">
                                    <strong>MSP CODE:</strong> 
                                    <span class="text-muted">{{ $company->msp_code ?? 'MSP Code Not Set' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="text-center my-3">LOANS DISBURSED BY SECTOR, GENDER AND AMOUNT FOR THE QUARTER ENDED {{ $quarterStart->format('d/m/Y') }}</h4>
                        <p class="text-center mb-2"><strong>BOT FORM: To be submitted Quarterly</strong></p>
                        <p class="text-center text-muted">(Amount in TZS)</p>
                        <div class="text-center mt-2">
                            <strong>Quarter {{ $quarter }} ({{ $quarterStart->format('d/m/Y') }} - {{ $quarterEnd->format('d/m/Y') }})</strong>
                        </div>
                        <div class="text-center mt-1">
                            <strong>As at: {{ \Carbon\Carbon::parse($asOfDate)->format('d/m/Y') }}</strong>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered bot-table">
                            <thead>
                                <tr class="table-dark">
                                    <th width="5%" class="text-center">Sno</th>
                                    <th width="25%">Sector</th>
                                    <th colspan="2" class="text-center">Loans Disbursed to Female</th>
                                    <th colspan="2" class="text-center">Loans Disbursed to Male</th>
                                    <th colspan="2" class="text-center">Total Loans Disbursed</th>
                                </tr>
                                <tr class="table-secondary">
                                    <th></th>
                                    <th></th>
                                    <th width="8%" class="text-center">Number</th>
                                    <th width="12%" class="text-center">Amount</th>
                                    <th width="8%" class="text-center">Number</th>
                                    <th width="12%" class="text-center">Amount</th>
                                    <th width="8%" class="text-center">Number (c+e)</th>
                                    <th width="12%" class="text-center">Amount (d+f)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sectors as $index => $sector)
                                <tr>
                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                    <td>{{ $sector }}</td>
                                    <td class="text-center">
                                        @if(isset($sectorData[$sector]))
                                            {{ $sectorData[$sector]['female_number'] }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(isset($sectorData[$sector]))
                                            TZS {{ number_format($sectorData[$sector]['female_amount'], 2) }}
                                        @else
                                            TZS 0.00
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(isset($sectorData[$sector]))
                                            {{ $sectorData[$sector]['male_number'] }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(isset($sectorData[$sector]))
                                            TZS {{ number_format($sectorData[$sector]['male_amount'], 2) }}
                                        @else
                                            TZS 0.00
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">
                                        @if(isset($sectorData[$sector]))
                                            {{ $sectorData[$sector]['female_number'] + $sectorData[$sector]['male_number'] }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">
                                        @if(isset($sectorData[$sector]))
                                            TZS {{ number_format($sectorData[$sector]['female_amount'] + $sectorData[$sector]['male_amount'], 2) }}
                                        @else
                                            TZS 0.00
                                        @endif
                                    </td>
                                </tr>
                                @endforeach

                                <!-- Total Row -->
                                <tr class="table-dark">
                                    <td class="text-center fw-bold text-white">Total</td>
                                    <td class="fw-bold text-white"></td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($sectorData, 'female_number')) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        TZS {{ number_format(array_sum(array_column($sectorData, 'female_amount')), 2) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($sectorData, 'male_number')) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        TZS {{ number_format(array_sum(array_column($sectorData, 'male_amount')), 2) }}
                                    </td>
                                    <td class="text-center fw-bold text-white">
                                        {{ array_sum(array_column($sectorData, 'female_number')) + array_sum(array_column($sectorData, 'male_number')) }}
                                    </td>
                                    <td class="text-end fw-bold text-white">
                                        TZS {{ number_format(array_sum(array_column($sectorData, 'female_amount')) + array_sum(array_column($sectorData, 'male_amount')), 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Note:</strong> This report shows loan disbursements categorized by economic sector and gender. 
                            Numbers represent loan counts and amounts are in Tanzanian Shillings (TZS).
                        </small>
                    </div>
                    
                    @if(count($sectorData) > 0)
                    <div class="mt-3">
                        <small class="text-info">
                            <strong>Current Data:</strong> 
                            Quarter {{ $quarter }} ({{ $quarterStart->format('d/m/Y') }} - {{ $quarterEnd->format('d/m/Y') }})
                            <br>
                            <strong>Total Loans:</strong> {{ $totalData['female_number'] + $totalData['male_number'] }} | 
                            <strong>Total Amount:</strong> TZS {{ number_format($totalData['female_amount'] + $totalData['male_amount'], 2) }}
                        </small>
                    </div>
                    @endif
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
    font-size: 12px;
}

.bot-table th {
    background: #6c757d !important;
    color: white;
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
    padding: 8px 4px;
}

.bot-table .table-secondary th {
    background: #495057 !important;
    color: white;
}

.bot-table td {
    padding: 6px 4px;
    vertical-align: middle;
}

.bot-table .table-dark {
    background-color: #343a40 !important;
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

.bot-table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.bot-table tbody tr:hover {
    background-color: #e9ecef;
}
</style>
@endsection 