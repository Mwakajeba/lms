@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Fees Management')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fees', 'url' => '#', 'icon' => 'bx bx-dollar-circle']
        ]" />

            <h6 class="mb-0 text-uppercase">FEES MANAGEMENT</h6>
            <hr />

            <!-- Dashboard Stats -->
            <div class="row row-cols-1 row-cols-lg-4">
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Fees</p>
                                <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-burning text-white"><i class='bx bx-dollar-circle'></i></div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Active Fees</p>
                                <h4 class="mb-0 text-success">{{ $stats['active'] }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-ohhappiness text-white"><i class='bx bx-check-circle'></i></div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Fixed Fees</p>
                                <h4 class="mb-0 text-info">{{ $stats['fixed'] }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-blues text-white"><i class='bx bx-money'></i></div>
                        </div>
                    </div>
                </div>
                <div class="col mb-4">
                    <div class="card radius-10">
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Percentage Fees</p>
                                <h4 class="mb-0 text-warning">{{ $stats['percentage'] }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-cosmic text-white"><i class='bx bx-percentage'></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fees Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title mb-0">Fees List</h4>
                                <div>
                                    <a href="{{ route('accounting.fees.create') }}" class="btn btn-primary">
                                        Add Fee
                                    </a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered dt-responsive nowrap w-100" id="feesTable">
                                    <thead>
                                        <tr>
                                            <th width="15%">Name</th>
                                            <th width="15%">Chart Account</th>
                                            <th width="10%">Type</th>
                                            <th width="10%">Amount</th>
                                            <th width="15%">Deduction Criteria</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Company</th>
                                            <th width="10%">Branch</th>
                                            <th width="10%">Created By</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fees as $fee)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('accounting.fees.show', Hashids::encode($fee->id)) }}"
                                                        class="text-primary fw-bold">
                                                        {{ $fee->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $fee->chartAccount->account_name ?? 'N/A' }}</td>
                                                <td>{!! $fee->fee_type_badge !!}</td>
                                                <td>{{ $fee->formatted_amount }}</td>
                                                <td>
                                                    {!! $fee->deduction_criteria_badge !!}
                                                </td>
                                                <td>{!! $fee->status_badge !!}</td>
                                                <td>{{ $fee->company->name ?? 'N/A' }}</td>
                                                <td>{{ $fee->branch->name ?? 'N/A' }}</td>
                                                <td>{{ $fee->createdBy->name ?? 'N/A' }}</td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('accounting.fees.show', Hashids::encode($fee->id)) }}"
                                                            class="btn btn-sm btn-outline-primary">
                                                            View
                                                        </a>
                                                        <a href="{{ route('accounting.fees.edit', Hashids::encode($fee->id)) }}"
                                                            class="btn btn-sm btn-outline-warning">
                                                            Edit
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger delete-fee-btn" title="Delete"
                                                            data-fee-id="{{ Hashids::encode($fee->id) }}"
                                                            data-fee-name="{{ $fee->name }}">
                                                            Delete
                                                        </button>
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
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#feesTable').DataTable({
                responsive: true,
                order: [[0, 'asc']],
                pageLength: 25,
                scrollX: true,
                columnDefs: [
                    {
                        targets: -1,
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    search: "Search fees:",
                    lengthMenu: "Show _MENU_ fees per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ fees",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });

            // Delete fee with SweetAlert confirmation
            $('.delete-fee-btn').on('click', function () {
                const feeId = $(this).data('fee-id');
                const feeName = $(this).data('fee-name');

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete fee "${feeName}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('<form>', {
                            'method': 'POST',
                            'action': `/accounting/fees/${feeId}`
                        });
                        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
                        form.append($('<input>', { 'type': 'hidden', 'name': '_method', 'value': 'DELETE' }));
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .table-responsive {
            overflow-x: auto;
        }

        #feesTable {
            width: 100% !important;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .d-flex.gap-2 {
            gap: 0.5rem !important;
        }
    </style>
@endpush