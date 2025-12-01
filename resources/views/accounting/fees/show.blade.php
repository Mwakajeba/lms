@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Fee Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fees', 'url' => route('accounting.fees.index'), 'icon' => 'bx bx-dollar-circle'],
            ['label' => 'Fee Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">FEE DETAILS</h6>
                    <p class="text-muted mb-0">View fee information</p>
                </div>
                <div>
                    <a href="{{ route('accounting.fees.edit', Hashids::encode($fee->id)) }}" class="btn btn-primary me-2">
                        Edit Fee
                    </a>
                    <a href="{{ route('accounting.fees.index') }}" class="btn btn-secondary">
                        Back to Fees
                    </a>
                </div>
            </div>
            <hr />

            <div class="row">
                <!-- Fee Header Card -->
                <div class="col-12 mb-4">
                    <div class="card radius-10 bg-primary">
                        <div class="card-body py-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div
                                        class="avatar-lg bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-dollar-circle text-white" style="font-size: 2rem"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="text-white mb-2">{{ $fee->name }}</h4>
                                    @if($fee->description)
                                        <p class="text-white text-opacity-75 mb-2">{{ Str::limit($fee->description, 100) }}</p>
                                    @endif
                                    <div class="d-flex align-items-center gap-2">
                                        {!! $fee->status_badge !!}
                                        {!! $fee->fee_type_badge !!}
                                        <span class="badge bg-white bg-opacity-25 text-white">
                                            <i class="bx bx-calendar me-1"></i>
                                            {{ $fee->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="col-md-6 mb-4">
                    <div class="card radius-10">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Fee Name</label>
                                    <p class="mb-0">{{ $fee->name }}</p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Chart Account</label>
                                    <p class="mb-0">
                                        <i class="bx bx-account me-1"></i>
                                        {{ $fee->chartAccount->account_name ?? 'N/A' }}
                                        @if($fee->chartAccount)
                                            <span class="text-muted">({{ $fee->chartAccount->account_code }})</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Fee Type</label>
                                    <p class="mb-0">
                                        {!! $fee->fee_type_badge !!}
                                    </p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Amount</label>
                                    <p class="mb-0 fw-bold text-primary h4">
                                        {{ $fee->formatted_amount }}
                                    </p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Deduction Criteria</label>
                                    <p class="mb-0">
                                        @php
                                            $deductionCriteriaOptions = App\Models\Fee::getDeductionCriteriaOptions();
                                            $criteriaLabel = $deductionCriteriaOptions[$fee->deduction_criteria] ?? 'N/A';
                                        @endphp
                                        <i class="bx bx-calendar-check me-1"></i>
                                        {{ $criteriaLabel }}
                                    </p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Status</label>
                                    <p class="mb-0">
                                        {!! $fee->status_badge !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Organization Details -->
                <div class="col-md-6 mb-4">
                    <div class="card radius-10">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-building me-2"></i>Organization Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Company</label>
                                    <p class="mb-0">
                                        <i class="bx bx-building me-1"></i>
                                        {{ $fee->company->name ?? 'N/A' }}
                                    </p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Branch</label>
                                    <p class="mb-0">
                                        <i class="bx bx-map me-1"></i>
                                        {{ $fee->branch->name ?? 'N/A' }}
                                    </p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Created By</label>
                                    <p class="mb-0">
                                        <i class="bx bx-user me-1"></i>
                                        {{ $fee->createdBy->name ?? 'N/A' }}
                                    </p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Created Date</label>
                                    <p class="mb-0">
                                        <i class="bx bx-calendar me-1"></i>
                                        {{ $fee->created_at->format('M d, Y H:i') }}
                                    </p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold text-muted">Last Updated</label>
                                    <p class="mb-0">
                                        <i class="bx bx-time me-1"></i>
                                        {{ $fee->updated_at->format('M d, Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                @if($fee->description)
                    <div class="col-12 mb-4">
                        <div class="card radius-10">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bx bx-detail me-2"></i>Description</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $fee->description }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('accounting.fees.edit', $fee) }}" class="btn btn-primary">
                                    <i class="bx bx-edit me-1"></i>Edit Fee
                                </a>
                                <a href="{{ route('accounting.fees.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Fees
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="bx bx-toggle-right me-1"></i>Change Status
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="changeStatus('active')">
                                                <i class="bx bx-check-circle me-1"></i>Activate
                                            </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="changeStatus('inactive')">
                                                <i class="bx bx-pause-circle me-1"></i>Deactivate
                                            </a></li>
                                    </ul>
                                </div>
                                <button type="button" class="btn btn-outline-danger delete-fee-btn"
                                    data-fee-id="{{ $fee->id }}" data-fee-name="{{ $fee->name }}">
                                    <i class="bx bx-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for status change -->
    <form id="statusForm" method="POST" style="display: none;">
        @csrf
        @method('PATCH')
        <input type="hidden" id="statusInput" name="status">
    </form>

@endsection

@push('scripts')
    <script>
        function changeStatus(status) {
            const statusLabels = {
                'active': 'Active',
                'inactive': 'Inactive'
            };

            Swal.fire({
                title: 'Change Status',
                text: `Are you sure you want to change the status to ${statusLabels[status]}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('statusInput').value = status;
                    document.getElementById('statusForm').action = '{{ route("accounting.fees.changeStatus", $fee) }}';
                    document.getElementById('statusForm').submit();
                }
            });
        }

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
    </script>
@endpush