@extends('layouts.main')

@section('title', 'Payment Vouchers')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <!-- Breadcrumb -->
            <div class="row">
                <div class="col-12">
                    <div class="page-breadcrumb d-flex align-items-center">
                        <div class="me-auto">
                            <x-breadcrumbs-with-icons :links="[
                                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                                ['label' => 'Payment Vouchers', 'url' => '#', 'icon' => 'bx bx-receipt']
                            ]" />
                        </div>
                        @can('create payment voucher')
                        <div class="ms-auto d-flex gap-2">
                            <a href="{{ route('accounting.payment-vouchers.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus"></i> New Payment Voucher
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
            <h6 class="mb-0 text-uppercase">PAYMENT VOUCHERS</h6>
            <hr />

            <!-- Dashboard Stats -->
            <div class="row row-cols-1 row-cols-lg-4 mb-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Payments</p>
                                <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-receipt'></i></div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">This Month</p>
                                <h4 class="mb-0">{{ $stats['this_month'] ?? 0 }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-calendar'></i></div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Amount</p>
                                <h4 class="mb-0">{{ number_format($stats['total_amount'] ?? 0, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-primary text-white"><i class='bx bx-dollar'></i></div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">This Month Amount</p>
                                <h4 class="mb-0">{{ number_format($stats['this_month_amount'] ?? 0, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white"><i class='bx bx-money'></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap w-100" id="paymentVouchersTable">
                                    <thead>
                                        <tr>
                                            <th width="10%">Date</th>
                                            <th width="12%">Reference</th>
                                            <th width="12%">Type</th>
                                            <th width="12%">Bank Account</th>
                                            <th width="12%">Payee</th>
                                            <th width="12%">Description</th>
                                            <th width="10%">Amount</th>
                                            <th width="10%">Status</th>
                                            <th width="8%">Actions</th>
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

@push("scripts")
    <script>
        $(document).ready(function () {
            // Initialize DataTable with Ajax
            var table = $('#paymentVouchersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("accounting.payment-vouchers.data") }}',
                    type: 'GET',
                    error: function(xhr, error, code) {
                        console.error('DataTables Ajax Error:', error, code);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to load payment vouchers data. Please refresh the page.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                columns: [
                    { data: 'formatted_date', name: 'date', title: 'Date', orderable: true, searchable: true },
                    { data: 'reference_link', name: 'reference', title: 'Reference', orderable: true, searchable: true },
                    { data: 'reference_type_badge', name: 'reference_type', title: 'Type', orderable: true, searchable: true },
                    { data: 'bank_account_name', name: 'bankAccount.name', title: 'Bank Account', orderable: true, searchable: true },
                    { data: 'payee_info', name: 'payee_info', title: 'Payee', orderable: false, searchable: false },
                    { data: 'description_limited', name: 'description', title: 'Description', orderable: false, searchable: true, className: 'text-start' },
                    { data: 'formatted_amount', name: 'amount', title: 'Amount', orderable: true, searchable: false },
                    { data: 'status_badge', name: 'approved', title: 'Status', orderable: true, searchable: false },
                    { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
                ],
                responsive: true,
                order: [[0, 'desc']], // Sort by date descending by default
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                language: {
                    search: "",
                    searchPlaceholder: "Search payment vouchers...",
                    processing: '<div class="d-flex justify-content-center align-items-center p-3"><div class="spinner-border text-primary me-2" role="status"><span class="visually-hidden">Loading...</span></div><span class="text-primary">Loading payment vouchers...</span></div>',
                    emptyTable: "No payment vouchers found",
                    info: "Showing _START_ to _END_ of _TOTAL_ payment vouchers",
                    infoEmpty: "Showing 0 to 0 of 0 payment vouchers",
                    infoFiltered: "(filtered from _MAX_ total payment vouchers)",
                    lengthMenu: "Show _MENU_ payment vouchers per page",
                    zeroRecords: "No matching payment vouchers found"
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
                        targets: 5, // Amount column
                        className: 'text-end',
                        responsivePriority: 2
                    },
                    {
                        targets: [0, 1, 2], // Priority columns for responsive
                        responsivePriority: 3
                    }
                ],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                drawCallback: function(settings) {
                    // Reinitialize tooltips after each draw
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });

            // Handle delete button clicks with event delegation
            $('#paymentVouchersTable').on('click', '.delete-payment-btn', function () {
                const paymentId = $(this).data('payment-id');
                const paymentReference = $(this).data('payment-reference');

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete payment voucher "${paymentReference}"? This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait while we delete the payment voucher.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Use AJAX instead of form submission to maintain loading state
                        console.log("Deleting payment with ID:", paymentId);
                        console.log("Delete URL:", `/accounting/payment-vouchers/${paymentId}`);
                        
                        $.ajax({
                            url: `{{ url('accounting/payment-vouchers') }}/${paymentId}`,
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: "DELETE"
                            },
                            success: function(response) {
                                console.log("Delete success:", response);
                                Swal.fire({
                                    title: "Deleted!",
                                    text: "Payment voucher has been deleted successfully.",
                                    icon: "success",
                                    confirmButtonText: "OK"
                                }).then(() => {
                                    table.ajax.reload(null, false); // Reload table without resetting pagination
                                });
                            },
                            error: function(xhr) {
                                console.log("Delete error:", xhr);
                                console.log("Response:", xhr.responseText);
                                let errorMessage = "An error occurred while deleting the payment voucher.";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                
                                Swal.fire({
                                    title: "Error!",
                                    text: errorMessage,
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .dataTables_wrapper {
            margin-top: 1rem;
        }

        .dataTables_length select {
            min-width: 80px;
        }

        .dataTables_filter input {
            min-width: 200px;
        }

        .table th {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .d-flex.gap-1>* {
            margin-right: 0.25rem;
        }

        .d-flex.gap-1>*:last-child {
            margin-right: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dataTables_filter input {
                min-width: 150px;
            }

            .table-responsive {
                font-size: 0.8rem;
            }

            .btn-sm {
                padding: 0.2rem 0.4rem;
                font-size: 0.7rem;
            }
        }

        /* DataTable pagination styling */
        .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
            margin-left: 2px;
            border: 1px solid #dee2e6;
            background-color: #fff;
            color: #495057;
            cursor: pointer;
        }

        .dataTables_paginate .paginate_button:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
        }

        .dataTables_paginate .paginate_button.current {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .dataTables_paginate .paginate_button.disabled {
            color: #6c757d;
            cursor: not-allowed;
            background-color: #fff;
            border-color: #dee2e6;
        }
    </style>
@endpush
