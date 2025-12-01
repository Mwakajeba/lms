@extends('layouts.main')

@section('title', 'Cash Deposits')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Deposit Accounts', 'url' => '#', 'icon' => 'bx bx-credit-card']
        ]" />
        <h6 class="mb-0 text-uppercase">DEPOSIT ACCOUNTS</h6>
        <hr />

        <!-- Stats Card -->
        <div class="row row-cols-1 row-cols-lg-4 mb-4">
            <div class="col">
                <div class="card radius-10">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Deposits</p>
                            <h4 class="mb-0">{{ $totalCollaterals }}</h4>
                        </div>
                        <div class="ms-3">
                            <div class="avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bx bx-wallet font-size-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Cash Deposits List</h4>
                    @can('create cash collateral')
                    <a href="{{ route('cash_collaterals.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Add Account
                    </a>
                    @endcan
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" id="collateralTable">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Created At</th>
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#collateralTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('cash_collaterals.index') }}',
                type: 'GET'
            },
            columns: [
                { data: 'customer_name', name: 'customer.name' },
                { data: 'type_name', name: 'type.name' },
                { data: 'formatted_amount', name: 'amount', searchable: false },
                { data: 'formatted_date', name: 'created_at' },
                { 
                    data: 'actions', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-center'
                }
            ],
            responsive: true,
            order: [[3, 'desc']], // Order by created_at desc
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search deposits...",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    searchable: false,
                    responsivePriority: 1
                },
                {
                    targets: [0, 1],
                    responsivePriority: 2
                }
            ],
            drawCallback: function() {
                // Reinitialize delete forms after table redraw
                initializeDeleteForms();
            }
        });

        // Function to initialize delete forms
        function initializeDeleteForms() {
            $('.delete-form').off('submit').on('submit', function(e) {
                e.preventDefault();
                
                const form = this;
                const itemName = $(this).find('button[data-name]').data('name');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You want to delete this deposit account?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Deleting...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Submit the form
                        form.submit();
                    }
                });
            });
        }

        // Initialize delete forms on page load
        initializeDeleteForms();
    });
</script>
@endpush