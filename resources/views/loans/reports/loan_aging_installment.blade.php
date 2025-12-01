@extends('layouts.main')

@section('title', 'Loan Aging Installment Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
              ['label' => 'Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
            ['label' => 'Loan Aging Installment Report', 'url' => '#', 'icon' => 'bx bx-calendar-check']
        ]" />
        <h6 class="mb-0 text-uppercase">LOAN AGING INSTALLMENT REPORT</h6>
        <hr />

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bx bx-calendar-check me-2"></i>Loan Aging Installment Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.loans.reports.loan_aging_installment') }}">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="as_of_date" class="form-label">As of Date</label>
                            <input type="date" class="form-control" id="as_of_date" name="as_of_date" value="{{ request('as_of_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select class="form-select" id="branch_id" name="branch_id">
                                @if(($branches->count() ?? 0) > 1)
                                    <option value="all" {{ request('branch_id') === 'all' ? 'selected' : '' }}>All My Branches</option>
                                @endif
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="loan_officer_id" class="form-label">Loan Officer</label>
                            <select class="form-select" id="loan_officer_id" name="loan_officer_id">
                                <option value="">All Loan Officers</option>
                                @foreach($loanOfficers as $officer)
                                    <option value="{{ $officer->id }}" {{ request('loan_officer_id') == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(isset($agingData))
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Installment Aging Summary</h5>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('accounting.loans.reports.loan_aging_installment.export_excel') }}" class="d-inline">
                        <input type="hidden" name="as_of_date" value="{{ request('as_of_date') }}">
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        <input type="hidden" name="loan_officer_id" value="{{ request('loan_officer_id') }}">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bx bx-download me-1"></i> Excel
                        </button>
                    </form>
                    <form method="GET" action="{{ route('accounting.loans.reports.loan_aging_installment.export_pdf') }}" class="d-inline">
                        <input type="hidden" name="as_of_date" value="{{ request('as_of_date') }}">
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        <input type="hidden" name="loan_officer_id" value="{{ request('loan_officer_id') }}">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bx bx-download me-1"></i> PDF
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                             <tr>
                                <th>Customer</th>
                                <th>Customer No</th>
                                <th>Phone</th>
                                <th>Loan No</th>
                                <th>Loan Amount</th>
                                <th>Installment Amount</th>
                                <th>Disbursed Date</th>
                                <th>Expiry</th>
                                <th>Branch</th>
                                <th>Loan Officer</th>
                                <th>Current</th>
                                <th>1-30 Days</th>
                                <th>31-60 Days</th>
                                <th>61-90 Days</th>
                                <th>91+ Days</th>
                                <th>Total Due Principal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agingData as $row)
                                <tr>
                                    <td>{{ $row['customer'] }}</td>
                                    <td>{{ $row['customer_no'] }}</td>
                                    <td>{{ $row['phone'] }}</td>
                                    <td>{{ $row['loan_no'] }}</td>
                                    <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['installment_amount'], 2) }}</td>
                                    <td>{{ $row['disbursed_no'] }}</td>
                                    <td>{{ $row['expiry'] }}</td>
                                    <td>{{ $row['branch'] }}</td>
                                    <td>{{ $row['loan_officer'] }}</td>
                                    <td class="text-end">{{ number_format($row['current'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['bucket_1_30'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['bucket_31_60'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['bucket_61_90'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['bucket_91_plus'], 2) }}</td>
                                    <td class="text-end fw-bold text-primary">{{ number_format($row['total_overdue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="text-center text-muted">No installment aging data found for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
