@extends('layouts.main')

@section('title', 'Loan Portfolio Tracking')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
                ['label' => 'Loan Portfolio Tracking', 'url' => '#', 'icon' => 'bx bx-line-chart']
            ]" />

        <h6 class="mb-0 text-uppercase">Loan Portfolio Tracking</h6>
        <hr />

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('loans.reports.portfolio_tracking') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="from_date" value="{{ $fromDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" name="to_date" value="{{ $toDate }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Branch</label>
                        <select class="form-select" name="branch_id">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>
                                    {{ $b->name ?? $b->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Loan Officer</label>
                        <select class="form-select" name="loan_officer_id">
                            <option value="">All</option>
                            @foreach($loanOfficers as $o)
                                <option value="{{ $o->id }}" {{ $loanOfficerId == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Group By</label>
                        <select class="form-select" name="group_by">
                            <option value="day" {{ $groupBy=='day'?'selected':'' }}>Day</option>
                            <option value="week" {{ $groupBy=='week'?'selected':'' }}>Week</option>
                            <option value="month" {{ $groupBy=='month'?'selected':'' }}>Month</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary"><i class="bx bx-search me-1"></i> Apply</button>
                        @if($showData)
                        <a href="{{ route('loans.reports.portfolio_tracking.export_excel', request()->all()) }}" class="btn btn-success"><i class="bx bx-download me-1"></i> Excel</a>
                        <a href="{{ route('loans.reports.portfolio_tracking.export_pdf', request()->all()) }}" class="btn btn-danger"><i class="bx bx-file me-1"></i> PDF</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        @if($showData)
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Group</th>
                            @if($groupBy !== 'day')
                            <th>Date Range</th>
                            @endif
                            <th>Customer Name</th>
                            <th>Loan Officer</th>
                            <th>Loan Product</th>
                            <th>Loan Account No.</th>
                            <th>Disbursement Date</th>
                            <th>Maturity Date</th>
                            <th class="text-end">Amount Disbursed</th>
                            <th class="text-end">Interest</th>
                            <th class="text-end">Total Amount (P+I)</th>
                            <th class="text-end">Principal Paid</th>
                            <th class="text-end">Interest Paid</th>
                            <th class="text-end">Penalties Paid</th>
                            <th class="text-end">Outstanding Principal</th>
                            <th class="text-end">Outstanding Interest</th>
                            <th class="text-end">Amount Overdue</th>
                            <th class="text-end">Days in Arrears</th>
                            <th>Loan Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trackingData as $r)
                        <tr class="{{ isset($r['is_summary']) && $r['is_summary'] ? 'table-warning fw-bold' : '' }}">
                            <td>{{ $r['group'] }}</td>
                            @if($groupBy !== 'day')
                            <td>{{ $r['date_range'] ?? '' }}</td>
                            @endif
                            <td>{{ $r['customer_name'] }}</td>
                            <td>{{ $r['loan_officer'] }}</td>
                            <td>{{ $r['loan_product'] }}</td>
                            <td>{{ $r['loan_account_no'] }}</td>
                            <td>{{ $r['disbursement_date'] }}</td>
                            <td>{{ $r['maturity_date'] }}</td>
                            <td class="text-end">{{ number_format($r['amount_disbursed'], 2) }}</td>
                            <td class="text-end">{{ number_format($r['interest'], 2) }}</td>
                            <td class="text-end">{{ number_format($r['total_amount'], 2) }}</td>
                            <td class="text-end">{{ number_format($r['principal_paid'], 2) }}</td>
                            <td class="text-end">{{ number_format($r['interest_paid'], 2) }}</td>
                            <td class="text-end">{{ number_format($r['penalties_paid'], 2) }}</td>
                            <td class="text-end">{{ number_format($r['outstanding_principal'], 2) }}</td>
                            <td class="text-end">{{ number_format($r['outstanding_interest'], 2) }}</td>
                            <td class="text-end">{{ number_format($r['amount_overdue'], 2) }}</td>
                            <td class="text-end">{{ $r['days_in_arrears'] }}</td>
                            <td>{{ ucfirst($r['loan_status']) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $groupBy !== 'day' ? '19' : '18' }}" class="text-center text-muted py-4">No data found for selected filters.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection


