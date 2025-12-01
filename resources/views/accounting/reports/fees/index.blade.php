@extends('layouts.main')

@section('title', 'Fees Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Accounting Reports', 'url' => route('accounting.reports.index'), 'icon' => 'bx bx-calculator'],
                ['label' => 'Fees Report', 'url' => '#', 'icon' => 'bx bx-money']
            ]" />
            <h6 class="mb-0 text-uppercase">FEES REPORT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Fees Report - GL Transactions</h4>

                            <!-- Filter Form -->
                            <form method="GET" action="{{ route('accounting.reports.fees') }}" id="feesReportForm">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <label for="start_date" class="form-label">Date From</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="{{ $startDate }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="end_date" class="form-label">Date To</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                               value="{{ $endDate }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="branch_id" class="form-label">Branch</label>
                                        <select class="form-select" id="branch_id" name="branch_id">
                                            @if(($branches->count() ?? 0) > 1)
                                                <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All My Branches</option>
                                            @endif
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="fee_id" class="form-label">Fee Type</label>
                                        <select class="form-select" id="fee_id" name="fee_id">
                                            <option value="all">All Fees</option>
                                            @foreach($fees as $fee)
                                                <option value="{{ $fee->id }}" {{ $feeId == $fee->id ? 'selected' : '' }}>
                                                    {{ $fee->name }} ({{ $fee->fee_type }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mb-4">
                                    <div class="col-12 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bx bx-search me-1"></i> Generate Report
                                        </button>
                                        <a href="{{ route('accounting.reports.fees') }}" class="btn btn-outline-secondary me-2">
                                            <i class="bx bx-refresh me-1"></i> Reset
                                        </a>
                                        <button type="button" class="btn btn-success me-2" onclick="exportExcel()">
                                            <i class="bx bx-download me-1"></i> Export Excel
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="exportPdf()">
                                            <i class="bx bx-file-pdf me-1"></i> Export PDF
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h5 class="card-title text-primary">Total Debit</h5>
                                            <h3 class="mb-0">{{ number_format($feesData['summary']['total_debit'], 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h5 class="card-title text-success">Total Credit</h5>
                                            <h3 class="mb-0">{{ number_format($feesData['summary']['total_credit'], 2) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h5 class="card-title text-info">Total Transactions</h5>
                                            <h3 class="mb-0">{{ number_format($feesData['summary']['total_transactions']) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h5 class="card-title text-warning">Unique Fees</h5>
                                            <h3 class="mb-0">{{ number_format($feesData['summary']['unique_fees']) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Table -->
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Fee Name</th>
                                            <th>Chart Account</th>
                                            <th>Account Code</th>
                                            <th>Customer</th>
                                            <th>Branch</th>
                                            <th>Nature</th>
                                            <th class="text-end">Amount</th>
                                            <th>Description</th>
                                            <th>Reference ID</th>
                                            <th>Transaction Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($feesData['data'] as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}</td>
                                                <td>{{ $item->fee_name }}</td>
                                                <td>{{ $item->chart_account_name }}</td>
                                                <td>{{ $item->account_code }}</td>
                                                <td>{{ $item->customer_name ?? 'N/A' }}</td>
                                                <td>{{ $item->branch_name ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge {{ $item->nature == 'debit' ? 'bg-danger' : 'bg-success' }}">
                                                        {{ ucfirst($item->nature) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                                <td>{{ $item->description }}</td>
                                                <td>{{ $item->reference_id }}</td>
                                                <td>{{ $item->transaction_type }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center">No fees transactions found for the selected criteria.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                    <tfoot class="table-dark">
                                        <tr>
                                            <td colspan="8" class="text-end"><strong>BALANCE:</strong></td>
                                            <td class="text-end"><strong>{{ number_format($feesData["summary"]["balance"], 2) }}</strong></td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportExcel() {
            const form = document.getElementById('feesReportForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const exportUrl = '{{ route("accounting.reports.fees.export") }}?' + params.toString();
            window.open(exportUrl, '_blank');
        }

        function exportPdf() {
            const form = document.getElementById('feesReportForm');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const exportUrl = '{{ route("accounting.reports.fees.export-pdf") }}?' + params.toString();
            window.open(exportUrl, '_blank');
        }
    </script>
@endsection
