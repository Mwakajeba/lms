@extends('layouts.main')

@section('title', 'Penalties Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Accounting Reports', 'url' => route('accounting.reports.index'), 'icon' => 'bx bx-calculator'],
                ['label' => 'Penalties Report', 'url' => '#', 'icon' => 'bx bx-error-circle']
            ]" />
            <h6 class="mb-0 text-uppercase">PENALTIES REPORT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Penalties Report</h4>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bx bx-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(isset($errors) && $errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-2"></i>
                                    Please fix the following errors:
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <!-- Filter Form -->
                            <form method="GET" action="{{ route('accounting.reports.penalties') }}" class="mb-4">
                                <div class="row g-3">
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
                                                <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>All Branches</option>
                                            @endif
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="penalty_id" class="form-label">Penalty Type</label>
                                        <select class="form-select" id="penalty_id" name="penalty_id">
                                            <option value="all">All Penalties</option>
                                            @foreach($penalties as $penalty)
                                                <option value="{{ $penalty->id }}" {{ $penaltyId == $penalty->id ? 'selected' : '' }}>
                                                    {{ $penalty->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="penalty_type" class="form-label">Account Type</label>
                                        <select class="form-select" id="penalty_type" name="penalty_type">
                                            <option value="all" {{ $penaltyType == 'all' ? 'selected' : '' }}>All Types</option>
                                            <option value="income" {{ $penaltyType == 'income' ? 'selected' : '' }}>Penalty Income</option>
                                            <option value="receivables" {{ $penaltyType == 'receivables' ? 'selected' : '' }}>Penalty Receivables</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bx bx-search me-1"></i> Generate Report
                                        </button>
                                        <a href="{{ route('accounting.reports.penalties.export-pdf', request()->query()) }}" 
                                           class="btn btn-danger me-2" target="_blank">
                                            <i class="bx bx-file-pdf me-1"></i> Export PDF
                                        </a>
                                        <a href="{{ route('accounting.reports.penalties.export', request()->query()) }}" 
                                           class="btn btn-success">
                                            <i class="bx bx-file me-1"></i> Export Excel
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <!-- Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-md-2">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-danger">Total Debit</h6>
                                            <h4 class="text-danger">{{ number_format($penaltiesData['summary']['total_debit'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-success">Total Credit</h6>
                                            <h4 class="text-success">{{ number_format($penaltiesData['summary']['total_credit'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">Balance</h6>
                                            <h4 class="text-primary">{{ number_format($penaltiesData['summary']['balance'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info">Total Transactions</h6>
                                            <h4 class="text-info">{{ number_format($penaltiesData['summary']['total_transactions']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-warning">Unique Penalties</h6>
                                            <h4 class="text-warning">{{ number_format($penaltiesData['summary']['unique_penalties']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-secondary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-secondary">Unique Customers</h6>
                                            <h4 class="text-secondary">{{ number_format($penaltiesData['summary']['unique_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Description</th>
                                            <th class="text-end">Amount</th>
                                            <th>Reference ID</th>
                                            <th>Transaction Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($penaltiesData['data'] as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                                                <td>{{ $item->customer_name ?? 'N/A' }}</td>
                                                <td>{{ $item->description }}</td>
                                                <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                                                <td>{{ $item->reference_id }}</td>
                                                <td>{{ $item->transaction_type }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="13" class="text-center text-muted py-4">
                                                    <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                                                    No penalty transactions found for the selected criteria.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="4" class="text-end fw-bold">TOTAL BALANCE:</td>
                                            <td class="text-end fw-bold text-{{ $penaltiesData['summary']['balance'] >= 0 ? 'success' : 'danger' }}">
                                                {{ number_format($penaltiesData['summary']['balance'], 2) }}
                                            </td>
                                            <td colspan="2"></td>
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
@endsection
