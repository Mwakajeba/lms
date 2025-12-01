@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Penalty Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Penalties', 'url' => route('accounting.penalties.index'), 'icon' => 'bx bx-error-circle'],
            ['label' => 'Penalty Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">PENALTY DETAILS</h6>
                    <p class="text-muted mb-0">View penalty information</p>
                </div>
                <div>
                    <a href="{{ route('accounting.penalties.edit', Hashids::encode($penalty->id)) }}"
                        class="btn btn-primary me-2">
                        Edit Penalty
                    </a>
                    <a href="{{ route('accounting.penalties.index') }}" class="btn btn-secondary">
                        Back to Penalties
                    </a>
                </div>
            </div>
            <hr />

            <!-- Prominent Header Card -->
            <div class="card radius-10 bg-gradient-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div
                            class="avatar-lg bg-white text-danger rounded-circle me-3 d-flex align-items-center justify-content-center">
                            <i class="bx bx-error-circle font-size-32"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="mb-1">{{ $penalty->name }}</h3>
                            <p class="mb-0 opacity-75">{{ $penalty->description ?: 'No description provided' }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            {!! $penalty->status_badge !!}
                            {!! $penalty->penalty_type_badge !!}
                            <span class="badge bg-light text-dark">
                                <i class="bx bx-calendar me-1"></i>
                                {{ $penalty->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column - Main Information -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card radius-10 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Penalty Name</label>
                                    <p class="form-control-plaintext">{{ $penalty->name }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Chart Account</label>
                                    <p class="form-control-plaintext">{{ $penalty->chartAccount->account_name ?? 'N/A' }}
                                        ({{ $penalty->chartAccount->account_code ?? 'N/A' }})</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Penalty Type</label>
                                    <p class="form-control-plaintext">{!! $penalty->penalty_type_badge !!}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Amount</label>
                                    <p class="form-control-plaintext">
                                        <strong class="text-primary fs-5">{{ $penalty->formatted_amount }}</strong>
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <p class="form-control-plaintext">{!! $penalty->status_badge !!}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Deduction Type</label>
                                    <p class="form-control-plaintext">{!! $penalty->deduction_type_badge !!}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Penalty Configuration Details -->
                    <div class="card radius-10 mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Penalty Configuration</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Calculation Method</label>
                                    <p class="form-control-plaintext">
                                        @if($penalty->isFixed())
                                            Fixed amount of {{ number_format($penalty->amount, 2) }}
                                        @else
                                            {{ number_format($penalty->amount, 2) }}% of the base amount
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Deduction Base</label>
                                    <p class="form-control-plaintext">
                                        @php
                                            $deductionTypeOptions = App\Models\Penalty::getDeductionTypeOptions();
                                            $deductionLabel = $deductionTypeOptions[$penalty->deduction_type] ?? 'Unknown';
                                        @endphp
                                        {!! $penalty->deduction_type_badge !!}
                                        <br>
                                        <small class="text-muted">{{ $deductionLabel }}</small>
                                    </p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <p class="form-control-plaintext">
                                        {{ $penalty->description ?: 'No description provided' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Sidebar Information -->
                <div class="col-lg-4">
                    <!-- Organization Information -->
                    <div class="card radius-10 mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bx bx-building me-2"></i>Organization</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-building me-2"></i>Company
                                </label>
                                <p class="form-control-plaintext">{{ $penalty->company->name ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-map-pin me-2"></i>Branch
                                </label>
                                <p class="form-control-plaintext">{{ $penalty->branch->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Information -->
                    <div class="card radius-10 mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bx bx-history me-2"></i>Audit Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-user me-2"></i>Created By
                                </label>
                                <p class="form-control-plaintext">{{ $penalty->createdBy->name ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-calendar me-2"></i>Created Date
                                </label>
                                <p class="form-control-plaintext">{{ $penalty->created_at->format('M d, Y H:i A') }}</p>
                            </div>
                            @if($penalty->updatedBy)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">
                                        <i class="bx bx-user-check me-2"></i>Last Updated By
                                    </label>
                                    <p class="form-control-plaintext">{{ $penalty->updatedBy->name }}</p>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bx bx-time me-2"></i>Last Updated
                                </label>
                                <p class="form-control-plaintext">{{ $penalty->updated_at->format('M d, Y H:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card radius-10">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('accounting.penalties.edit', $penalty) }}" class="btn btn-primary">
                                    <i class="bx bx-edit me-1"></i>Edit Penalty
                                </a>
                                <a href="{{ route('accounting.penalties.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Penalties
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
                                <button type="button" class="btn btn-outline-danger" onclick="deletePenalty()">
                                    <i class="bx bx-trash me-1"></i>Delete Penalty
                                </button>
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
        const penaltyName = '{{ $penalty->name }}';

        function changeStatus(status) {
            const action = status === 'active' ? 'activate' : 'deactivate';
            const icon = status === 'active' ? 'success' : 'warning';

            Swal.fire({
                title: `${action.charAt(0).toUpperCase() + action.slice(1)} Penalty`,
                text: `Are you sure you want to ${action} this penalty?`,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: status === 'active' ? '#28a745' : '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${action} it!`,
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = $('<form>', {
                        'method': 'POST',
                        'action': '{{ route("accounting.penalties.changeStatus", $penalty) }}'
                    });

                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': '{{ csrf_token() }}'
                    }));

                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_method',
                        'value': 'PATCH'
                    }));

                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': 'status',
                        'value': status
                    }));

                    $('body').append(form);

                    // Submit form and handle response
                    form.submit().done(function (response) {
                        // Show success toast notification
                        toastr.success(`Penalty has been ${action}d successfully`);
                    }).fail(function (xhr) {
                        // Show error toast notification
                        toastr.error('An error occurred while updating the status');
                    });
                }
            });
        }

        function deletePenalty() {
            Swal.fire({
                title: 'Delete Penalty',
                text: `Are you sure you want to delete "${penaltyName}"? This action cannot be undone.`,
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
                        'action': '{{ route("accounting.penalties.destroy", $penalty) }}'
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

                    // Submit form and handle response
                    form.submit().done(function (response) {
                        // Show success toast notification
                        toastr.success('Penalty has been deleted successfully');
                        // Redirect to index page after short delay
                        setTimeout(() => {
                            window.location.href = '{{ route("accounting.penalties.index") }}';
                        }, 1000);
                    }).fail(function (xhr) {
                        // Show error toast notification
                        toastr.error('An error occurred while deleting the penalty');
                    });
                }
            });
        }

        // Initialize toastr options
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // Show success toast if session has success message
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif
    </script>
@endpush

@push('styles')
    <style>
        .bg-gradient-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        .font-size-32 {
            font-size: 2rem;
        }

        .fs-5 {
            font-size: 1.25rem !important;
        }
    </style>
@endpush