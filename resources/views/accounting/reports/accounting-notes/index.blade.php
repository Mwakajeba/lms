@extends('layouts.main')

@section('title', 'Accounting Notes Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Accounting Notes Report', 'url' => '#', 'icon' => 'bx bx-note']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-note me-2"></i>Accounting Notes Report</h5>
                                <small class="text-muted">Generate accounting notes and policies report</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success" onclick="generateReport()">
                                    <i class="bx bx-refresh me-1"></i> Generate Report
                                </button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')">
                                            <i class="bx bx-file-pdf me-2"></i> Export PDF
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">
                                            <i class="bx bx-file me-2"></i> Export Excel
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section -->
                        <form id="accountingNotesForm" method="GET" action="{{ route('accounting.reports.accounting-notes') }}">
                            <div class="row">
                                <!-- As of Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="as_of_date" class="form-label">As of Date</label>
                                    <input type="date" class="form-control" id="as_of_date" name="as_of_date" 
                                           value="{{ $asOfDate }}" required>
                                </div>

                                <!-- Reporting Type -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="reporting_type" class="form-label">Reporting Type</label>
                                    <select class="form-select" id="reporting_type" name="reporting_type" required>
                                        <option value="accrual" {{ $reportingType === 'accrual' ? 'selected' : '' }}>Accrual Basis</option>
                                        <option value="cash" {{ $reportingType === 'cash' ? 'selected' : '' }}>Cash Basis</option>
                                    </select>
                                </div>

                                <!-- Branch (Assigned) -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select" id="branch_id" name="branch_id">
                                        @if(($branches->count() ?? 0) > 1)
                                            <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All Branches</option>
                                        @endif
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Level of Detail -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="level_of_detail" class="form-label">Level of Detail</label>
                                    <select class="form-select" id="level_of_detail" name="level_of_detail">
                                        <option value="detailed" {{ $levelOfDetail === 'detailed' ? 'selected' : '' }}>Detailed</option>
                                        <option value="summary" {{ $levelOfDetail === 'summary' ? 'selected' : '' }}>Summary</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bx bx-search me-1"></i>Generate Report
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if(isset($accountingNotesData))
                        <!-- Results -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">ACCOUNT CLASSES REPORT</h6>
                                                <small class="text-muted">
                                                    As at: {{ \Carbon\Carbon::parse($asOfDate)->format('M d, Y') }} | 
                                                    Basis: {{ ucfirst($reportingType) }} | 
                                                    Detail: {{ ucfirst($levelOfDetail) }} |
                                                    Branch: @if(($branches->count() ?? 0) > 1 && $branchId === 'all') All Branches @else {{ optional($branches->firstWhere('id', $branchId))->name ?? 'N/A' }} @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Summary Statistics -->
                                        <div class="row mb-4">
                                            <div class="col-md-3">
                                                <div class="card bg-primary text-white">
                                                    <div class="card-body text-center">
                                                        <h4>{{ $accountingNotesData['account_classes_data']['summary']['total_classes'] }}</h4>
                                                        <small>Account Classes</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card bg-success text-white">
                                                    <div class="card-body text-center">
                                                        <h4>{{ $accountingNotesData['account_classes_data']['summary']['total_groups'] }}</h4>
                                                        <small>Account Groups</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card bg-info text-white">
                                                    <div class="card-body text-center">
                                                        <h4>{{ $accountingNotesData['account_classes_data']['summary']['total_accounts'] }}</h4>
                                                        <small>Chart Accounts</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card bg-warning text-white">
                                                    <div class="card-body text-center">
                                                        <h4>{{ number_format($accountingNotesData['account_classes_data']['summary']['total_transactions']) }}</h4>
                                                        <small>Transactions</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Account Classes Hierarchical View -->
                                        <div class="account-classes-hierarchy">
                                            @php
                                                $groupedData = $accountingNotesData['account_classes_data']['data']->groupBy('class_name');
                                            @endphp
                                            
                                            @forelse($groupedData as $className => $classData)
                                                <!-- Account Class Section -->
                                                <div class="account-class-section mb-4">
                                                    <div class="card">
                                                        <div class="card-header bg-primary text-white">
                                                            <h6 class="mb-0">
                                                                <i class="bx bx-category me-2"></i>{{ $className }}:
                                                            </h6>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            @php
                                                $groupedByGroup = $classData->groupBy('group_name');
                                            @endphp
                                            
                                            @foreach($groupedByGroup as $groupName => $groupData)
                                                <!-- Account Group Section -->
                                                <div class="account-group-section">
                                                    <div class="group-header bg-light p-3 border-bottom">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h6 class="mb-0 text-secondary">
                                                                <i class="bx bx-folder me-2"></i>{{ $groupName }}
                                                            </h6>
                                                            @php
                                                                $groupTotalDebit = $groupData->sum('total_debit');
                                                                $groupTotalCredit = $groupData->sum('total_credit');
                                                                $groupNetAmount = $groupTotalDebit - $groupTotalCredit;
                                                                $groupAccountCount = $groupData->sum('account_count');
                                                                $groupTransactionCount = $groupData->sum('transaction_count');
                                                            @endphp
                                                            <div class="group-totals" style="font-size: 12px;">
                                                                <span style="font-weight: bold;">D: {{ number_format($groupTotalDebit, 2) }}</span> | 
                                                                <span style="font-weight: bold;">C: {{ number_format($groupTotalCredit, 2) }}</span> | 
                                                                <span style="font-weight: bold; color: #28a745;">Net: {{ number_format($groupNetAmount, 2) }}</span> | 
                                                                <span style="font-weight: bold;"> {{ $groupTransactionCount }} Transactions</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    @if($levelOfDetail === 'detailed')
                                                        <!-- Detailed View - Show Chart Accounts -->
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-borderless mb-0">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th style="width: 15%">Account Code</th>
                                                                        <th style="width: 35%">Account Name</th>
                                                                        <th class="text-end" style="width: 12%">Total Debit</th>
                                                                        <th class="text-end" style="width: 12%">Total Credit</th>
                                                                        <th class="text-end" style="width: 12%">Net Amount</th>
                                                                        <th class="text-center" style="width: 14%">Transactions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($groupData as $item)
                                                                        <tr>
                                                                            <td><code>{{ $item->account_code }}</code></td>
                                                                            <td>{{ $item->account_name }}</td>
                                                                            <td class="text-end">{{ number_format($item->total_debit, 2) }}</td>
                                                                            <td class="text-end">{{ number_format($item->total_credit, 2) }}</td>
                                                                            <td class="text-end fw-bold">{{ number_format($item->net_amount, 2) }}</td>
                                                                            <td class="text-center">
                                                                                <span class="badge bg-info">{{ $item->transaction_count }}</span>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <!-- Summary View - Show Group Totals -->
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-borderless mb-0">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th class="text-end" style="width: 25%">Total Debit</th>
                                                                        <th class="text-end" style="width: 25%">Total Credit</th>
                                                                        <th class="text-end" style="width: 25%">Net Amount</th>
                                                                        <th class="text-center" style="width: 25%">Accounts | Transactions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @php
                                                                        $groupTotalDebit = $groupData->sum('total_debit');
                                                                        $groupTotalCredit = $groupData->sum('total_credit');
                                                                        $groupNetAmount = $groupTotalDebit - $groupTotalCredit;
                                                                        $groupAccountCount = $groupData->sum('account_count');
                                                                        $groupTransactionCount = $groupData->sum('transaction_count');
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="text-end">{{ number_format($groupTotalDebit, 2) }}</td>
                                                                        <td class="text-end">{{ number_format($groupTotalCredit, 2) }}</td>
                                                                        <td class="text-end fw-bold">{{ number_format($groupNetAmount, 2) }}</td>
                                                                        <td class="text-center">
                                                                            <span class="badge bg-primary me-1">{{ $groupAccountCount }}</span>
                                                                            <span class="badge bg-info">{{ $groupTransactionCount }}</span>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">
                                    <i class="bx bx-info-circle me-2"></i>No account classes data found for the selected criteria
                                </div>
                            @endforelse
                                        </div>

                                        <!-- Summary Totals -->
                                        <div class="row mt-4">
                                            <div class="col-12">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <div class="row text-center">
                                                            <div class="col-md-3">
                                                <h6 class="text-muted">Total Debit</h6>
                                                <h5 class="text-primary">{{ number_format($accountingNotesData['account_classes_data']['summary']['total_debit'], 2) }}</h5>
                                            </div>
                                            <div class="col-md-3">
                                                <h6 class="text-muted">Total Credit</h6>
                                                <h5 class="text-success">{{ number_format($accountingNotesData['account_classes_data']['summary']['total_credit'], 2) }}</h5>
                                            </div>
                                            <div class="col-md-3">
                                                <h6 class="text-muted">Net Amount</h6>
                                                <h5 class="text-info">{{ number_format($accountingNotesData['account_classes_data']['summary']['total_net'], 2) }}</h5>
                                            </div>
                                            <div class="col-md-3">
                                                <h6 class="text-muted">Average per Account</h6>
                                                <h5 class="text-warning">{{ $accountingNotesData['account_classes_data']['summary']['total_accounts'] > 0 ? number_format($accountingNotesData['account_classes_data']['summary']['total_net'] / $accountingNotesData['account_classes_data']['summary']['total_accounts'], 2) : '0.00' }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.account-classes-hierarchy .account-class-section {
    margin-bottom: 2rem;
}

.account-classes-hierarchy .account-group-section {
    border-left: 3px solid #e9ecef;
    margin-left: 1rem;
    margin-bottom: 1rem;
}

.account-classes-hierarchy .group-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 3px solid #007bff;
}

.account-classes-hierarchy .table-sm td,
.account-classes-hierarchy .table-sm th {
    padding: 0.5rem;
    border: none;
    border-bottom: 1px solid #f1f3f4;
}

.account-classes-hierarchy .table-sm tbody tr:hover {
    background-color: #f8f9fa;
}

.account-classes-hierarchy code {
    background-color: #f8f9fa;
    color: #495057;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
}

.account-classes-hierarchy .badge {
    font-size: 0.75em;
}

.account-classes-hierarchy .group-totals {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.account-classes-hierarchy .group-totals .badge {
    font-size: 0.7em;
    padding: 0.25rem 0.5rem;
}
</style>

<script>
function generateReport() {
    document.getElementById('accountingNotesForm').submit();
}

function exportReport(type) {
    const form = document.getElementById('accountingNotesForm');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    const url = '{{ route("accounting.reports.accounting-notes.export") }}?' + new URLSearchParams(formData);
    
    // Show loading state
    Swal.fire({
        title: 'Generating Report...',
        text: 'Please wait while we prepare your ' + type.toUpperCase() + ' report.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create a hidden iframe to handle the download
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = url;
    
    // Add iframe to document
    document.body.appendChild(iframe);
    
    // Set a timeout to close the loading dialog after a reasonable time
    setTimeout(() => {
        Swal.close();
        // Remove the iframe after a delay
        setTimeout(() => {
            if (document.body.contains(iframe)) {
                document.body.removeChild(iframe);
            }
        }, 1000);
    }, 3000);
}
</script>
@endsection 