@extends('layouts.main')

@section('title', 'Loan Outstanding Balance Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
             ['label' => 'Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
            ['label' => 'Loan Outstanding Balance Report', 'url' => '#', 'icon' => 'bx bx-calculator']
        ]" />
        <h6 class="mb-0 text-uppercase">LOAN OUTSTANDING BALANCE REPORT</h6>
        <hr />

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bx bx-calculator me-2"></i>Loan Outstanding Balance Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('accounting.loans.reports.loan_outstanding') }}">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="as_of_date" class="form-label">As of Date</label>
                            <input type="date" class="form-control" id="as_of_date" name="as_of_date" value="{{ request('as_of_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="loan_officer_id" class="form-label">Loan Officer</label>
                            <select class="form-select" id="loan_officer_id" name="loan_officer_id">
                                <option value="">All Officers</option>
                                @foreach($loanOfficers as $officer)
                                    <option value="{{ $officer->id }}" {{ request('loan_officer_id') == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                                @endforeach
                            </select>
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
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </form>
                
                @if(isset($outstandingData) && !empty($outstandingData))
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" class="btn btn-success" onclick="exportReport('excel')">
                                <i class="bx bx-file me-1"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">
                                <i class="bx bx-file-pdf me-1"></i> Export PDF
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        
        <div class="row mb-4">
            <div class="col-md-2 mb-3">
                <div class="border-l-4 border-primary rounded-lg p-4 bg-gray-50">
                    <p class="text-sm font-medium text-gray-500">Total Principal Disbursed</p>
                    <h3 class="text-2xl font-bold mt-1 text-primary">
                        {{ number_format($summary['total_principal_disbursed'] ?? 0, 2) }}
                    </h3>
                    <small class="text-muted">(Loan amounts given)</small>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="border-l-4 border-info rounded-lg p-4 bg-gray-50">
                    <p class="text-sm font-medium text-gray-500">Total Expected Interest</p>
                    <h3 class="text-2xl font-bold mt-1 text-info">
                        {{ number_format($summary['total_expected_interest'] ?? 0, 2) }}
                    </h3>
                    <small class="text-muted">(Total interest for all loans)</small>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="border-l-4 border-warning rounded-lg p-4 bg-gray-50">
                    <p class="text-sm font-medium text-gray-500">Total Paid Interest</p>
                    <h3 class="text-2xl font-bold mt-1 text-warning">
                        {{ number_format($summary['total_paid_interest'] ?? 0, 2) }}
                    </h3>
                    <small class="text-muted">(Already received)</small>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="border-l-4 border-success rounded-lg p-4 bg-gray-50">
                    <p class="text-sm font-medium text-gray-500">Total Principal Paid</p>
                    <h3 class="text-2xl font-bold mt-1 text-success">
                        {{ number_format($summary['total_principal_paid'] ?? 0, 2) }}
                    </h3>
                    <small class="text-muted">(Principal repaid)</small>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="border-l-4 border-danger rounded-lg p-4 bg-gray-50">
                    <p class="text-sm font-medium text-gray-500">Outstanding Interest</p>
                    <h3 class="text-2xl font-bold mt-1 text-danger">
                        {{ number_format($summary['total_outstanding_interest'] ?? 0, 2) }}
                    </h3>
                    <small class="text-muted">(Due but not paid)</small>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <div class="border-l-4 border-secondary rounded-lg p-4 bg-gray-50">
                    <p class="text-sm font-medium text-gray-500">Accrued Interest</p>
                    <h3 class="text-2xl font-bold mt-1 text-secondary">
                        {{ number_format($summary['total_accrued_interest'] ?? 0, 2) }}
                    </h3>
                    <small class="text-muted">(Earned but not due)</small>
                </div>
            </div>
        </div>
        
        <!-- Interest Relationship Verification -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Interest Relationship Verification</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Expected Interest (Original)</h6>
                                    <h4 class="text-primary">{{ number_format($summary['total_expected_interest'] ?? 0, 2) }}</h4>
                                    <small class="text-muted">(Total interest for all loans)</small>
                                </div>
                            </div>
                            <div class="col-md-1 text-center d-flex align-items-center">
                                <span class="text-muted">=</span>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted">Interest Paid</h6>
                                    <h5 class="text-success">{{ number_format($summary['total_paid_interest'] ?? 0, 2) }}</h5>
                                    <small class="text-muted">(Already received)</small>
                                </div>
                            </div>
                            <div class="col-md-1 text-center d-flex align-items-center">
                                <span class="text-muted">+</span>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted">Outstanding Interest</h6>
                                    <h5 class="text-danger">{{ number_format($summary['total_outstanding_interest'] ?? 0, 2) }}</h5>
                                    <small class="text-muted">(Due but not paid)</small>
                                </div>
                            </div>
                            <div class="col-md-1 text-center d-flex align-items-center">
                                <span class="text-muted">+</span>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h6 class="text-muted">Not Due Interest</h6>
                                    <h5 class="text-warning">{{ number_format($summary['total_not_due_interest'] ?? 0, 2) }}</h5>
                                    <small class="text-muted">(Future payments)</small>
                                </div>
                            </div>
                            <div class="col-md-1 text-center d-flex align-items-center">
                                <span class="text-muted">=</span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Calculated Expected Interest:</h6>
                                <h4 class="text-info">{{ number_format($summary['total_calculated_expected_interest'] ?? 0, 2) }}</h4>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Difference:</h6>
                                <h4 class="{{ abs(($summary['total_expected_interest'] ?? 0) - ($summary['total_calculated_expected_interest'] ?? 0)) < 0.01 ? 'text-success' : 'text-warning' }}">
                                    {{ number_format(($summary['total_expected_interest'] ?? 0) - ($summary['total_calculated_expected_interest'] ?? 0), 2) }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        @if(isset($outstandingData))
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Outstanding Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th colspan="10" class="text-center">DISBURSEMENT</th>
                                <th colspan="2" class="text-center">REPAYMENT</th>
                                <th colspan="6" class="text-center">OUTSTANDING & INTEREST BREAKDOWN</th>
                            </tr>
                            <tr>
                                <th>Customer</th>
                                <th>Customer No</th>
                                <th>Phone</th>
                                <th>Loan No</th>
                                <th>Disbursed Amount</th>
                                <th>Expected Interest</th>
                                <th>Disbursed Date</th>
                                <th>Expiry</th>
                                <th>Branch</th>
                                <th>Loan Officer</th>
                                <th class="text-end">Principal Paid</th>
                                <th class="text-end">Interest Paid</th>
                                <th class="text-end">Outstanding Principal</th>
                                <th class="text-end">Accrued Interest</th>
                                <th class="text-end">Outstanding Interest</th>
                                <th class="text-end">Not Due Interest</th>
                                <th class="text-end">Outstanding Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($outstandingData as $row)
                                <tr>
                                    <td>{{ $row['customer'] }}</td>
                                    <td>{{ $row['customer_no'] }}</td>
                                    <td>{{ $row['phone'] }}</td>
                                    <td>{{ $row['loan_no'] }}</td>
                                    <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['interest'], 2) }}</td>
                                    <td>{{ $row['disbursed_no'] }}</td>
                                    <td>{{ $row['expiry'] }}</td>
                                    <td>{{ $row['branch'] }}</td>
                                    <td>{{ $row['loan_officer'] }}</td>
                                    <td class="text-end">{{ number_format($row['principal_paid'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['interest_paid'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['amount'] - $row['principal_paid'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['accrued_interest'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['outstanding_interest'], 2) }}</td>
                                    <td class="text-end">{{ number_format($row['not_due_interest'], 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($row['outstanding_balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center text-muted">No outstanding data found for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th colspan="4" class="text-center">TOTALS</th>
                                <th class="text-end">{{ number_format(collect($outstandingData)->sum('amount'), 2) }}</th>
                                <th class="text-end">{{ number_format(collect($outstandingData)->sum('interest'), 2) }}</th>
                                <th colspan="4"></th>
                                <th class="text-end">{{ number_format(collect($outstandingData)->sum('principal_paid'), 2) }}</th>
                                <th class="text-end">{{ number_format(collect($outstandingData)->sum('interest_paid'), 2) }}</th>
                                <th class="text-end">{{ number_format(collect($outstandingData)->sum(function($row) { return $row['amount'] - $row['principal_paid']; }), 2) }}</th>
                                <th class="text-end">{{ number_format(collect($outstandingData)->sum('accrued_interest'), 2) }}</th>
                                <th class="text-end">{{ number_format(collect($outstandingData)->sum('outstanding_interest'), 2) }}</th>
                                <th class="text-end">{{ number_format(collect($outstandingData)->sum('not_due_interest'), 2) }}</th>
                                <th class="text-end">{{ number_format(collect($outstandingData)->sum('outstanding_balance'), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function exportReport(type) {
    const form = document.querySelector('form');
    const formData = new FormData(form);
    formData.append('export_type', type);
    
    // Convert FormData to URL parameters
    const params = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        if (value !== '') {
            params.append(key, value);
        }
    }
    
    const url = '{{ route("accounting.loans.reports.loan_outstanding") }}?' + params.toString();
    
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
    
    // Close the loading state after a short delay
    setTimeout(() => {
        Swal.close();
    }, 2000);
}
</script>
@endsection
