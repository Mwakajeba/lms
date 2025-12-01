@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Penalties Management')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Penalties', 'url' => '#', 'icon' => 'bx bx-error-circle']
        ]" />

            <h6 class="mb-0 text-uppercase">PENALTIES MANAGEMENT</h6>
            <hr />

            <!-- Dashboard Stats -->
            <div class="row row-cols-1 row-cols-lg-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Penalties</p>
                                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-error-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Active Penalties</p>
                                <h4 class="mb-0">{{ $stats['active'] }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-ohhappiness text-white"><i class='bx bx-check-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Fixed Penalties</p>
                                <h4 class="mb-0">{{ $stats['fixed'] }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-blues text-white"><i class='bx bx-dollar'></i></div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Percentage Penalties</p>
                                <h4 class="mb-0">{{ $stats['percentage'] }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-percentage'></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deduction Type Stats -->
            <div class="row row-cols-1 row-cols-lg-2 mb-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Outstanding Amount Deduction</p>
                                <h4 class="mb-0">{{ $stats['outstanding_amount'] }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-orange text-white"><i class='bx bx-money'></i></div>
                        </div>
                    </div>
                </div>

                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Principal Deduction</p>
                                <h4 class="mb-0">{{ $stats['principal'] }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-home'></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">Penalties List</h4>
                                <div>
                                    <a href="{{ route('accounting.penalties.create') }}" class="btn btn-primary">
                                        Add Penalty
                                    </a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap w-100" id="penaltiesTable">
                                    <thead>
                                        <tr>
                                            <th width="15%">Name</th>
                                            <th width="15%">Penalty Income Account</th>
                                            <th width="15%">Penalty Receivable Account</th>
                                            <th width="10%">Type</th>
                                            <th width="10%">Amount</th>
                                            <th width="15%">Deduction Type</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Created By</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($penalties as $penalty)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('accounting.penalties.show', Hashids::encode($penalty->id)) }}"
                                                        class="text-primary fw-bold">
                                                        {{ $penalty->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $penalty->penaltyIncomeAccount->account_name ?? 'N/A' }}</td>
                                                <td>{{ $penalty->penaltyReceivablesAccount->account_name ?? 'N/A' }}</td>
                                                <td>{!! $penalty->penalty_type_badge !!}</td>
                                                <td>{{ $penalty->formatted_amount }}</td>
                                                <td>{!! $penalty->deduction_type_badge !!}</td>
                                                <td>{!! $penalty->status_badge !!}</td>
                                                <td>{{ $penalty->createdBy->name ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('accounting.penalties.show', Hashids::encode($penalty->id)) }}"
                                                            class="btn btn-sm btn-outline-primary">
                                                            View
                                                        </a>
                                                        <a href="{{ route('accounting.penalties.edit', Hashids::encode($penalty->id)) }}"
                                                            class="btn btn-sm btn-outline-warning">
                                                            Edit
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger delete-penalty-btn"
                                                            title="Delete" data-penalty-id="{{ Hashids::encode($penalty->id) }}"
                                                            data-penalty-name="{{ $penalty->name }}">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bx bx-error-circle font-size-48 mb-3"></i>
                                                        <h5>No Penalties Found</h5>
                                                        <p>Start by creating your first penalty configuration.</p>
                                                        <a href="{{ route('accounting.penalties.create') }}"
                                                            class="btn btn-primary">
                                                            <i class="bx bx-plus me-2"></i>Add First Penalty
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
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

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#penaltiesTable').DataTable({
                responsive: true,
                order: [[0, 'asc']],
                pageLength: 25,
                language: {
                    search: "Search penalties:",
                    lengthMenu: "Show _MENU_ penalties per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ penalties",
                    infoEmpty: "Showing 0 to 0 of 0 penalties",
                    infoFiltered: "(filtered from _MAX_ total penalties)",
                    emptyTable: "No penalties available",
                    zeroRecords: "No matching penalties found"
                }
            });

            // Delete penalty functionality with SweetAlert
            $('.delete-penalty-btn').on('click', function () {
                const penaltyId = $(this).data('penalty-id');
                const penaltyName = $(this).data('penalty-name');

                Swal.fire({
                    title: 'Delete Penalty',
                    text: `Are you sure you want to delete "${penaltyName}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('<form>', {
                            'method': 'POST',
                            'action': `/accounting/penalties/${penaltyId}`
                        });

                        form.append($('<input>', {
                            'type': 'hidden',
                            'name': '_token',
                            'value': '{{ csrf_token() }}'
                        }));

                        form.append($('<input>', {
                            'type': 'hidden',
                            'name': '_method',
                            'value': 'DELETE'
                        }));

                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush