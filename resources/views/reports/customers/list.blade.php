@extends('layouts.main')

@section('title', 'Customer List Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Customer Reports', 'url' => route('reports.customers'), 'icon' => 'bx bx-group'],
                ['label' => 'Customer List Report', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />
            <h6 class="mb-0 text-uppercase">CUSTOMER LIST REPORT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Customer List Report</h4>

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
                            <form method="GET" action="{{ route('reports.customers.list') }}" class="mb-4">
                                <div class="row g-3">
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
                                        <label for="region_id" class="form-label">Region</label>
                                        <select class="form-select" id="region_id" name="region_id">
                                            <option value="all">All Regions</option>
                                            @foreach($regions as $region)
                                                <option value="{{ $region->id }}" {{ $regionId == $region->id ? 'selected' : '' }}>
                                                    {{ $region->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="district_id" class="form-label">District</label>
                                        <select class="form-select" id="district_id" name="district_id">
                                            <option value="all">All Districts</option>
                                            @foreach($districts as $district)
                                                <option value="{{ $district->id }}" {{ $districtId == $district->id ? 'selected' : '' }}>
                                                    {{ $district->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category">
                                            <option value="all">All Categories</option>
                                            <option value="individual" {{ $category == 'individual' ? 'selected' : '' }}>Individual</option>
                                            <option value="group" {{ $category == 'group' ? 'selected' : '' }}>Group</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="sex" class="form-label">Gender</label>
                                        <select class="form-select" id="sex" name="sex">
                                            <option value="all">All Genders</option>
                                            <option value="male" {{ $sex == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ $sex == 'female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="has_loans" class="form-label">Has Loans</label>
                                        <select class="form-select" id="has_loans" name="has_loans">
                                            <option value="all">All Customers</option>
                                            <option value="yes" {{ $hasLoans == 'yes' ? 'selected' : '' }}>With Loans</option>
                                            <option value="no" {{ $hasLoans == 'no' ? 'selected' : '' }}>Without Loans</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="has_collateral" class="form-label">Has Collateral</label>
                                        <select class="form-select" id="has_collateral" name="has_collateral">
                                            <option value="all">All Customers</option>
                                            <option value="yes" {{ $hasCollateral == 'yes' ? 'selected' : '' }}>With Collateral</option>
                                            <option value="no" {{ $hasCollateral == 'no' ? 'selected' : '' }}>Without Collateral</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="registration_date_from" class="form-label">Registration Date From</label>
                                        <input type="date" class="form-control" id="registration_date_from" name="registration_date_from" 
                                               value="{{ $registrationDateFrom }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="registration_date_to" class="form-label">Registration Date To</label>
                                        <input type="date" class="form-control" id="registration_date_to" name="registration_date_to" 
                                               value="{{ $registrationDateTo }}">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bx bx-search me-1"></i> Generate Report
                                        </button>
                                        <a href="{{ route('reports.customers.list.export-pdf', request()->query()) }}" 
                                           class="btn btn-danger me-2" target="_blank">
                                            <i class="bx bx-file-pdf me-1"></i> Export PDF
                                        </a>
                                        <a href="{{ route('reports.customers.list.export', request()->query()) }}" 
                                           class="btn btn-success">
                                            <i class="bx bx-file me-1"></i> Export Excel
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <!-- Summary Cards -->
                            <div class="row mb-4">
                                <div class="col-md-2">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">Total Customers</h6>
                                            <h4 class="text-primary">{{ number_format($customersData['summary']['total_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info">Male Customers</h6>
                                            <h4 class="text-info">{{ number_format($customersData['summary']['male_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-warning">Female Customers</h6>
                                            <h4 class="text-warning">{{ number_format($customersData['summary']['female_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-success">With Loans</h6>
                                            <h4 class="text-success">{{ number_format($customersData['summary']['customers_with_loans']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-secondary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-secondary">With Collateral</h6>
                                            <h4 class="text-secondary">{{ number_format($customersData['summary']['customers_with_collateral']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card border-danger">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-danger">Total Loans</h6>
                                            <h4 class="text-danger">{{ number_format($customersData['summary']['total_loans']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Summary -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-success">Total Loan Amount</h6>
                                            <h4 class="text-success">{{ number_format($customersData['summary']['total_loan_amount'], 2) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info">Total Collateral Amount</h6>
                                            <h4 class="text-info">{{ number_format($customersData['summary']['total_collateral_amount'], 2) }}</h4>
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
                                            <th>Customer No</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Region</th>
                                            <th>District</th>
                                            <th>Branch</th>
                                            <th>Category</th>
                                            <th>Gender</th>
                                            <th>Date Registered</th>
                                            <th>Has Loans</th>
                                            <th>Loan Count</th>
                                            <th class="text-end">Total Loan Amount</th>
                                            <th>Has Collateral</th>
                                            <th class="text-end">Collateral Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customersData['data'] as $index => $customer)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $customer->customerNo }}</td>
                                                <td>{{ $customer->name }}</td>
                                                <td>{{ $customer->phone1 }}</td>
                                                <td>{{ $customer->email ?? 'N/A' }}</td>
                                                <td>{{ $customer->region->name ?? 'N/A' }}</td>
                                                <td>{{ $customer->district->name ?? 'N/A' }}</td>
                                                <td>{{ $customer->branch->name ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $customer->category === 'individual' ? 'primary' : 'success' }}">
                                                        {{ ucfirst($customer->category ?? 'N/A') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $customer->sex === 'male' ? 'info' : 'warning' }}">
                                                        {{ ucfirst($customer->sex) }}
                                                    </span>
                                                </td>
                                                <td>{{ $customer->dateRegistered ? $customer->dateRegistered->format('d/m/Y') : 'N/A' }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $customer->loans->count() > 0 ? 'success' : 'secondary' }}">
                                                        {{ $customer->loans->count() > 0 ? 'Yes' : 'No' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">{{ $customer->loans->count() }}</td>
                                                <td class="text-end">{{ number_format($customer->loans->sum('amount'), 2) }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $customer->has_cash_collateral ? 'success' : 'secondary' }}">
                                                        {{ $customer->has_cash_collateral ? 'Yes' : 'No' }}
                                                    </span>
                                                </td>
                                                <td class="text-end">{{ number_format($customer->collaterals->sum('amount'), 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="16" class="text-center text-muted py-4">
                                                    <i class="bx bx-info-circle fs-1 d-block mb-2"></i>
                                                    No customers found for the selected criteria.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
