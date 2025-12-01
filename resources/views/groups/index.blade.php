@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Group Management')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Groups', 'url' => '#', 'icon' => 'bx bx-group']
        ]" />
            <h6 class="mb-0 text-uppercase">GROUPS</h6>
            <hr />

            <!-- Dashboard Stats -->
            <div class="row row-cols-1 row-cols-lg-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Groups</p>
                                <h4 class="mb-0">{{ $groups->count() ?? 0 }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-group'></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Active Groups</p>
                                <h4 class="mb-0">{{ $groups->count() ?? 0 }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-group'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Groups Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            @can('create group')
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">Groups List</h4>
                                <div>
                                    <a href="{{ route('groups.create') }}" class="btn btn-primary">
                                        <i class="bx bx-plus"></i> Add Group
                                    </a>
                                </div>
                            </div>
                            @endcan

                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap w-100" id="groupsTable">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap">Group Name</th>
                                            <th class="text-nowrap">Loan Officer</th>
                                            <th class="text-nowrap">Branch</th>
                                            <th class="text-nowrap">Total Loans</th>
                                            <th class="text-nowrap">Created Date</th>
                                            <th class="text-center text-nowrap">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($groups as $group)
                                            <tr>
                                                <td class="text-nowrap">
                                                    <div>
                                                        <strong>{{ $group->name }}</strong>
                                                    </div>
                                                </td>
                                                <td class="text-nowrap">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="avatar-sm bg-light-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <i class="bx bx-user font-size-16"></i>
                                                        </div>
                                                        <div>
                                                            <strong>{{ $group->loanOfficer->name ?? 'N/A' }}</strong>
                                                            <br>
                                                            <small
                                                                class="text-muted">{{ $group->loanOfficer->email ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-nowrap">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="avatar-sm bg-light-success rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <i class="bx bx-building font-size-16"></i>
                                                        </div>
                                                        <div>
                                                            <strong>{{ $group->branch->name ?? 'N/A' }}</strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-nowrap">
                                                    <span class="badge bg-info">{{ $group->loans_count ?? 0 }} loans</span>
                                                    {{-- TODO: Uncomment when Loan model is properly set up
                                                    <span class="badge bg-info">{{ $group->loans->count() ?? 0 }} loans</span>
                                                    --}}
                                                </td>
                                                <td class="text-nowrap">
                                                    {{ $group->created_at->format('M d, Y') }}
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <div class="btn-group" role="group">
                                                        @can('view group details')
                                                        <a href="{{ route('groups.show', Hashids::encode($group->id)) }}"
                                                            class="btn btn-sm btn-outline-info" title="View Details">
                                                            View
                                                        </a>
                                                        @endcan

                                                        @can('edit group')
                                                        <a href="{{ route('groups.edit', Hashids::encode($group->id)) }}"
                                                            class="btn btn-sm btn-outline-primary" title="Edit Group">
                                                            Edit
                                                        </a>
                                                        @endcan

                                                        @can('delete group')
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                            title="Delete Group"
                                                            data-group-id="{{ Hashids::encode($group->id) }}"
                                                            data-group-name="{{ json_encode($group->name) }}">
                                                            Delete
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
            @foreach($groups as $group)
                <form id="delete-form-{{ Hashids::encode($group->id) }}"
                    action="{{ route('groups.destroy', Hashids::encode($group->id)) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize DataTable
            var table = $('#groupsTable').DataTable({
                responsive: false,
                order: [[0, 'asc']],
                pageLength: 25,
                language: {
                    search: "Search groups:",
                    lengthMenu: "Show _MENU_ groups per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ groups",
                    infoEmpty: "Showing 0 to 0 of 0 groups",
                    infoFiltered: "(filtered from _MAX_ total groups)"
                },
                columnDefs: [
                    {
                        targets: 0, // Group Name
                        width: '25%'
                    },
                    {
                        targets: 1, // Loan Officer
                        width: '30%'
                    },
                    {
                        targets: 2, // Branch
                        width: '15%'
                    },
                    {
                        targets: 3, // Total Loans
                        width: '15%'
                    },
                    {
                        targets: 4, // Created Date
                        width: '15%'
                    },
                    {
                        targets: -1, // Actions column
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '15%'
                    }
                ],
                initComplete: function () {
                    // Re-initialize tooltips after DataTable initializes
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                }
            });

            // Handle view button click
            $('#groupsTable').on('click', '.view-btn, a[href*="groups/show"]', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                if (href) {
                    window.location.href = href;
                }
            });

            // Handle edit button click
            $('#groupsTable').on('click', '.edit-btn, a[href*="groups/edit"]', function (e) {
                e.preventDefault();
                var href = $(this).attr('href');
                if (href) {
                    window.location.href = href;
                }
            });

            // Delete confirmation with SweetAlert2
            $('#groupsTable').on('click', '.delete-btn', function (e) {
                e.preventDefault();
                var groupId = $(this).data('group-id');
                var groupName = $(this).data('group-name');

                Swal.fire({
                    title: 'Delete Group?',
                    text: `Are you sure you want to delete "${groupName}"? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit the delete form
                        $(`#delete-form-${groupId}`).submit();
                    }
                });
            });

            // Success message handling with default toast
            @if(session('success'))
                // Show default toast notification
                var toast = new bootstrap.Toast(document.getElementById('toast-success'));
                document.getElementById('toast-success-message').textContent = '{{ session('success') }}';
                toast.show();
            @endif

                // Error message handling with default toast
                @if(session('error'))
                    // Show default toast notification
                    var toast = new bootstrap.Toast(document.getElementById('toast-error'));
                    document.getElementById('toast-error-message').textContent = '{{ session('error') }}';
                    toast.show();
                @endif
                                        });
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
        #groupsTable {
            width: 100% !important;
            table-layout: fixed;
        }

        #groupsTable th,
        #groupsTable td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Allow group name to wrap slightly */
        #groupsTable td:first-child {
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

        /* Group name styling */
        .table td strong {
            color: #495057;
            font-weight: 600;
        }

        .table td small {
            color: #6c757d;
        }

        /* Status badge colors */
        .badge.bg-info {
            background-color: #17a2b8 !important;
        }

        .badge.bg-primary {
            background-color: #007bff !important;
        }

        /* Avatar styling */
        .avatar-sm {
            width: 2.5rem;
            height: 2.5rem;
        }

        .bg-light-primary {
            background-color: rgba(13, 110, 253, 0.1) !important;
            color: #0d6efd !important;
        }
    </style>
@endpush