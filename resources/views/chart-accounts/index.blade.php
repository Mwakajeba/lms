@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Chart of Accounts')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <!-- Breadcrumbs -->
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Chart of Accounts', 'url' => '#', 'icon' => 'bx bx-spreadsheet']
             ]" />
            <!-- End Breadcrumbs -->

            <div class="row row-cols-1 row-cols-lg-3">
                <div class="col">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0">Total Accounts</p>
                                    <h4 class="font-weight-bold">{{ $stats['total'] ?? 0 }}</h4>
                                </div>
                                <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-spreadsheet'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0">Cash Flow Accounts</p>
                                    <h4 class="font-weight-bold">{{ $stats['cash_flow'] ?? 0 }}</h4>
                                </div>
                                <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-money-withdraw'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0">Equity Accounts</p>
                                    <h4 class="font-weight-bold">{{ $stats['equity'] ?? 0 }}</h4>
                                </div>
                                <div class="widgets-icons bg-gradient-primary text-white"><i class='bx bx-pie-chart-alt'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end row-->

            <h6 class="mb-0 text-uppercase">CHART OF ACCOUNTS</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Chart of Accounts</h5>
                        @can('create chart account')
                        <a href="{{ route('accounting.chart-accounts.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> Add New Account
                        </a>
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table id="chartAccountsTable" class="table table-hover dt-responsive nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Account Class</th>
                                    <th>Account Group</th>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Cash Flow</th>
                                    <th>Cash Flow Category</th>
                                    <th>Equity</th>
                                    <th>Equity Category</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
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
    <!--end page wrapper -->
    <!--start overlay-->
    <div class="overlay toggle-icon"></div>
    <!--end overlay-->
    <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
    <!--End Back To Top Button-->
    <footer class="page-footer">
        <p class="mb-0">Copyright © 2021. All right reserved.</p>
    </footer>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize DataTable with Ajax
            var table = $('#chartAccountsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("accounting.chart-accounts.data") }}',
                    type: 'GET',
                    error: function(xhr, error, code) {
                        console.error('DataTables Ajax Error:', error, code);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to load chart accounts data. Please refresh the page.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                columns: [
                    { data: 'account_class_name', name: 'accountClassGroup.accountClass.name', title: 'Account Class', orderable: true, searchable: true },
                    { data: 'account_group_name', name: 'accountClassGroup.name', title: 'Account Group', orderable: true, searchable: true },
                    { data: 'account_code', name: 'account_code', title: 'Account Code', orderable: true, searchable: true },
                    { data: 'account_name', name: 'account_name', title: 'Account Name', orderable: true, searchable: true },
                    { data: 'cash_flow_badge', name: 'has_cash_flow', title: 'Cash Flow', orderable: true, searchable: false },
                    { data: 'cash_flow_category_name', name: 'cashFlowCategory.name', title: 'Cash Flow Category', orderable: true, searchable: false },
                    { data: 'equity_badge', name: 'has_equity', title: 'Equity', orderable: true, searchable: false },
                    { data: 'equity_category_name', name: 'equityCategory.name', title: 'Equity Category', orderable: true, searchable: false },
                    { data: 'formatted_created_at', name: 'created_at', title: 'Created At', orderable: true, searchable: true },
                    { data: 'actions', name: 'actions', title: 'Actions', orderable: false, searchable: false }
                ],
                responsive: true,
                order: [[2, 'asc']], // Sort by account code ascending by default
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                language: {
                    search: "",
                    searchPlaceholder: "Search chart accounts...",
                    processing: '<div class="d-flex justify-content-center align-items-center p-3"><div class="spinner-border text-primary me-2" role="status"><span class="visually-hidden">Loading...</span></div><span class="text-primary">Loading chart accounts...</span></div>',
                    emptyTable: "No chart accounts found",
                    info: "Showing _START_ to _END_ of _TOTAL_ chart accounts",
                    infoEmpty: "Showing 0 to 0 of 0 chart accounts",
                    infoFiltered: "(filtered from _MAX_ total chart accounts)",
                    lengthMenu: "Show _MENU_ chart accounts per page",
                    zeroRecords: "No matching chart accounts found"
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
                        targets: [0, 1, 2, 3], // Priority columns for responsive
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
            $('#chartAccountsTable').on('click', '.delete-account-btn', function () {
                const accountId = $(this).data('account-id');
                const accountName = $(this).data('account-name');
                const accountCode = $(this).data('account-code');

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete chart account "${accountCode} - ${accountName}"? This action cannot be undone!`,
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
                            text: 'Please wait while we delete the chart account.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Use AJAX instead of form submission to maintain loading state
                        $.ajax({
                            url: `/accounting/chart-accounts/${accountId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Chart account has been deleted successfully.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    table.ajax.reload(null, false); // Reload table without resetting pagination
                                });
                            },
                            error: function(xhr) {
                                let errorMessage = 'An error occurred while deleting the chart account.';
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