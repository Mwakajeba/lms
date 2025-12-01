@extends('layouts.main')
@section('title', 'Bulk Upload Customers')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Customers', 'url' => route('customers.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Bulk Upload', 'url' => '#', 'icon' => 'bx bx-upload']
        ]" />

            <h6 class="mb-0 text-uppercase">BULK UPLOAD CUSTOMERS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="row">
                                <!-- Sample Download Section -->
                                <div class="col-md-6 mb-4">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="bx bx-download me-2"></i>Download Sample CSV</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-3">Download the sample CSV file to understand the
                                                required format for bulk upload.</p>
                                            <a href="{{ route('customers.download-sample') }}"
                                                class="btn btn-outline-primary">
                                                <i class="bx bx-download me-2"></i>Download Sample CSV
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instructions Section -->
                                <div class="col-md-6 mb-4">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Instructions</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="mb-0">
                                                <li>Download the sample CSV file first</li>
                                                <li>Fill in the customer data following the format</li>
                                                <li>Save as CSV format</li>
                                                <li>Upload the file below</li>
                                                <li>Select cash deposit options if needed</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-2"></i>
                                    <strong>Upload failed!</strong> Please fix the following errors:
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(session('upload_errors'))
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <i class="bx bx-warning me-2"></i>
                                    <strong>Upload completed with warnings!</strong> Some rows had issues:
                                    <ul class="mb-0 mt-2">
                                        @foreach(session('upload_errors') as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bx bx-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <!-- Upload Form -->
                            <form action="{{ route('customers.bulk-upload.store') }}" method="POST"
                                enctype="multipart/form-data" id="bulkUploadForm">
                                @csrf

                                <div class="row">
                                    <!-- CSV File Upload -->
                                    <div class="col-md-12 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="bx bx-file me-2"></i>Upload CSV File</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="csv_file" class="form-label">Select CSV File <span
                                                            class="text-danger">*</span></label>
                                                    <input type="file" name="csv_file" id="csv_file"
                                                        class="form-control @error('csv_file') is-invalid @enderror"
                                                        accept=".csv" required>
                                                    <div class="form-text">Only CSV files are allowed. Maximum size: 5MB
                                                    </div>
                                                    @error('csv_file')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cash Deposit Options -->
                                    <div class="col-md-12 mb-4">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="mb-0"><i class="bx bx-money me-2"></i>Cash Deposit Options
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" value="1"
                                                                name="has_cash_collateral" id="has_cash_collateral" checked>
                                                            <label class="form-check-label" for="has_cash_collateral">
                                                                Apply Cash Deposit to All Customers
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 mb-3" id="collateral-type-container">
                                                        <label class="form-label">Deposit Type</label>
                                                        <select name="collateral_type_id" class="form-select">
                                                            <option value="">Select Deposit Type</option>
                                                            @foreach($collateralTypes as $index => $type)
                                                                <option value="{{ $type->id }}" {{ $index === 0 ? 'selected' : '' }}>{{ $type->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Customers
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="bx bx-upload me-1"></i>
                                        <span id="submitText">Upload Customers</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkbox = document.querySelector('#has_cash_collateral');
            const collateralContainer = document.querySelector('#collateral-type-container');
            const form = document.querySelector('#bulkUploadForm');
            const submitBtn = document.querySelector('#submitBtn');
            const submitText = document.querySelector('#submitText');

            // Show/hide collateral type
            function toggleCollateralField() {
                if (checkbox.checked) {
                    collateralContainer.style.display = 'block';
                } else {
                    collateralContainer.style.display = 'none';
                }
            }

            checkbox.addEventListener('change', toggleCollateralField);
            // Initialize the state on page load
            toggleCollateralField();

            // Handle form submission
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitText.textContent = 'Uploading...';
                submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Uploading...';
            });
        });
    </script>
@endpush