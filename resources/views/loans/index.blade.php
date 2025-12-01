@extends('layouts.main')

@section('title', 'Loans')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => '#', 'icon' => 'bx bx-credit-card'],
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">LOAN MANAGEMENT</h6>
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#openingBalanceModal">
                    <i class="bx bx-upload me-1"></i> Opening Balance
                </button>
            </div>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- Loan Calculator -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-calculator fs-1 text-info"></i>
                                            </div>
                                            <h5 class="card-title">Loan Calculator</h5>
                                            <p class="card-text">Simulate loan scenarios, view schedules and export results.</p>
                                            <a href="{{ route('loan-calculator.index') }}" class="btn btn-info position-relative">
                                                <i class="bx bx-calculator me-1"></i> Open Calculator
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @can('view loans')
                                    <!-- Active Loans -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-primary position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ $stats['active'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-building fs-1 text-primary"></i>
                                                </div>
                                                <h5 class="card-title">Active Loans</h5>
                                                <p class="card-text">Manage your company loans disbursed to customers.</p>
                                                <a href="{{ route('loans.list') }}" class="btn btn-primary position-relative">
                                                    <i class="bx bx-cog me-1"></i> View Loans
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view applied loans')
                                    <!-- Applied Loans -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['applied'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-plus-circle fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Applied Loans</h5>
                                                <p class="card-text">Manage and initiate loan applications.</p>
                                                <a href="{{ route('loans.by-status', 'applied') }}"
                                                    class="btn btn-success position-relative">
                                                    <i class="bx bx-file-plus me-1"></i> View Applications
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan

                                @can('view checked loans')
                                    <!-- Checked Applications -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-teal position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">{{ $stats['checked'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-check-circle fs-1 text-secondary"></i>
                                                </div>
                                                <h5 class="card-title">Checked Applications</h5>
                                                <p class="card-text">Manage and check applied loans.</p>
                                                <a href="{{ route('loans.by-status', 'checked') }}"
                                                    class="btn btn-secondary position-relative">
                                                    <i class="bx bx-check me-1"></i> View Applications
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view approved loans')
                                    <!-- Approved Applications -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-purple position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info">{{ $stats['approved'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-check-circle fs-1 text-info"></i>
                                                </div>
                                                <h5 class="card-title">Approved Applications</h5>
                                                <p class="card-text">Manage and verify applied loans.</p>
                                                <a href="{{ route('loans.by-status', 'approved') }}"
                                                    class="btn btn-info position-relative">
                                                    <i class="bx bx-verify me-1"></i> View Applications
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view authorized loans')
                                    <!-- Authorized Applications -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-orange position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">{{ $stats['authorized'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-badge-check fs-1 text-warning"></i>
                                                </div>
                                                <h5 class="card-title">Authorized Applications</h5>
                                                <p class="card-text">Manage and approve applied loans.</p>
                                                <a href="{{ route('loans.by-status', 'authorized') }}"
                                                    class="btn btn-warning position-relative">
                                                    <i class="bx bx-badge-check me-1"></i> View Applications
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view defaulted loans')
                                    <!-- Defaulted Loans -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-danger position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $stats['defaulted'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-error fs-1 text-danger"></i>
                                                </div>
                                                <h5 class="card-title">Defaulted Loans</h5>
                                                <p class="card-text">Manage all defaulted loans.</p>
                                                <a href="{{ route('loans.by-status', 'defaulted') }}"
                                                    class="btn btn-danger position-relative">
                                                    <i class="bx bx-error me-1"></i> View Loans
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view rejected loans')
                                    <!-- Rejected Applications -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-danger position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $stats['rejected'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-x-circle fs-1 text-danger"></i>
                                                </div>
                                                <h5 class="card-title">Rejected Applications</h5>
                                                <p class="card-text">Manage all rejected loan applications.</p>
                                                <a href="{{ route('loans.by-status', 'rejected') }}"
                                                    class="btn btn-danger position-relative">
                                                    <i class="bx bx-x-circle me-1"></i> View Applications
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view writeoff loans')
                                    <!-- Written Off Loans -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-danger position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $stats['written_off'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-x-circle fs-1 text-danger"></i>
                                                </div>
                                                <h5 class="card-title">Written Off Loans</h5>
                                                <p class="card-text">Manage all written off loans.</p>
                                                <a href="{{ route('loans.writtenoff') }}" class="btn btn-danger">
                                                    <i class="bx bx-x-circle me-1"></i> View Loans
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                                @can('view completed loans')
                                    <!-- Completed Loans -->
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card border-success position-relative">
                                            <span
                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ $stats['completed'] ?? 0 }}</span>
                                            <div class="card-body text-center">
                                                <div class="mb-3">
                                                    <i class="bx bx-check-circle fs-1 text-success"></i>
                                                </div>
                                                <h5 class="card-title">Completed Loans</h5>
                                                <p class="card-text">Manage all completed loans.</p>
                                                <a href="{{ route('loans.by-status', 'completed') }}" class="btn btn-success">
                                                    <i class="bx bx-check-circle me-1"></i> View Loans
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Opening Balance Modal -->
    <div class="modal fade" id="openingBalanceModal" tabindex="-1" aria-labelledby="openingBalanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="openingBalanceModalLabel">Opening Balance - Bulk Loan Creation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="openingBalanceForm" action="{{ route('loans.opening-balance.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <!-- Download Template Button -->
                            <div class="col-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Step 1: Download Template</h6>
                                    <button type="button" id="downloadTemplateBtn" class="btn btn-outline-primary btn-sm">
                                        <i class="bx bx-download me-1"></i> Download Template
                                    </button>
                                </div>
                                <small class="text-muted">Download the CSV template and fill in your loan data</small>
                            </div>

                            <!-- Product Selection -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loan Product <span class="text-danger">*</span></label>
                                <select name="product_id" class="form-select @error('product_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Product</option>
                                    @foreach($products ?? [] as $product)
                                        <option value="{{ $product->id ?? '' }}" {{ old('product_id') == ($product->id ?? '') ? 'selected' : '' }}>
                                            {{ $product->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Branch Selection -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Branch</option>
                                    @foreach($branches ?? [] as $branch)
                                        <option value="{{ $branch->id ?? '' }}" {{ old('branch_id') == ($branch->id ?? '') ? 'selected' : '' }}>
                                            {{ $branch->name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Chart Account Selection -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chart Account <span class="text-danger">*</span></label>
                                <select name="chart_account_id"
                                    class="form-select @error('chart_account_id') is-invalid @enderror select2-single" required>
                                    <option value="">Select Chart Account</option>
                                    @foreach($chartAccounts ?? [] as $account)
                                        <option value="{{ $account->id ?? '' }}" {{ old('chart_account_id') == ($account->id ?? '') ? 'selected' : '' }}>
                                            {{ $account->account_name ?? '' }} ({{ $account->account_code ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('chart_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- CSV File Upload -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CSV File <span class="text-danger">*</span></label>
                                <input type="file" name="csv_file"
                                    class="form-control @error('csv_file') is-invalid @enderror" accept=".csv" required>
                                @error('csv_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Upload the filled CSV template</small>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Instructions:</h6>
                            <ul class="mb-0">
                                <li>Select a loan product first, then download the template</li>
                                <li>Interest cycle will be automatically taken from the selected product</li>
                                <li>Fill in the loan data in the CSV template</li>
                                <li>Ensure customer numbers exist in the system</li>
                                <li>Loans will be created with 'active' status</li>
                                <li>Repayments will be processed automatically if amount_paid > 0</li>
                                <li>Process runs in background - you'll be notified when complete</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="bx bx-upload me-1"></i> Process Opening Balance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <footer class="page-footer">
        <p class="mb-0">Copyright © {{ date('Y') }}. All right reserved. -- By SAFCO FINTECH</p>
    </footer>

@endsection

@push('styles')
    <style>
        .border-purple {
            border-color: #6f42c1 !important;
        }

        .text-purple {
            color: #6f42c1 !important;
        }

        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }

        .btn-purple:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
            color: white;
        }

        .border-orange {
            border-color: #fd7e14 !important;
        }

        .text-orange {
            color: #fd7e14 !important;
        }

        .btn-orange {
            background-color: #fd7e14;
            border-color: #fd7e14;
            color: white;
        }

        .btn-orange:hover {
            background-color: #e8690b;
            border-color: #e8690b;
            color: white;
        }

        .border-teal {
            border-color: #20c997 !important;
        }

        .text-teal {
            color: #20c997 !important;
        }

        .btn-teal {
            background-color: #20c997;
            border-color: #20c997;
            color: white;
        }

        .btn-teal:hover {
            background-color: #1ba37e;
            border-color: #1ba37e;
            color: white;
        }

        .border-danger {
            border-color: #dc3545 !important;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #bb2d3b;
            color: white;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const downloadTemplateBtn = document.getElementById('downloadTemplateBtn');
            const productSelect = document.querySelector('select[name="product_id"]');

            downloadTemplateBtn.addEventListener('click', function () {
                const productId = productSelect.value;

                if (!productId) {
                    alert('Please select a loan product first before downloading the template.');
                    productSelect.focus();
                    return;
                }

                // Create download URL with product_id parameter
                const downloadUrl = '{{ route("loans.opening-balance.template") }}?product_id=' + productId;

                // Create a temporary link and trigger download
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = 'opening_balance_template_{{ date("Y-m-d") }}.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
@endpush