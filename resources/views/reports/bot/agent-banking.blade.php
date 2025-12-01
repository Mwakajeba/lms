@extends('layouts.main')

@section('title', 'BOT Agent Banking Balances Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-file'],
            ['label' => 'BOT Reports', 'url' => route('reports.bot'), 'icon' => 'bx bx-building-house'],
            ['label' => 'Agent Banking Balances', 'url' => '#', 'icon' => 'bx bx-bank']
        ]" />

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">BOT Agent Banking Balances Report</h6>
            <a href="{{ route('reports.bot.agent-banking.export', ['as_of_date' => $asOfDate]) }}" class="btn btn-success">
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
                        <a href="{{ route('reports.bot.agent-banking') }}" class="btn btn-secondary">
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
                        
                        <h4 class="text-center my-3">AGENT BANKING BALANCES IN BANKS AND FINANCIAL INSTITUTIONS FOR THE QUARTER ENDED</h4>
                        <p class="text-center mb-2"><strong>BOT FORM MSP2-08: To be submitted Quarterly</strong></p>
                        <p class="text-center text-muted">(Amount in TZS 0.00)</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered bot-table">
                            <thead>
                                <tr class="table-secondary">
                                    <th width="8%">Sno</th>
                                    <th width="62%">Name of Bank or Financial Institution</th>
                                    <th width="20%">Balance</th>
                                    <th width="10%">Validation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Banks in Tanzania Section -->
                                <tr class="table-info">
                                    <td class="text-center fw-bold">1</td>
                                    <td class="fw-bold text-primary">BANKS IN TANZANIA (lookup values)</td>
                                    <td class="text-center">-</td>
                                    <td></td>
                                </tr>
                                
                                @foreach($banksTz as $index => $bank)
                                <tr>
                                    <td class="text-center">{{ $index + 2 }}</td>
                                    <td>{{ $bank }}</td>
                                    <td class="text-end">0.00</td>
                                    <td class="text-center">0</td>
                                </tr>
                                @endforeach

                                <!-- MFSP Section -->
                                <tr class="table-warning">
                                    <td class="text-center fw-bold">{{ count($banksTz) + 2 }}</td>
                                    <td class="fw-bold text-warning">MICROFINANCE INSTITUTIONS & COMMUNITY BANKS</td>
                                    <td class="text-center">-</td>
                                    <td></td>
                                </tr>
                                
                                @foreach($mfsp as $index => $mfspItem)
                                <tr>
                                    <td class="text-center">{{ count($banksTz) + 3 + $index }}</td>
                                    <td>{{ $mfspItem }}</td>
                                    <td class="text-end">0.00</td>
                                    <td class="text-center">0</td>
                                </tr>
                                @endforeach

                                <!-- MNOs Section -->
                                <tr class="table-success">
                                    <td class="text-center fw-bold">{{ count($banksTz) + count($mfsp) + 3 }}</td>
                                    <td class="fw-bold text-success">MOBILE NETWORK OPERATORS (MNOS)</td>
                                    <td class="text-center">-</td>
                                    <td></td>
                                </tr>
                                
                                @foreach($mnos as $index => $mno)
                                <tr>
                                    <td class="text-center">{{ count($banksTz) + count($mfsp) + 4 + $index }}</td>
                                    <td>{{ $mno }}</td>
                                    <td class="text-end">0.00</td>
                                    <td class="text-center">0</td>
                                </tr>
                                @endforeach

                                <!-- Empty rows to reach 30 -->
                                @php
                                    $currentRow = count($banksTz) + count($mfsp) + count($mnos) + 4;
                                    $remainingRows = 30 - $currentRow;
                                @endphp
                                
                                @for($i = 0; $i < $remainingRows; $i++)
                                <tr>
                                    <td class="text-center">{{ $currentRow + $i }}</td>
                                    <td></td>
                                    <td class="text-end"></td>
                                    <td class="text-center">0</td>
                                </tr>
                                @endfor

                                <!-- Total Row -->
                                <tr class="table-dark">
                                    <td class="text-center fw-bold">30</td>
                                    <td class="fw-bold text-white">TOTAL BALANCE</td>
                                    <td class="text-end fw-bold text-white">0.00</td>
                                    <td class="text-center text-white">0 C30=MSP2_01C5</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            <strong>Note:</strong> This report shows agent banking balances held in various banks and financial institutions. 
                            Balances should reflect actual amounts as of the quarter end date.
                        </small>
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

.bot-table td {
    padding: 6px 4px;
    vertical-align: middle;
}

.bot-table .table-info {
    background-color: #d1ecf1 !important;
}

.bot-table .table-warning {
    background-color: #fff3cd !important;
}

.bot-table .table-success {
    background-color: #d4edda !important;
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

.text-primary {
    color: #007bff !important;
}

.text-warning {
    color: #856404 !important;
}

.text-success {
    color: #155724 !important;
}

.text-white {
    color: white !important;
}
</style>
@endsection 