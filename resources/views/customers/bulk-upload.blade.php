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
                                <!-- Excel Template Download Section -->
                                <div class="col-md-8 mb-4">
                                    <div class="card border-success shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0"><i class="bx bx-download me-2"></i>Download Excel Template (Required)</h5>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted mb-3"><strong>Excel format (.xlsx) is required for bulk upload.</strong> The template includes all regions and districts from the database with dependent dropdowns.</p>
                                            <ul class="text-muted mb-3">
                                                <li>✅ <strong>Dependent dropdowns:</strong> Select Region first, then District will auto-filter based on selected region</li>
                                                <li>✅ <strong>All regions and districts</strong> are loaded from the database (from seeders)</li>
                                                <li>✅ <strong>Sex validation:</strong> M or F only</li>
                                                <li>✅ <strong>Data validation:</strong> Prevents invalid entries</li>
                                                <li>✅ <strong>Easy data entry:</strong> Just select from dropdowns</li>
                                                <li>✅ <strong>Auto-formats:</strong> Phone numbers starting with 0 will be auto-formatted to 255 prefix</li>
                                            </ul>
                                            <a href="{{ route('customers.download-excel-template') }}"
                                                class="btn btn-success btn-lg">
                                                <i class="bx bx-download me-2"></i>Download Excel Template (.xlsx)
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Instructions Section -->
                                <div class="col-md-4 mb-4">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Instructions</h6>
                                        </div>
                                        <div class="card-body">
                                            <ol class="mb-0 small">
                                                <li>Download the Excel template (.xlsx)</li>
                                                <li>Fill in customer data</li>
                                                <li><strong>Select Region first</strong>, then District will auto-filter</li>
                                                <li>Save as Excel format (.xlsx)</li>
                                                <li>Upload the file below</li>
                                                <li>Select cash deposit options if needed</li>
                                            </ol>
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
                                    <strong>Upload completed with warnings!</strong> 
                                    @if(session('success_count'))
                                        <p class="mb-2">
                                            <strong>Successfully imported:</strong> {{ session('success_count') }} customers
                                            @if(session('failed_count'))
                                                | <strong>Failed:</strong> {{ session('failed_count') }} customers
                                            @endif
                                        </p>
                                    @endif
                                    <p class="mb-2">Some rows had issues:</p>
                                    <ul class="mb-2">
                                        @foreach(array_slice(session('upload_errors'), 0, 10) as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                        @if(count(session('upload_errors')) > 10)
                                            <li><em>... and {{ count(session('upload_errors')) - 10 }} more errors</em></li>
                                        @endif
                                    </ul>
                                    @if(session('failed_customer_records'))
                                        <div class="mt-3">
                                            <a href="{{ route('customers.export-failed-records') }}" class="btn btn-danger btn-sm">
                                                <i class="bx bx-download me-1"></i> Download Failed Records Excel File
                                            </a>
                                            <small class="d-block text-muted mt-1">The Excel file contains all failed records with their original data and error reasons.</small>
                                        </div>
                                    @endif
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

                            <!-- Progress Bar (Hidden initially) -->
                            <div id="upload-progress-container" class="card mb-4" style="display: none;">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="bx bx-loader-alt bx-spin me-2"></i>Processing Upload...</h6>
                                    <div class="progress" style="height: 30px;">
                                        <div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            <span id="upload-progress-text">0%</span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small id="upload-status-text" class="text-muted">Preparing upload...</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Form -->
                            <form action="{{ route('customers.bulk-upload.store') }}" method="POST"
                                enctype="multipart/form-data" id="bulkUploadForm">
                                @csrf

                                <div class="row">
                                    <!-- Excel File Upload -->
                                    <div class="col-md-12 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0"><i class="bx bx-file me-2"></i>Upload Excel File</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="excel_file" class="form-label">Select Excel File (.xlsx) <span
                                                            class="text-danger">*</span></label>
                                                    <input type="file" name="csv_file" id="excel_file"
                                                        class="form-control @error('csv_file') is-invalid @enderror"
                                                        accept=".xlsx,.xls" required>
                                                    <div class="form-text">
                                                        <i class="bx bx-info-circle"></i> <strong>Excel format (.xlsx or .xls) is required.</strong> Maximum size: 10MB. 
                                                        For files with more than 100 rows, processing will be queued automatically.
                                                        <br>
                                                        <small class="text-muted">Note: Regions and districts are loaded from the database. Use the Excel template to ensure correct region/district selection.</small>
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
            const progressContainer = document.getElementById('upload-progress-container');
            const progressBar = document.getElementById('upload-progress-bar');
            const progressText = document.getElementById('upload-progress-text');
            const statusText = document.getElementById('upload-status-text');

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

            // Check if we're returning from an upload (show completion)
            @if(session('success') || session('upload_errors'))
                if (progressContainer) {
                    progressContainer.style.display = 'block';
                    progressBar.style.width = '100%';
                    progressBar.setAttribute('aria-valuenow', 100);
                    progressBar.classList.remove('progress-bar-animated');
                    progressBar.classList.add('bg-success');
                    progressText.textContent = '100%';
                    @if(session('success'))
                        statusText.textContent = 'Upload completed successfully!';
                    @else
                        statusText.textContent = 'Upload completed with errors. Please review below.';
                    @endif
                }
            @endif

            // Handle form submission with progress bar
            form.addEventListener('submit', function (e) {
                const fileInput = document.getElementById('excel_file');
                const file = fileInput.files[0];
                
                if (!file) {
                    return;
                }

                // Show progress bar
                if (progressContainer) {
                    progressContainer.style.display = 'block';
                    progressBar.classList.remove('bg-success');
                    progressBar.classList.add('progress-bar-animated');
                    progressBar.style.width = '0%';
                    progressBar.setAttribute('aria-valuenow', 0);
                    progressText.textContent = '0%';
                    statusText.textContent = 'Uploading file...';
                }
                
                submitBtn.disabled = true;
                submitText.textContent = 'Uploading...';
                submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Uploading...';
                
                // Simulate progress (since we can't track real progress for synchronous uploads)
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) {
                        progress = 90; // Don't go to 100% until upload completes
                    }
                    if (progressBar) {
                        progressBar.style.width = progress + '%';
                        progressBar.setAttribute('aria-valuenow', progress);
                        progressText.textContent = Math.round(progress) + '%';
                        statusText.textContent = 'Processing file...';
                    }
                }, 500);

                // Store interval ID to clear if needed (though page will reload)
                window.uploadProgressInterval = progressInterval;
            });
        });
    </script>
@endpush