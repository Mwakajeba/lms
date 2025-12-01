@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Supplier Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Suppliers', 'url' => route('accounting.suppliers.index'), 'icon' => 'bx bx-store'],
            ['label' => 'Supplier Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">SUPPLIER DETAILS</h6>
                    <p class="text-muted mb-0">View supplier information</p>
                </div>
                <div>
                    @can('edit supplier')
                    <a href="{{ route('accounting.suppliers.edit', Hashids::encode($supplier->id)) }}"
                        class="btn btn-primary me-2">
                        Edit Supplier
                    </a>
                    @endcan
                    @can('view suppliers')
                    <a href="{{ route('accounting.suppliers.index') }}" class="btn btn-secondary">
                        Back to Suppliers
                    </a>
                    @endcan
                </div>
            </div>
            <hr />

            <div class="row">
                <!-- Supplier Header Card -->
                <div class="col-12 mb-4">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div
                                    class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-4">
                                    <i class="bx bx-store font-size-32"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="mb-1">{{ $supplier->name }}</h4>
                                    @if($supplier->company_registration_name)
                                        <p class="text-muted mb-2">{{ $supplier->company_registration_name }}</p>
                                    @endif
                                    <div class="d-flex align-items-center">
                                        {!! $supplier->status_badge !!}
                                        <span class="ms-3 text-muted">
                                            <i class="bx bx-calendar me-1"></i>
                                            Created: {{ $supplier->created_at->format('M d, Y') }}
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
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Supplier Name</label>
                                    <p class="mb-0">{{ $supplier->name }}</p>
                                </div>

                                @if($supplier->email)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Email Address</label>
                                        <p class="mb-0">
                                            <i class="bx bx-envelope me-2"></i>
                                            <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                                        </p>
                                    </div>
                                @endif

                                @if($supplier->phone)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Phone Number</label>
                                        <p class="mb-0">
                                            <i class="bx bx-phone me-2"></i>
                                            <a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>
                                        </p>
                                    </div>
                                @endif

                                @if($supplier->address || $supplier->region)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Address</label>
                                        <p class="mb-0">
                                            <i class="bx bx-map-pin me-2"></i>
                                            {{ $supplier->full_address }}
                                        </p>
                                    </div>
                                @endif

                                @if($supplier->branch)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Branch</label>
                                        <p class="mb-0">
                                            <i class="bx bx-building me-2"></i>
                                            {{ $supplier->branch->name }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business & Legal Information -->
                <div class="col-md-6 mb-4">
                    <div class="card radius-10">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-building me-2"></i>Business & Legal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($supplier->company_registration_name)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Company Registration Name</label>
                                        <p class="mb-0">{{ $supplier->company_registration_name }}</p>
                                    </div>
                                @endif

                                @if($supplier->tin_number)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">TIN Number</label>
                                        <p class="mb-0">
                                            <i class="bx bx-id-card me-2"></i>
                                            {{ $supplier->tin_number }}
                                        </p>
                                    </div>
                                @endif

                                @if($supplier->vat_number)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">VAT Number</label>
                                        <p class="mb-0">
                                            <i class="bx bx-receipt me-2"></i>
                                            {{ $supplier->vat_number }}
                                        </p>
                                    </div>
                                @endif

                                @if($supplier->products_or_services)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Products or Services</label>
                                        <p class="mb-0">{{ $supplier->products_or_services }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Banking Information -->
                <div class="col-md-6 mb-4">
                    <div class="card radius-10">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bx bx-credit-card me-2"></i>Banking Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @if($supplier->bank_name)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Bank Name</label>
                                        <p class="mb-0">
                                            <i class="bx bx-bank me-2"></i>
                                            {{ $supplier->bank_name }}
                                        </p>
                                    </div>
                                @endif

                                @if($supplier->bank_account_number)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Bank Account Number</label>
                                        <p class="mb-0">
                                            <i class="bx bx-credit-card me-2"></i>
                                            {{ $supplier->bank_account_number }}
                                        </p>
                                    </div>
                                @endif

                                @if($supplier->account_name)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Account Name</label>
                                        <p class="mb-0">{{ $supplier->account_name }}</p>
                                    </div>
                                @endif

                                @if(!$supplier->bank_name && !$supplier->bank_account_number && !$supplier->account_name)
                                    <div class="col-12">
                                        <p class="text-muted mb-0">No banking information available</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="col-md-6 mb-4">
                    <div class="card radius-10">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bx bx-cog me-2"></i>System Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <div class="mb-2">{!! $supplier->status_badge !!}</div>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Created By</label>
                                    <p class="mb-0">
                                        <i class="bx bx-user me-2"></i>
                                        {{ optional($supplier->createdBy)->name ?? 'System' }}
                                    </p>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Created Date</label>
                                    <p class="mb-0">
                                        <i class="bx bx-calendar me-2"></i>
                                        {{ $supplier->created_at->format('F d, Y \a\t g:i A') }}
                                    </p>
                                </div>

                                @if($supplier->updated_at != $supplier->created_at)
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-bold">Last Updated</label>
                                        <p class="mb-0">
                                            <i class="bx bx-calendar-check me-2"></i>
                                            {{ $supplier->updated_at->format('F d, Y \a\t g:i A') }}
                                            @if($supplier->updatedBy)
                                                by {{ $supplier->updatedBy->name }}
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card radius-10">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Quick Actions</h6>
                                    <p class="text-muted mb-0">Manage this supplier</p>
                                </div>
                                <div>
                                    <a href="{{ route('accounting.suppliers.edit', $supplier) }}"
                                        class="btn btn-primary me-2">
                                        Edit Supplier
                                    </a>

                                    @can('change supplier status')
                                    <!-- Status Change Dropdown -->
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            Change Status
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="changeStatus('active')">
                                                    Activate
                                                </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="changeStatus('inactive')">
                                                    Deactivate
                                                </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="changeStatus('blacklisted')">
                                                    Blacklist
                                                </a></li>
                                        </ul>
                                    </div>
                                    @endcan

                                    @can('delete supplier')
                                    <button type="button" class="btn btn-outline-danger ms-2 delete-supplier-btn"
                                        data-supplier-id="{{ $supplier->id }}" data-supplier-name="{{ $supplier->name }}">
                                        Delete
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Change Form -->
    <form id="statusForm" method="POST" style="display: none;">
        @csrf @method('PATCH')
        <input type="hidden" name="status" id="statusInput">
    </form>

@endsection

@push('scripts')
    <script>
        function changeStatus(status) {
            const statusLabels = {
                'active': 'Active',
                'inactive': 'Inactive',
                'blacklisted': 'Blacklisted'
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
                    document.getElementById('statusForm').action = '{{ route("accounting.suppliers.changeStatus", $supplier) }}';
                    document.getElementById('statusForm').submit();
                }
            });
        }

        // SweetAlert delete confirmation
        $('.delete-supplier-btn').on('click', function () {
            const supplierId = $(this).data('supplier-id');
            const supplierName = $(this).data('supplier-name');

            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete supplier "${supplierName}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form and submit it
                    const form = $('<form>', {
                        'method': 'POST',
                        'action': `/accounting/suppliers/${supplierId}`
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
    </script>
@endpush