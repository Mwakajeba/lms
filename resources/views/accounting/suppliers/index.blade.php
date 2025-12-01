@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Supplier Management')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Suppliers', 'url' => '#', 'icon' => 'bx bx-store']
        ]" />

            <h6 class="mb-0 text-uppercase">SUPPLIER MANAGEMENT</h6>
            <hr />

            <!-- Dashboard Stats -->
            <div class="row row-cols-1 row-cols-lg-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Suppliers</p>
                                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-store font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Active Suppliers</p>
                                <h4 class="mb-0 text-success">{{ $stats['active'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-check-circle font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Inactive Suppliers</p>
                                <h4 class="mb-0 text-warning">{{ $stats['inactive'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-pause-circle font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Blacklisted</p>
                                <h4 class="mb-0 text-danger">{{ $stats['blacklisted'] }}</h4>
                            </div>
                            <div class="ms-3">
                                <div
                                    class="avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bx bx-block font-size-24"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suppliers Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">Suppliers List</h4>
                                <div>
                                    @can('create supplier')
                                    <a href="{{ route('accounting.suppliers.create') }}" class="btn btn-primary">
                                        Add Supplier
                                    </a>
                                    @endcan
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover dt-responsive nowrap" id="suppliersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Contact Info</th>
                                            <th>Location</th>
                                            <th>Business Details</th>
                                            <th>Status</th>
                                            <th>Branch</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via Ajax -->
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

@push('styles')
<style>
    .supplier-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #fff;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .supplier-name {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .supplier-contact {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .supplier-location {
        font-size: 0.875rem;
        color: #495057;
    }
    
    .supplier-business {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .status-badge {
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    
    .dataTables_wrapper .dataTables_info {
        margin-top: 1rem;
    }
    
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1rem;
    }
    
    /* Loading states */
    .dataTables_processing {
        background: rgba(255, 255, 255, 0.9) !important;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }
    
    .swal2-popup {
        z-index: 9999 !important;
    }
    
    /* Enhanced loading spinner */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize DataTable with Ajax
            var table = $('#suppliersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("accounting.suppliers.data") }}',
                    type: 'GET',
                    error: function(xhr, error, code) {
                        console.error('DataTables Ajax Error:', error, code);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to load suppliers data. Please refresh the page.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                columns: [
                    { data: 'supplier_name', name: 'name', title: 'Name', orderable: true, searchable: true },
                    { data: 'contact_info', name: 'contact_info', title: 'Contact Info', orderable: false, searchable: false },
                    { data: 'location', name: 'address', title: 'Location', orderable: false, searchable: true },
                    { data: 'business_details', name: 'business_details', title: 'Business Details', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'status', title: 'Status', orderable: true, searchable: true },
                    { data: 'branch_name', name: 'branch.name', title: 'Branch', orderable: true, searchable: true },
                    { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
                ],
                responsive: true,
                order: [[0, 'asc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                language: {
                    search: "",
                    searchPlaceholder: "Search suppliers...",
                    processing: '<div class="d-flex justify-content-center align-items-center p-3"><div class="spinner-border text-primary me-2" role="status"><span class="visually-hidden">Loading...</span></div><span class="text-primary">Loading suppliers...</span></div>',
                    emptyTable: "No suppliers found",
                    info: "Showing _START_ to _END_ of _TOTAL_ suppliers",
                    infoEmpty: "Showing 0 to 0 of 0 suppliers",
                    infoFiltered: "(filtered from _MAX_ total suppliers)",
                    lengthMenu: "Show _MENU_ suppliers per page",
                    zeroRecords: "No matching suppliers found"
                },
                columnDefs: [
                    {
                        targets: -1, // Actions column
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        responsivePriority: 1
                    },
                    {
                        targets: [0, 1, 2], // Priority columns for responsive
                        responsivePriority: 2
                    }
                ],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                drawCallback: function(settings) {
                    // Reinitialize tooltips after each draw
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });

            // Handle delete button clicks with event delegation
            $('#suppliersTable').on('click', '.delete-supplier-btn', function () {
                const supplierId = $(this).data('supplier-id');
                const supplierName = $(this).data('supplier-name');

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete supplier "${supplierName}"? This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading immediately
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait while we delete the supplier.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Use AJAX instead of form submission to maintain loading state
                        $.ajax({
                            url: `/accounting/suppliers/${supplierId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Supplier has been deleted successfully.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    table.ajax.reload(null, false); // Reload table without resetting pagination
                                });
                            },
                            error: function(xhr) {
                                let errorMessage = 'An error occurred while deleting the supplier.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                
                                Swal.fire({
                                    title: 'Error!',
                                    text: errorMessage,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush