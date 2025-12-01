@php
use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Loan Product Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loan Products', 'url' => '#', 'icon' => 'bx bx-credit-card']
        ]" />
        <h6 class="mb-0 text-uppercase">LOAN PRODUCTS</h6>
        <hr />

        <!-- Dashboard Stats -->
        <div class="row row-cols-1 row-cols-lg-4">
            <div class="col mb-4">
                <div class="card radius-10">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Products</p>
                            <h4 class="mb-0">{{ $loanProducts->count() ?? 0 }}</h4>
                        </div>
                        <div class="ms-3">
                            <div
                                class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-credit-card font-size-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col mb-4">
                <div class="card radius-10">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Active Products</p>
                            <h4 class="mb-0">{{ $loanProducts->where('is_active', true)->count() ?? 0 }}</h4>
                        </div>
                        <div class="ms-3">
                            <div
                                class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-check-circle font-size-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loan Products Table -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Loan Products List</h4>
                            @can('create loan product')
                            <div>
                                <a href="{{ route('loan-products.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus"></i> Add Loan Product
                                </a>
                            </div>
                            @endcan
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap w-100" id="loanProductsTable">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">Product Name</th>
                                        <th class="text-nowrap">Type</th>
                                        <th class="text-nowrap">Interest Rate</th>
                                        <th class="text-nowrap">Principal Range</th>
                                        <th class="text-nowrap">Status</th>
                                        <th class="text-center text-nowrap">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loanProducts as $product)
                                    <tr>
                                        <td class="text-nowrap">
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $product->interest_cycle }} cycle</small>
                                            </div>
                                        </td>
                                        <td class="text-nowrap">
                                            <span class="badge bg-primary">{{ ucfirst($product->product_type) }}</span>
                                        </td>
                                        <td class="text-nowrap">
                                            {{ number_format($product->minimum_interest_rate, 2) }}% -
                                            {{ number_format($product->maximum_interest_rate, 2) }}%
                                        </td>
                                        <td class="text-nowrap">
                                            {{ number_format($product->minimum_principal, 0) }} -
                                            {{ number_format($product->maximum_principal, 0) }}
                                        </td>
                                        <td class="text-nowrap">
                                            @if($product->is_active ?? true)
                                            <span class="badge bg-success">Active</span>
                                            @else
                                            <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <div class="btn-group" role="group">
                                                @canany(['view product details', 'admin'])
                                                <a href="{{ route('loan-products.show', Hashids::encode($product->id)) }}"
                                                    class="btn btn-sm btn-outline-info" title="View Details">
                                                    view
                                                </a>
                                                @endcanany

                                                @can('edit loan product')
                                                <a href="{{ route('loan-products.edit', Hashids::encode($product->id)) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Edit Product">
                                                    edit
                                                </a>
                                                @endcan

                                                @can('deactivate loan product')
                                                <button type="button"
                                                    class="btn btn-sm {{ $product->is_active ?? true ? 'btn-outline-warning' : 'btn-outline-success' }} toggle-status-btn"
                                                    title="{{ $product->is_active ?? true ? 'Deactivate' : 'Activate' }} Product"
                                                    data-product-id="{{ Hashids::encode($product->id) }}"
                                                    data-product-name="{{ $product->name }}"
                                                    data-current-status="{{ $product->is_active ?? true ? 'active' : 'inactive' }}">
                                                    {{ $product->is_active ?? true ? 'deactivate' : 'activate' }}
                                                </button>
                                                @endcan

                                                @can('delete loan product')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                    title="Delete Product"
                                                    data-product-id="{{ Hashids::encode($product->id) }}"
                                                    data-product-name="{{ $product->name }}">
                                                    delete
                                                </button>
                                                @endcan
                                            </div>
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

        <!-- Hidden delete forms -->
        @foreach($loanProducts as $product)
        <form id="delete-form-{{ Hashids::encode($product->id) }}"
            action="{{ route('loan-products.destroy', Hashids::encode($product->id)) }}" method="POST"
            style="display: none;">
            @csrf
            @method('DELETE')
        </form>
        @endforeach

    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize DataTable
        var table = $('#loanProductsTable').DataTable({
            responsive: false, // Disable responsive to prevent column collapsing
            order: [
                [0, 'asc']
            ],
            pageLength: 25,
            language: {
                search: "Search products:",
                lengthMenu: "Show _MENU_ products per page",
                info: "Showing _START_ to _END_ of _TOTAL_ products",
                infoEmpty: "Showing 0 to 0 of 0 products",
                infoFiltered: "(filtered from _MAX_ total products)"
            },
            columnDefs: [{
                    targets: 0, // Product Name
                    width: '25%'
                },
                {
                    targets: 1, // Type
                    width: '12%'
                },
                {
                    targets: 2, // Interest Rate
                    width: '18%'
                },
                {
                    targets: 3, // Principal Range
                    width: '20%'
                },
                {
                    targets: 4, // Status
                    width: '10%'
                },
                {
                    targets: -1, // Actions column (last column)
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    width: '15%'
                }
            ],
            initComplete: function() {
                // Re-initialize tooltips after DataTable initializes
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });

        // Handle view button click
        $('#loanProductsTable').on('click', '.view-btn, a[href*="loan-products/show"]', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if (href) {
                window.location.href = href;
            }
        });

        // Handle edit button click
        $('#loanProductsTable').on('click', '.edit-btn, a[href*="loan-products/edit"]', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if (href) {
                window.location.href = href;
            }
        });

        // Delete confirmation with SweetAlert2
        $('#loanProductsTable').on('click', '.delete-btn', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var productName = $(this).data('product-name');

            Swal.fire({
                title: 'Delete Loan Product?',
                text: `Are you sure you want to delete "${productName}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the delete form
                    $(`#delete-form-${productId}`).submit();
                }
            });
        });

        // Toggle status confirmation with SweetAlert2
        $('#loanProductsTable').on('click', '.toggle-status-btn', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var productName = $(this).data('product-name');
            var currentStatus = $(this).data('current-status');
            var newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            var actionText = currentStatus === 'active' ? 'deactivate' : 'activate';

            Swal.fire({
                title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Loan Product?`,
                text: `Are you sure you want to ${actionText} "${productName}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: currentStatus === 'active' ? '#ffc107' : '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${actionText} it!`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit form for status toggle
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/loan-products/${productId}/toggle-status`;

                    var csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    var methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'PATCH';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Success message handling with default toast
        @if(session('success'))
        // Show default toast notification
        var toast = new bootstrap.Toast(document.getElementById('toast-success'));
        document.getElementById('toast-success-message').textContent = '{{ session('
        success ') }}';
        toast.show();
        @endif

        // Error message handling with default toast
        @if(session('error'))
        // Show default toast notification
        var toast = new bootstrap.Toast(document.getElementById('toast-error'));
        document.getElementById('toast-error-message').textContent = '{{ session('
        error ') }}';
        toast.show();
        @endif
    });

    // Duplicate product function
    function duplicateProduct(productId) {
        Swal.fire({
            title: 'Duplicate Product?',
            text: 'This will create a copy of the loan product with "(Copy)" added to the name.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, duplicate it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to create page with duplicate parameter
                window.location.href = `{{ route('loan-products.create') }}?duplicate=${productId}`;
            }
        });
    }

    // Export product function
    function exportProduct(productId) {
        Swal.fire({
            title: 'Export Product?',
            text: 'This will export the loan product configuration as a JSON file.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Export',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create a temporary form to download the export
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('loan-products.show', ':id') }}`.replace(':id', productId);

                var csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                var exportInput = document.createElement('input');
                exportInput.type = 'hidden';
                exportInput.name = 'export';
                exportInput.value = '1';

                form.appendChild(csrfToken);
                form.appendChild(exportInput);
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        });
    }
