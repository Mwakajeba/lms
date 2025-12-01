

@extends('layouts.main')

@section('title', 'Non Performing Loan Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
            ['label' => 'NPL Report', 'url' => '#', 'icon' => 'bx bx-error-circle']
        ]" />
        <h6 class="mb-0 text-uppercase">NON PERFORMING LOAN REPORT</h6>
        <hr />

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bx bx-error-circle me-2"></i>Non Performing Loan Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.loans.reports.npl') }}">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="as_of_date" class="form-label">As of Date</label>
                            <input type="date" class="form-control" id="as_of_date" name="as_of_date" value="{{ $asOfDate ?? '' }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select class="form-select" id="branch_id" name="branch_id">
                                @if(($branches->count() ?? 0) > 1)
                                    <option value="all" {{ ($branchId ?? '') === 'all' ? 'selected' : '' }}>All My Branches</option>
                                @endif
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ ($branchId ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="loan_officer_id" class="form-label">Loan Officer</label>
                            <select class="form-select select2-single" id="loan_officer_id" name="loan_officer_id">
                                <option value="">All Loan Officers</option>
                                @foreach($loanOfficers as $officer)
                                    <option value="{{ $officer->id }}" {{ ($loanOfficerId ?? '') == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- groups --}}
                        <div class="col-md-3 mb-3">
                            <label for="group_id" class="form-label">Group</label>
                            <select class="form-select select2-single" id="group_id" name="group_id">
                                <option value="">All Groups</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ (request()->get('group_id') == $group->id) ? 'selected' : '' }}>{{ $group->name }}</option>
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

        @if($showData)
        <!-- Dashboard Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($nplSummary['total_npl_loans'] ?? 0) }}</h3>
                                <p class="mb-0">NPL Loans</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-error-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">TZS {{ number_format($nplSummary['total_npl_amount'] ?? 0, 2) }}</h3>
                                <p class="mb-0">NPL Amount</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-money bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($nplSummary['average_dpd'] ?? 0, 1) }}</h3>
                                <p class="mb-0">Avg DPD</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-time bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($nplSummary['provision_total'] ?? 0, 2) }}</h3>
                                <p class="mb-0">Total Provision</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-shield bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NPL Table -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>NPL Loans Details</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('accounting.loans.reports.npl.export_excel', request()->all()) }}" class="btn btn-success btn-sm">
                        <i class="bx bx-download me-1"></i> Excel
                    </a>
                    <a href="{{ route('accounting.loans.reports.npl.export_pdf', request()->all()) }}" class="btn btn-danger btn-sm">
                        <i class="bx bx-download me-1"></i> PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Date Of</th>
                                <th>Branch</th>
                                <th>Loan Officer</th>
                                <th>Loan ID</th>
                                <th>Borrower</th>
                                <th>Disbursed Date</th>
                                <th>Last Payment</th>
                                <th>Total Outstanding (TZS)</th>
                                <th>NPL Outstanding (TZS)</th>
                                <th>DPD</th>
                                <th>Classification</th>
                                <th>Provision %</th>
                                <th>Provision (TZS)</th>
                                <th>Collateral</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($nplData) && count($nplData) > 0)
                                @foreach($nplData as $row)
                                    <tr>
                                        <td>{{ $row['date_of'] }}</td>
                                        <td>{{ $row['branch'] }}</td>
                                        <td>{{ $row['loan_officer'] }}</td>
                                        <td>{{ $row['loan_id'] }}</td>
                                        <td>{{ $row['borrower'] }}</td>
                                        <td>{{ $row['disbursed_date'] ?? 'N/A' }}</td>
                                        <td>{{ $row['last_payment_date'] ?? 'N/A' }}</td>
                                        <td class="text-end">{{ number_format($row['outstanding']) }}</td>
                                        <td class="text-end text-danger fw-bold">{{ number_format($row['npl_outstanding'] ?? $row['outstanding']) }}</td>
                                        <td class="text-end">
                                            <span class="badge
                                                @if($row['dpd'] <= 30) bg-warning
                                                @elseif($row['dpd'] <= 60) bg-orange
                                                @elseif($row['dpd'] <= 90) bg-danger
                                                @else bg-dark
                                                @endif">
                                                {{ $row['dpd'] }} days
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge
                                                @if($row['classification'] == 'Loss') bg-danger
                                                @elseif($row['classification'] == 'Doubtful') bg-warning
                                                @elseif($row['classification'] == 'Substandard') bg-info
                                                @else bg-success
                                                @endif">
                                                {{ $row['classification'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $row['provision_percent'] }}</td>
                                        <td class="text-end">{{ number_format($row['provision_amount']) }}</td>
                                        <td>{{ $row['collateral'] ?: 'None' }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ $row['status'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="15" class="text-center text-muted">No NPL loans found for the selected criteria.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
