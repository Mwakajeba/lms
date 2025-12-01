@extends('layouts.main')

@section('title', 'Cash Book Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Accounting Reports', 'url' => route('reports.index'), 'icon' => 'bx bx-calculator'],
            ['label' => 'Cash Book Report', 'url' => '#', 'icon' => 'bx bx-book']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0"><i class="bx bx-book me-2"></i>Cash Book Report</h5>
                                <small class="text-muted">Generate cash book for the specified period</small>
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
                        <form id="cashBookForm" method="GET" action="{{ route('accounting.reports.cash-book') }}">
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

                                <!-- Bank Account -->
                                <div class="col-md-6 col-lg-3 mb-3">
                                    <label for="bank_account_id" class="form-label">Bank Account</label>
                                    <select class="form-select" id="bank_account_id" name="bank_account_id">
                                        <option value="all" {{ $bankAccountId === 'all' ? 'selected' : '' }}>All Bank Accounts</option>
                                        @foreach($bankAccounts as $bankAccount)
                                            <option value="{{ $bankAccount->id }}" {{ $bankAccountId == $bankAccount->id ? 'selected' : '' }}>
                                                {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                            </option>
                                        @endforeach
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
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bx bx-search me-1"></i>Generate Report
                                    </button>
                                </div>
                            </div>
                        </form>

                        @if(isset($cashBookData))
                        <!-- Results -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0">CASH BOOK</h6>
                                                <small class="text-muted">
                                                    @if($startDate === $endDate)
                                                        As at: {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} | 
                                                    @else
                                                        Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }} | 
                                                    @endif
                                                    Bank Account: {{ $bankAccountId === 'all' ? 'All Accounts' : $bankAccounts->where('id', $bankAccountId)->first()->name ?? 'N/A' }} |
                                                    Branch: 
                                                    @if(($branches->count() ?? 0) > 1 && $branchId === 'all')
                                                        All Branches
                                                    @else
                                                        {{ optional($branches->firstWhere('id', $branchId))->name ?? 'N/A' }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if(count($cashBookData['transactions']) > 0)
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <td colspan="9" class="text-center fw-bold fs-5">
                                                                {{ $user->company->name ?? 'SmartFinance' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="9" class="text-center fw-bold">
                                                                CASH BOOK
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="9" class="text-center fw-bold">
                                                                @if($startDate === $endDate)
                                                                    AS AT {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
                                                                @else
                                                                    FROM {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} TO {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="text-center">DATE</th>
                                                            <th class="text-center">DESCRIPTION</th>
                                                            <th class="text-center">CUSTOMER</th>
                                                            <th class="text-center">BANK ACCOUNT</th>
                                                            <th class="text-center">TRANSACTION NO</th>
                                                            <th class="text-center">REFERENCE NO.</th>
                                                            <th class="text-center">DEBIT</th>
                                                            <th class="text-center">CREDIT</th>
                                                            <th class="text-center">BALANCE</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="7" class="text-end fw-bold">Opening Balance</td>
                                                            <td></td>
                                                            <td class="text-end fw-bold">{{ number_format($cashBookData['opening_balance'], 2) }}</td>
                                                        </tr>

                                                        @php
                                                            $running_balance = $cashBookData['opening_balance'];
                                                            $total_receipts = 0;
                                                            $total_payments = 0;
                                                        @endphp

                                                        @foreach($cashBookData['transactions'] as $transaction)
                                                            @php
                                                                $debit = $transaction['debit'];
                                                                $credit = $transaction['credit'];

                                                                $total_receipts += $debit;
                                                                $total_payments += $credit;

                                                                $running_balance += $debit - $credit;
                                                            @endphp

                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('d/m/Y') }}</td>
                                                                <td class="text-start">{{ $transaction['description'] }}</td>
                                                                <td class="text-start">{{ $transaction['customer_name'] }}</td>
                                                                <td class="text-start">{{ $transaction['bank_account'] }}</td>
                                                                <td>{{ $transaction['transaction_no'] }}</td>
                                                                <td>{{ $transaction['reference_no'] }}</td>
                                                                <td class="text-end">{{ $debit > 0 ? number_format($debit, 2) : '' }}</td>
                                                                <td class="text-end">{{ $credit > 0 ? number_format($credit, 2) : '' }}</td>
                                                                <td class="text-end">{{ number_format($running_balance, 2) }}</td>
                                                            </tr>
                                                        @endforeach

                                                        <tr>
                                                            <td colspan="6" class="text-end fw-bold">Total Debit</td>
                                                            <td class="text-end fw-bold">{{ number_format($total_receipts, 2) }}</td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6" class="text-end fw-bold">Total Credit</td>
                                                            <td></td>
                                                            <td class="text-end fw-bold">{{ number_format($total_payments, 2) }}</td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="6" class="text-end fw-bold">Final Balance</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td class="text-end fw-bold">{{ number_format($running_balance, 2) }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="8" class="text-end fw-bold">Closing Balance</td>
                                                            <td class="text-end fw-bold">{{ number_format($running_balance, 2) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="bx bx-info-circle fs-1 text-muted"></i>
                                                <p class="mt-2 text-muted">No cash book data found for the selected criteria.</p>
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
    document.getElementById('cashBookForm').submit();
}

function exportReport(type) {
    const form = document.getElementById('cashBookForm');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    const url = '{{ route("accounting.reports.cash-book.export") }}?' + new URLSearchParams(formData);
    
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