</script>
@endpush

@push('styles')
<style>
    /* Table styling */
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    /* Prevent table from wrapping */
    #loanProductsTable {
        width: 100% !important;
        table-layout: fixed;
    }

    #loanProductsTable th,
    #loanProductsTable td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Allow product name to wrap slightly */
    #loanProductsTable td:first-child {
        white-space: normal;
        word-wrap: break-word;
    }

    /* Button group styling - prevent wrapping */
    .btn-group {
        display: flex !important;
        gap: 2px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    .btn-group .btn {
        margin-right: 0;
        border-radius: 0.25rem;
        flex-shrink: 0;
        white-space: nowrap;
    }

    /* Badge styling */
    .badge {
        font-size: 0.75em;
        font-weight: 500;
    }

    /* Button sizing */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Hover effects */
    .btn-outline-info:hover,
    .btn-outline-primary:hover,
    .btn-outline-danger:hover {
        transform: translateY(-1px);
        transition: transform 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* DataTable specific styling */
    .dataTables_wrapper .btn-group {
        display: flex !important;
        gap: 2px;
    }

    .dataTables_wrapper .btn {
        display: inline-block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .dataTables_wrapper td:last-child {
        text-align: center !important;
        vertical-align: middle !important;
    }

    /* Table responsive */
    .table-responsive {
        border-radius: 0.5rem;
        overflow-x: auto;
        overflow-y: hidden;
    }

    /* Ensure DataTable wrapper doesn't cause wrapping */
    .dataTables_wrapper {
        width: 100%;
    }

    .dataTables_wrapper .dataTables_scroll {
        overflow-x: auto;
    }

    /* Card styling */
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    /* Tooltip styling */
    .tooltip {
        font-size: 0.875rem;
    }

    /* Product name styling */
    .table td strong {
        color: #495057;
        font-weight: 600;
    }

    .table td small {
        color: #6c757d;
    }

    /* Status badge colors */
    .badge.bg-success {
        background-color: #28a745 !important;
    }

    .badge.bg-danger {
        background-color: #dc3545 !important;
    }

    .badge.bg-primary {
        background-color: #007bff !important;
    }
</style>
@endpush