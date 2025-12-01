@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Group Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Groups', 'url' => route('groups.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Group Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />
                <!-- <div>
                                                                                    <a href="{{ route('groups.payment', Hashids::encode($group->id)) }}" class="btn btn-primary">
                                                                                        <i class="bx bx-edit"></i> Add Group Payment
                                                                                    </a>
                                                                                </div> -->
            </div>

            <!-- Group Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <h2 class="text-primary mb-2">{{ $group->name }}</h2>
                            <p class="text-muted mb-0">Group Information</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Group Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-user text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Loan Officer</h5>
                            <h4 class="text-primary mb-0">{{ $group->loanOfficer->name ?? 'N/A' }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-group text-success" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Current Members</h5>
                            <h4 class="text-success mb-0">{{ $group->current_member_count }}</h4>
                            <small class="text-muted">{{ $group->minimum_members }} - {{ $group->maximum_members }}
                                range</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-crown text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Group Leader</h5>
                            <h4 class="text-warning mb-0">{{ $group->groupLeader->name ?? 'N/A' }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="bx bx-calendar text-info" style="font-size: 2rem;"></i>
                            </div>
                            <h5 class="card-title text-muted mb-1">Meeting Schedule</h5>
                            <h4 class="text-info mb-0">
                                @if($group->meeting_day)
                                    {{ ucfirst($group->meeting_day) }}<br>
                                    <small>{{ $group->meeting_time ? $group->meeting_time->format('H:i') : '9:00 AM' }}</small>
                                @else
                                    Not Set
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Cards -->
            <div class="row">
                <!-- Group Information Card -->
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-info-circle me-2"></i>Group Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Branch</label>
                                    <p class="mb-0 fw-bold">{{ $group->branch->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Created Date</label>
                                    <p class="mb-0 fw-bold">{{ $group->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">Last Updated</label>
                                    <p class="mb-0 fw-bold">{{ $group->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Officer Information Card -->
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Loan Officer Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Officer Name</label>
                                    <p class="mb-0 fw-bold">{{ $group->loanOfficer->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Email Address</label>
                                    <p class="mb-0 fw-bold">{{ $group->loanOfficer->email ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">Phone Number</label>
                                    <p class="mb-0 fw-bold">{{ $group->loanOfficer->phone ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Group Leader Information Card -->
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Group Leader Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Group Leader Name</label>
                                    <p class="mb-0 fw-bold">{{ $group->groupLeader->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label text-muted small">Email Address</label>
                                    <p class="mb-0 fw-bold">{{ $group->groupLeader->email ?? 'N/A' }}</p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">Phone Number</label>
                                    <p class="mb-0 fw-bold">{{ $group->groupLeader->phone ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Group Members Management Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-group me-2"></i>Group Members
                                ({{ $group->current_member_count }}/{{ $group->maximum_members }})
                            </h5>
                            <div>
                                @if($group->canAcceptMoreMembers())
                                    <a href="{{ route('group-members.create', Hashids::encode($group->id)) }}"
                                        class="btn btn-light btn-sm me-2">
                                        <i class="bx bx-plus"></i> Add Member
                                    </a>
                                @else
                                    <span class="badge bg-warning text-dark me-2">Group Full</span>
                                @endif
                                @if($group->members->count() > 0 && $availableGroups->count() > 0)
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#transferMemberModal">
                                        <i class="bx bx-transfer"></i> Transfer Member
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if($group->members->count() > 0)
                                <div class="table-responsive">
                                    <table id="groupMembersTable" class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Member</th>
                                                <th>Joined Date</th>
                                                <th>Notes</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bx bx-group text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">No Members Yet</h5>
                                    <p class="text-muted">This group doesn't have any members yet.</p>
                                    @if($group->canAcceptMoreMembers())
                                        <a href="{{ route('group-members.create', Hashids::encode($group->id)) }}"
                                            class="btn btn-primary">
                                            <i class="bx bx-plus"></i> Add First Member
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Group Loans Table -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-credit-card me-2"></i>Group Loans
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="groupLoansTable" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Loan No</th>
                                            <th>Customer No</th>
                                            <th>Customer</th>
                                            <th>Amount (with Interest)</th>
                                            <th>Total Paid</th>
                                            <th>Outstanding Balance</th>
                                            <th>Disbursed On</th>
                                            <th>Expiry</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transfer Member Modal (moved inside content to keep layout intact) -->
            <div class="modal fade" id="transferMemberModal" tabindex="-1" aria-labelledby="transferMemberModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transferMemberModalLabel">Transfer Member</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="transferMemberForm" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="memberSelect" class="form-label">Select Member to Transfer</label>
                                    <select class="form-select" id="memberSelect" name="member_id" required>
                                        <option value="">Choose a member...</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="targetGroupSelect" class="form-label">Transfer to Group</label>
                                    <select class="form-select" id="targetGroupSelect" name="target_group_id" required>
                                        <option value="">Choose target group...</option>
                                        @foreach($availableGroups as $availableGroup)
                                            <option value="{{ $availableGroup->id }}">{{ $availableGroup->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Note:</strong> Members can only be transferred if they have completed all their
                                    loans in
                                    the current group.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Transfer Member</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            border-bottom: none;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
        }

        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.875rem;
        }

        .badge {
            font-size: 0.75em;
            font-weight: 500;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .fw-bold {
            font-weight: 600 !important;
        }

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

@push('scripts')
    <script>
        $(document).ready(function () {
            const membersTable = $('#groupMembersTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ url('group-members-ajax/' . $group->id) }}',
                    dataSrc: 'data',
                    error: function (xhr) {
                        console.error('Group members AJAX error:', xhr.status, xhr.statusText);
                        console.error('Response:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to load members',
                            text: 'Please refresh the page. If it persists, check server logs.'
                        });
                    }
                },
                columns: [
                    { data: 'member', orderable: false, searchable: true },
                    { data: 'joined_date' },
                    { data: 'notes', orderable: false, searchable: true },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                drawCallback: function () {
                    // Ensure click handlers after each draw
                    $('#groupMembersTable .js-remove-member').off('click').on('click', function () {
                        const encodedGroup = $(this).data('encoded-group');
                        const memberId = $(this).data('member-id');
                        const memberName = $(this).data('member-name');
                        removeMember(encodedGroup, memberId, memberName);
                    });
                }
            });

            // Delegate click handler for dynamically loaded remove buttons
            $('#groupMembersTable').on('click', '.js-remove-member', function () {
                const encodedGroup = $(this).data('encoded-group');
                const memberId = $(this).data('member-id');
                const memberName = $(this).data('member-name');
                removeMember(encodedGroup, memberId, memberName);
            });
        });
    </script>
    <script>
        // Delegated click handler to avoid inline JS and escaping issues
            $(document).on('click', '.remove-member-btn', function () {
            const groupId = $(this).data('group-id');
            const memberId = $(this).data('member-id');
            const memberName = $(this).data('member-name');
            const actionUrl = $(this).data('action-url');

            Swal.fire({
                title: 'Remove Member?',
                text: `Are you sure you want to remove "${memberName}" from this group? They will be assigned to the individual group.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = actionUrl || `/groups/${groupId}/members/${memberId}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#groupLoansTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ url('group-loans-ajax/' . $group->id) }}',
                    dataSrc: 'data'
                },
                columns: [
                    { data: 'loan_no' },
                    { data: 'customer_no' },
                    { data: 'customer' },
                    { data: 'amount_with_interest' },
                    { data: 'total_paid' },
                    { data: 'outstanding' },
                    { data: 'disbursed_on' },
                    { data: 'last_repayment_date' },
                    {
                        data: 'show_url',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return '<a href="' + data + '" class="btn btn-sm btn-info">View</a>';
                        }
                    }
                ]
            });
        });

        // Transfer Member Modal functionality
        $('#transferMemberModal').on('show.bs.modal', function () {
            // Populate member select with current group members
            const memberSelect = $('#memberSelect');
            memberSelect.empty().append('<option value="">Choose a member...</option>');

            // Get members from the server via AJAX
            $.ajax({
                url: '{{ url("groups/" . Hashids::encode($group->id) . "/members-for-transfer") }}',
                method: 'GET',
                success: function (response) {
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function (member) {
                            memberSelect.append(`<option value="${member.id}">${member.name}</option>`);
                        });
                    }
                },
                error: function () {
                    console.error('Failed to load members for transfer');
                }
            });
        });

        // Handle transfer form submission
        $('#transferMemberForm').on('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const memberId = formData.get('member_id');
            const targetGroupId = formData.get('target_group_id');

            if (!memberId || !targetGroupId) {
                alert('Please select both member and target group.');
                return;
            }

            Swal.fire({
                title: 'Transfer Member?',
                text: 'Are you sure you want to transfer this member to the selected group?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, transfer',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ url("groups/" . Hashids::encode($group->id) . "/transfer-member") }}',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response?.message || 'Member transferred successfully',
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            const response = xhr.responseJSON;
                            Swal.fire({
                                icon: 'error',
                                title: 'Transfer failed',
                                text: response?.message || 'Unknown error',
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush