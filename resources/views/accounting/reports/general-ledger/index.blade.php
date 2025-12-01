@extends('layouts.main')

@section('title', 'General Ledger Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'General Ledger Report', 'url' => '#', 'icon' => 'bx bx-book-open']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-book-open me-2"></i>General Ledger Report</h5>
                                <small class="text-muted">Generate detailed general ledger for the specified period</small>
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
                        <form id="generalLedgerForm" method="GET" action="{{ route('accounting.reports.general-ledger') }}">
                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" 
                                           value="{{ $startDate }}" required>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" 
                                           value="{{ $endDate }}" required>
                                </div>

                                <!-- Report Type -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="report_type" class="form-label">Report Type</label>
                                    <select class="form-select" id="report_type" name="report_type" required>
                                        <option value="accrual" {{ $reportType === 'accrual' ? 'selected' : '' }}>Accrual Basis</option>
                                        <option value="cash" {{ $reportType === 'cash' ? 'selected' : '' }}>Cash Basis</option>
                                    </select>
                                </div>

                                <!-- Account -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="account_id" class="form-label">Account</label>
                                    <select class="form-select select2-single" id="account_id" name="account_id">
                                        <option value="">All Accounts</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_code }} - {{ $account->account_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
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

                                <!-- Show Opening Balance -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="show_opening_balance" class="form-label">Show Opening Balance</label>
                                    <select class="form-select" id="show_opening_balance" name="show_opening_balance">
                                        <option value="1" {{ $showOpeningBalance ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ !$showOpeningBalance ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>

                                <!-- Group By -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="group_by" class="form-label">Group By</label>
                                    <select class="form-select" id="group_by" name="group_by">
                                        <option value="account" {{ $groupBy === 'account' ? 'selected' : '' }}>Account</option>
                                        <option value="date" {{ $groupBy === 'date' ? 'selected' : '' }}>Date</option>
                                        <option value="voucher" {{ $groupBy === 'voucher' ? 'selected' : '' }}>Voucher</option>
                                    </select>
                                </div>
                            </div>
                        </form>

                        @if(isset($generalLedgerData))
                        <!-- Summary Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Report Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h4 class="text-primary">{{ number_format($generalLedgerData['summary']['total_debit'], 2) }}</h4>
                                                    <small class="text-muted">Total Debit</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h4 class="text-danger">{{ number_format($generalLedgerData['summary']['total_credit'], 2) }}</h4>
                                                    <small class="text-muted">Total Credit</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h4 class="text-{{ $generalLedgerData['summary']['net_movement'] >= 0 ? 'success' : 'danger' }}">
                                                        {{ number_format($generalLedgerData['summary']['net_movement'], 2) }}
                                                    </h4>
                                                    <small class="text-muted">Net Movement</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h4 class="text-info">{{ $generalLedgerData['summary']['transaction_count'] }}</h4>
                                                    <small class="text-muted">Transactions</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Results -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">GENERAL LEDGER</h6>
                                                <small class="text-muted">
                                                    @if($startDate === $endDate)
                                                        As at: {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} | 
                                                    @else
                                                        Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} | 
                                                    @endif
                                                    Basis: {{ ucfirst($reportType) }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($generalLedgerData) && count($generalLedgerData['transactions']) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped" id="generalLedgerTable">
                                                    <thead>
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Account Code</th>
                                                            <th>Account Name</th>
                                                            <th>Customer</th>
                                                            <th>Transaction ID</th>
                                                            <th>Description</th>
                                                            <th class="text-end">Debit</th>
                                                            <th class="text-end">Credit</th>
                                                            <th class="text-end">Balance</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $currentAccount = null;
                                                            $accountTotalDebit = 0;
                                                            $accountTotalCredit = 0;
                                                        @endphp

                                                        @foreach($generalLedgerData['transactions'] as $transaction)
                                                            @if($currentAccount !== $transaction->chart_account_id)
                                                                @if($currentAccount !== null)
                                                                    <!-- Account Total Row -->
                                                                    @php
                                                                        $previousTransaction = $generalLedgerData['transactions'][$loop->index - 1] ?? null;
                                                                    @endphp
                                                                    <tr class="table-secondary">
                                                                        <td colspan="6"><strong>Total for {{ $previousTransaction->account_code }} - {{ $previousTransaction->account_name }}</strong></td>
                                                                        <td class="text-end"><strong>{{ number_format($accountTotalDebit, 2) }}</strong></td>
                                                                        <td class="text-end"><strong>{{ number_format($accountTotalCredit, 2) }}</strong></td>
                                                                        <td class="text-end"><strong>{{ number_format($accountTotalDebit - $accountTotalCredit, 2) }}</strong></td>
                                                                    </tr>
                                                                @endif

                                                                @if($showOpeningBalance && isset($generalLedgerData['opening_balances'][$transaction->chart_account_id]))
                                                                    @php
                                                                        $openingBalance = $generalLedgerData['opening_balances'][$transaction->chart_account_id];
                                                                        $openingAmount = $openingBalance->total_debit - $openingBalance->total_credit;
                                                                    @endphp
                                                                    <tr class="table-info">
                                                                        <td>{{ \Carbon\Carbon::parse($startDate)->subDay()->format('M d, Y') }}</td>
                                                                        <td>{{ $transaction->account_code }}</td>
                                                                        <td>{{ $transaction->account_name }}</td>
                                                                        <td>N/A</td>
                                                                        <td>OPENING BALANCE</td>
                                                                        <td>Balance brought forward</td>
                                                                        <td class="text-end">{{ $openingAmount >= 0 ? number_format($openingAmount, 2) : '' }}</td>
                                                                        <td class="text-end">{{ $openingAmount < 0 ? number_format(abs($openingAmount), 2) : '' }}</td>
                                                                        <td class="text-end">{{ number_format($openingAmount, 2) }}</td>
                                                                    </tr>
                                                                @endif

                                                                @php
                                                                    $currentAccount = $transaction->chart_account_id;
                                                                    $accountTotalDebit = 0;
                                                                    $accountTotalCredit = 0;
                                                                @endphp
                                                            @endif

                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($transaction->date)->format('M d, Y') }}</td>
                                                                <td>{{ $transaction->account_code }}</td>
                                                                <td>{{ $transaction->account_name }}</td>
                                                                <td>{{ $transaction->customer_name ?? 'N/A' }}</td>
                                                                <td>{{ $transaction->transaction_id }}</td>
                                                                <td>{{ $transaction->description }}</td>
                                                                <td class="text-end">{{ $transaction->nature === 'debit' ? number_format($transaction->amount, 2) : '' }}</td>
                                                                <td class="text-end">{{ $transaction->nature === 'credit' ? number_format($transaction->amount, 2) : '' }}</td>
                                                                <td class="text-end">{{ number_format($transaction->running_balance, 2) }}</td>
                                                            </tr>

                                                            @php
                                                                if ($transaction->nature === 'debit') {
                                                                    $accountTotalDebit += $transaction->amount;
                                                                } else {
                                                                    $accountTotalCredit += $transaction->amount;
                                                                }
                                                            @endphp
                                                        @endforeach

                                                        @if($currentAccount !== null)
                                                            <!-- Final Account Total Row -->
                                                            @php
                                                                $lastTransaction = end($generalLedgerData['transactions']);
                                                            @endphp
                                                            <tr class="table-secondary">
                                                                <td colspan="6"><strong>Total for {{ $lastTransaction->account_code }} - {{ $lastTransaction->account_name }}</strong></td>
                                                                <td class="text-end"><strong>{{ number_format($accountTotalDebit, 2) }}</strong></td>
                                                                <td class="text-end"><strong>{{ number_format($accountTotalCredit, 2) }}</strong></td>
                                                                <td class="text-end"><strong>{{ number_format($accountTotalDebit - $accountTotalCredit, 2) }}</strong></td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="bx bx-info-circle fs-1 text-muted"></i>
                                                <p class="mt-2 text-muted">No general ledger data found for the selected criteria.</p>
                                            </div>
                                        @endif
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

<script>
function generateReport() {
    document.getElementById('generalLedgerForm').submit();
}

function exportReport(type) {
    const form = document.getElementById('generalLedgerForm');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    const url = '{{ route("accounting.reports.general-ledger.export") }}?' + new URLSearchParams(formData);
    
    // Show loading state
    Swal.fire({
        title: 'Generating Report...',
        text: 'Please wait while we prepare your ' + type.toUpperCase() + ' report.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Download the file
    window.location.href = url;

    // close the loading state after a short delay
    setTimeout(() => {
        Swal.close();
    }, 2000);
}

// Initialize DataTable
$(document).ready(function() {
    if ($('#generalLedgerTable').length) {
        $('#generalLedgerTable').DataTable({
            pageLength: 50,
            order: [[0, 'asc'], [1, 'asc']],
            columnDefs: [
                { targets: [5, 6, 7], className: 'text-end' }
            ]
        });
    }
    if ($('.select2-single').length && $.fn.select2) {
        $('.select2-single').select2({
            width: '100%',
            placeholder: 'All Accounts',
            allowClear: true
        });
    }
});
</script>
@endsection 