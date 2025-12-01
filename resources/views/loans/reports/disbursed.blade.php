@extends('layouts.main')

@section('title', 'Loans')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => route('reports.loans'), 'icon' => 'bx bx-file'],
            ['label' => 'Loan Disbursement Report', 'url' => '#', 'icon' => 'bx bx-dollar-circle']
        ]" />

        <!-- Report Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0"><i class="bx bx-dollar-circle me-2"></i>Loan Disbursement Report</h5>
                            <small class="text-muted">Generate and export detailed loan disbursement records.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-download me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'download')">
                                            <i class="bx bx-file-pdf me-2"></i> Export PDF
                                        </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel', 'download')">
                                            <i class="bx bx-file me-2"></i> Export Excel
                                        </a></li>
                                </ul>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-show-alt me-1"></i> View
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf', 'view')">
                                            <i class="bx bx-file-pdf me-2"></i> View PDF
                                        </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters Section -->
                        <form id="loanDisbursementForm" method="GET" action="{{ route('accounting.loans.reports.disbursed') }}">
                            <div class="row">
                                <!-- Start Date -->
                                <div class="col-md-3 col-lg-3 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}">
                                </div>
                                <!-- End Date -->
                                <div class="col-md-3 col-lg-3 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}">
                                </div>
                                <!-- Branch -->
                                <div class="col-md-3 col-lg-3 mb-3">
                                    <label for="branch_id" class="form-label">Branch</label>
                                    <select class="form-select select2-single" id="branch_id" name="branch_id">
                                        @if(($branches->count() ?? 0) > 1)
                                            <option value="all" {{ request('branch_id') === 'all' ? 'selected' : '' }}>All My Branches</option>
                                        @endif
                                        @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- grop --}}
                                <div class="col-md-3 col-lg-3 mb-3">
                                    <label for="group_id" class="form-label">Group</label>
                                    <select class="form-select select2-single" id="group_id" name="group_id">
                                        <option value="">All Groups</option>
                                        @foreach($groups as $group)
                                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- loan officer --}}
                                <div class="col-md-3 col-lg-3 mb-3">
                                    <label for="loan_officer_id" class="form-label">Loan Officer</label>
                                    <select class="form-select select2-single" id="loan_officer_id" name="loan_officer_id">
                                        <option value="">All Loan Officers</option>
                                        @foreach($loanOfficers as $user)
                                        <option value="{{ $user->id }}" {{ request('loan_officer_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Button Section -->
                                <div class="col-md-6 col-lg-3 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bx bx-search me-1"></i> Apply Filters
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($disbursements))
        <!-- Report Summary Cards -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Report Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="border-l-4 border-blue-500 rounded-lg p-4 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-500">Total Amount Disbursed</p>
                                    <h3 class="text-2xl font-bold mt-1 text-blue-600">
                                        {{ number_format($summary['total_disbursed'] ?? 0, 2) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="border-l-4 border-emerald-500 rounded-lg p-4 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-500">Number of Loans</p>
                                    <h3 class="text-2xl font-bold mt-1 text-emerald-600">
                                        {{ number_format($summary['loan_count'] ?? 0) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="border-l-4 border-purple-500 rounded-lg p-4 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-500">Average Disbursed Amount</p>
                                    <h3 class="text-2xl font-bold mt-1 text-purple-600">
                                        {{ number_format($summary['average_disbursed'] ?? 0, 2) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="border-l-4 border-danger rounded-lg p-4 bg-gray-50">
                                    <p class="text-sm font-medium text-gray-500">Total Interest Expected</p>
                                    <h3 class="text-2xl font-bold mt-1 text-danger">
                                        {{ number_format($summary['total_interest_expected'] ?? 0, 2) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Table Card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Disbursement Details</h6>
                    </div>
                    <div class="card-body">
                        @if(isset($disbursements) && count($disbursements) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>

                                        <th scope="col">A/C NO.</th>
                                        <th scope="col">Disbursement Date</th>
                                        <th scope="col">Period</th>
                                        <th scope="col">Loan Officer</th>
                                        <th scope="col">Customer Name</th>
                                        <th scope="col">Customer No</th>
                                        <th scope="col">Group Name</th>
                                        <th scope="col">Loan No</th>
                                        <th scope="col">REF No</th>
                                        <th scope="col">Application Date</th>
                                        <th scope="col">Loan Product</th>
                                        <th scope="col">Disbursed Amount</th>
                                        <th scope="col">Amount To Pay</th>
                                        <th scope="col">Inetrest Amount</th>
                                        <th scope="col">Inetrest Rate</th>
                                        <th scope="col">End Date</th>
                                        <th scope="col">Branch</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($disbursements as $disbursement)
                                    <tr>

                                        <td>{{ $disbursement->customer->customerNo ?? 'N/A' }} - {{ $disbursement->loanNo ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($disbursement->disbursed_on)->format('M d, Y') }}</td>
                                        <td>{{ $disbursement->period }} {{ $disbursement->getPeriodUnit() }}</td>
                                        <td>{{ $disbursement->loanOfficer->name ?? 'N/A' }}</td>
                                        <td>{{ $disbursement->customer->name ?? 'N/A' }}</td>
                                        <td>{{ $disbursement->customer->customerNo ?? 'N/A' }}</td>
                                        <td>{{ $disbursement->group->name ?? 'N/A' }}</td>
                                        <td>{{ $disbursement->loanNo ?? 'N/A'}}</td>
                                        <td>{{ $disbursement->loanNo ?? 'N/A'}}</td>
                                        <td>{{ $disbursement->date_applied }}</td>
                                        <td>{{ $disbursement->product->name ?? 'N/A' }}</td>
                                        <td class="text-right">{{ number_format($disbursement->amount, 2) }}</td>
                                        <td>{{ number_format($disbursement->amount_total, 2) }}</td>
                                        <td>{{ number_format($disbursement->interest_amount, 2) }}</td>
                                        <td>{{ number_format($disbursement->interest, 2) }}</td>

                                        <td>{{ \Carbon\Carbon::parse($disbursement->last_repayment_date)->format('M d, Y') }}</td>
                                        <td>{{ $disbursement->branch->name ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bx bx-info-circle fs-1 text-muted"></i>
                            <p class="mt-2 text-muted">No loan disbursement data found for the selected criteria.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    function exportReport(type, action) {
        const form = document.getElementById('loanDisbursementForm');
        const formData = new FormData(form);
        formData.append('export_type', type);
        formData.append('export_action', action); // Add the action parameter

        // Create the URL with all form data
        const url = '{{ route("accounting.loans.reports.loan-export") }}?' + new URLSearchParams(Object.fromEntries(formData));

        Swal.fire({
            title: 'Generating Report...',
            html: `Please wait while we prepare your ${type.toUpperCase()} report.`,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Create a temporary link to trigger the download/view without navigating the page
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank'; // Open in a new tab for "view"
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Hide the loading spinner after a short delay (e.g., 3 seconds)
        // This is a practical workaround since we don't have a direct callback
        setTimeout(() => {
            Swal.close();
        }, 3000);
    }
</script>
@endsection
