@extends('layouts.main')

@section('title', 'Customer Demographics Report')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Customer Reports', 'url' => route('reports.customers'), 'icon' => 'bx bx-group'],
                ['label' => 'Customer Demographics Report', 'url' => '#', 'icon' => 'bx bx-pie-chart-alt-2']
            ]" />
            <h6 class="mb-0 text-uppercase">CUSTOMER DEMOGRAPHICS REPORT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Customer Demographics Report</h4>

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
                            <form method="GET" action="{{ route('reports.customers.demographics') }}" class="mb-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                               value="{{ $startDate }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="end_date" class="form-label">End Date</label>
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
                                        <label for="gender" class="form-label">Gender</label>
                                        <select class="form-select" id="gender" name="gender">
                                            <option value="all" {{ $gender == 'all' ? 'selected' : '' }}>All Genders</option>
                                            <option value="male" {{ $gender == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ $gender == 'female' ? 'selected' : '' }}>Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category">
                                            <option value="all" {{ $category == 'all' ? 'selected' : '' }}>All Categories</option>
                                            <option value="individual" {{ $category == 'individual' ? 'selected' : '' }}>Individual</option>
                                            <option value="group" {{ $category == 'group' ? 'selected' : '' }}>Group</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="age_group" class="form-label">Age Group</label>
                                        <select class="form-select" id="age_group" name="age_group">
                                            <option value="all" {{ $ageGroup == 'all' ? 'selected' : '' }}>All Age Groups</option>
                                            <option value="Under 18" {{ $ageGroup == 'Under 18' ? 'selected' : '' }}>Under 18</option>
                                            <option value="18-25" {{ $ageGroup == '18-25' ? 'selected' : '' }}>18-25</option>
                                            <option value="26-35" {{ $ageGroup == '26-35' ? 'selected' : '' }}>26-35</option>
                                            <option value="36-45" {{ $ageGroup == '36-45' ? 'selected' : '' }}>36-45</option>
                                            <option value="46-55" {{ $ageGroup == '46-55' ? 'selected' : '' }}>46-55</option>
                                            <option value="56-65" {{ $ageGroup == '56-65' ? 'selected' : '' }}>56-65</option>
                                            <option value="Over 65" {{ $ageGroup == 'Over 65' ? 'selected' : '' }}>Over 65</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bx bx-search me-1"></i> Generate Report
                                        </button>
                                        <a href="{{ route('reports.customers.demographics.export-pdf', request()->query()) }}" 
                                           class="btn btn-danger me-2" target="_blank">
                                            <i class="bx bx-file-pdf me-1"></i> Export PDF
                                        </a>
                                        <a href="{{ route('reports.customers.demographics.export', request()->query()) }}" 
                                           class="btn btn-success">
                                            <i class="bx bx-file me-1"></i> Export Excel
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <!-- Summary Statistics -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-primary">Total Customers</h6>
                                            <h4 class="text-primary">{{ number_format($demographicsData['statistics']['total_customers']) }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-info">Average Age</h6>
                                            <h4 class="text-info">{{ $demographicsData['statistics']['average_age'] }} years</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-success">With Loans</h6>
                                            <h4 class="text-success">{{ number_format($demographicsData['statistics']['customers_with_loans']) }}</h4>
                                            <small class="text-muted">{{ $demographicsData['statistics']['loan_percentage'] }}%</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-warning">With Collateral</h6>
                                            <h4 class="text-warning">{{ number_format($demographicsData['statistics']['customers_with_collateral']) }}</h4>
                                            <small class="text-muted">{{ $demographicsData['statistics']['collateral_percentage'] }}%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Demographics Charts -->
                            <div class="row mb-4">
                                <!-- Gender Distribution -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Gender Distribution</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Gender</th>
                                                            <th class="text-center">Count</th>
                                                            <th class="text-center">Percentage</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($demographicsData['statistics']['gender_distribution'] as $gender => $data)
                                                            <tr>
                                                                <td>
                                                                    <span class="badge bg-{{ $gender == 'male' ? 'primary' : ($gender == 'female' ? 'danger' : 'secondary') }}">
                                                                        {{ ucfirst($gender) }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-center">{{ number_format($data['count']) }}</td>
                                                                <td class="text-center">{{ $data['percentage'] }}%</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Age Group Distribution -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Age Group Distribution</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Age Group</th>
                                                            <th class="text-center">Count</th>
                                                            <th class="text-center">Percentage</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($demographicsData['statistics']['age_group_distribution'] as $ageGroup => $data)
                                                            <tr>
                                                                <td>
                                                                    <span class="badge bg-info">
                                                                        {{ $ageGroup }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-center">{{ number_format($data['count']) }}</td>
                                                                <td class="text-center">{{ $data['percentage'] }}%</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Geographic Distribution -->
                            <div class="row mb-4">
                                <!-- Region Distribution -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Region Distribution</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive" style="max-height: 300px;">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Region</th>
                                                            <th class="text-center">Count</th>
                                                            <th class="text-center">Percentage</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($demographicsData['statistics']['region_distribution'] as $region => $data)
                                                            <tr>
                                                                <td>{{ $region }}</td>
                                                                <td class="text-center">{{ number_format($data['count']) }}</td>
                                                                <td class="text-center">{{ $data['percentage'] }}%</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Branch Distribution -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Branch Distribution</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive" style="max-height: 300px;">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Branch</th>
                                                            <th class="text-center">Count</th>
                                                            <th class="text-center">Percentage</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($demographicsData['statistics']['branch_distribution'] as $branch => $data)
                                                            <tr>
                                                                <td>{{ $branch }}</td>
                                                                <td class="text-center">{{ number_format($data['count']) }}</td>
                                                                <td class="text-center">{{ $data['percentage'] }}%</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Category Distribution -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Category Distribution</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Category</th>
                                                            <th class="text-center">Count</th>
                                                            <th class="text-center">Percentage</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($demographicsData['statistics']['category_distribution'] as $category => $data)
                                                            <tr>
                                                                <td>
                                                                    <span class="badge bg-{{ $category == 'individual' ? 'success' : 'warning' }}">
                                                                        {{ ucfirst($category) }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-center">{{ number_format($data['count']) }}</td>
                                                                <td class="text-center">{{ $data['percentage'] }}%</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Monthly Registration Trends -->
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Monthly Registration Trends</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive" style="max-height: 300px;">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Month</th>
                                                            <th class="text-center">New Registrations</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($demographicsData['statistics']['monthly_registrations'] as $month => $count)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}</td>
                                                                <td class="text-center">
                                                                    <span class="badge bg-primary">{{ number_format($count) }}</span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
