@extends('layouts.main')

@section('title', 'Loan Delinquency Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
            ['label' => 'Delinquency Report', 'url' => '#', 'icon' => 'bx bx-error-circle']
        ]" />
        <h6 class="mb-0 text-uppercase">LOAN DELINQUENCY REPORT</h6>
        <hr />

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bx bx-error-circle me-2"></i>Loan Delinquency Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.loans.reports.delinquency') }}">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <label for="as_of_date" class="form-label">As of Date</label>
                            <input type="date" class="form-control" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="branch_id" class="form-label">Branch</label>
                            <select class="form-select" id="branch_id" name="branch_id">
                                @if(($branches->count() ?? 0) > 1)
                                    <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All My Branches</option>
                                @endif
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="loan_officer_id" class="form-label">Loan Officer</label>
                            <select class="form-select" id="loan_officer_id" name="loan_officer_id">
                                <option value="">All Loan Officers</option>
                                @foreach($loanOfficers as $officer)
                                    <option value="{{ $officer->id }}" {{ $loanOfficerId == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="bucket" class="form-label">Bucket</label>
                            <select class="form-select" id="bucket" name="bucket">
                                <option value="">All Buckets</option>
                                <option value="1-30" {{ $bucket == '1-30' ? 'selected' : '' }}>1-30 Days</option>
                                <option value="31-60" {{ $bucket == '31-60' ? 'selected' : '' }}>31-60 Days</option>
                                <option value="61-90" {{ $bucket == '61-90' ? 'selected' : '' }}>61-90 Days</option>
                                <option value="91-180" {{ $bucket == '91-180' ? 'selected' : '' }}>91-180 Days</option>
                                <option value="180+" {{ $bucket == '180+' ? 'selected' : '' }}>180+ Days</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($showData)
        <!-- Summary Cards Row 1 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($delinquencyData['summary']['total_delinquent_loans']) }}</h3>
                                <p class="mb-0">Delinquent Loans</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-error-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">TZS {{ number_format($delinquencyData['summary']['total_delinquent_amount'], 2) }}</h3>
                                <p class="mb-0">Delinquent Amount</p>
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
                                <h3 class="mb-0">{{ number_format($delinquencyData['summary']['average_days_overdue'], 1) }}</h3>
                                <p class="mb-0">Avg Days Overdue</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-time bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ number_format($delinquencyData['summary']['delinquency_rate'], 2) }}%</h3>
                                <p class="mb-0">Delinquency Rate</p>
                            </div>
                            <div class="text-white-50">
                                <i class="bx bx-trending-down bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bucket Analysis Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-1">{{ number_format($delinquencyData['buckets']['1-30']['count']) }}</h4>
                        <p class="mb-1">1-30 Days</p>
                        <small class="text-dark">TZS {{ number_format($delinquencyData['buckets']['1-30']['amount'], 0) }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-1">{{ number_format($delinquencyData['buckets']['31-60']['count']) }}</h4>
                        <p class="mb-1">31-60 Days</p>
                        <small class="text-dark">TZS {{ number_format($delinquencyData['buckets']['31-60']['amount'], 0) }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-orange text-dark">
                    <div class="card-body text-center">
                        <h4 class="mb-1">{{ number_format($delinquencyData['buckets']['61-90']['count']) }}</h4>
                        <p class="mb-1">61-90 Days</p>
                        <small class="text-dark">TZS {{ number_format($delinquencyData['buckets']['61-90']['amount'], 0) }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-1">{{ number_format($delinquencyData['buckets']['91-180']['count']) }}</h4>
                        <p class="mb-1">91-180 Days</p>
                        <small class="text-dark">TZS {{ number_format($delinquencyData['buckets']['91-180']['amount'], 0) }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-secondary text-white">
                    <div class="card-body text-center">
                        <h4 class="mb-1">{{ number_format($delinquencyData['buckets']['180+']['count']) }}</h4>
                        <p class="mb-1">180+ Days</p>
                        <small class="text-white">TZS {{ number_format($delinquencyData['buckets']['180+']['amount'], 0) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Delinquent Loans Details</h5>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('accounting.loans.reports.delinquency') }}" class="d-inline">
                        <input type="hidden" name="as_of_date" value="{{ request('as_of_date') }}">
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                        <input type="hidden" name="loan_officer_id" value="{{ request('loan_officer_id') }}">
                        <input type="hidden" name="bucket" value="{{ request('bucket') }}">
                        <input type="hidden" name="export_type" value="excel">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bx bx-download me-1"></i> Excel
                        </button>
                    </form>
                    <form method="GET" action="{{ route('accounting.loans.reports.delinquency') }}" class="d-inline">
                        <input type="hidden" name="as_of_date" value="{{ request('as_of_date') }}">
                        <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
                        <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                        <input type="hidden" name="loan_officer_id" value="{{ request('loan_officer_id') }}">
                        <input type="hidden" name="bucket" value="{{ request('bucket') }}">
                        <input type="hidden" name="export_type" value="pdf">
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
                                <th>Branch</th>
                                <th>Group</th>
                                <th>Loan Officer</th>
                                <th>Outstanding</th>
                                <th>Days Overdue</th>
                                <th>Bucket</th>
                                <th>Severity</th>
                                <th>Last Payment</th>
                                <th>Next Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($delinquencyData['loans'] as $loan)
                            <tr>
                                <td>{{ $loan['customer'] }}</td>
                                <td>{{ $loan['customer_no'] }}</td>
                                <td>{{ $loan['phone'] }}</td>
                                <td>{{ $loan['branch'] }}</td>
                                <td>{{ $loan['group'] }}</td>
                                <td>{{ $loan['loan_officer'] }}</td>
                                <td class="text-end">TZS {{ number_format($loan['outstanding_amount'], 2) }}</td>
                                <td class="text-end">
                                    <span class="badge 
                                        @if($loan['days_in_arrears'] <= 30) bg-warning
                                        @elseif($loan['days_in_arrears'] <= 60) bg-success
                                        @elseif($loan['days_in_arrears'] <= 90) bg-danger
                                        @else bg-dark
                                        @endif">
                                        {{ $loan['days_in_arrears'] }} days
                                    </span>
                                </td>
                                <td class="text-center">{{ $loan['delinquency_bucket'] }}</td>
                                <td class="text-center">
                                    <span class="badge 
                                        @if($loan['severity_level'] == 'Low') bg-success
                                        @elseif($loan['severity_level'] == 'Medium') bg-warning
                                        @elseif($loan['severity_level'] == 'High') bg-danger
                                        @else bg-dark
                                        @endif">
                                        {{ $loan['severity_level'] }}
                                    </span>
                                </td>
                                <td>{{ $loan['last_payment_date'] }}</td>
                                <td>{{ $loan['next_due_date'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">No delinquent loans found for the selected criteria.</td>
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
