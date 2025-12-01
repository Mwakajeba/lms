@extends('layouts.main')

@section('title', 'Bank Account Details')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 text-dark fw-bold">
                            <i class="bx bx-bank me-2 text-primary"></i>
                            Bank Account Details
                        </h4>
                    </div>
                    <div class="d-flex gap-2">
                        @can('edit bank account')
                        <a href="{{ route('accounting.bank-accounts.edit', Hashids::encode($bankAccount->id)) }}"
                            class="btn btn-primary">
                            <i class="bx bx-edit me-1"></i> Edit Account
                        </a>
                        @endcan

                        @can('view bank accounts')
                        <a href="{{ route('accounting.bank-accounts') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to List
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Left Column - Account Information -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            Bank Account Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <i class="bx bx-hash text-primary fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Account ID</small>
                                        <span class="fw-bold text-dark">#{{ $bankAccount->id }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <i class="bx bx-bank text-success fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Bank Name</small>
                                        <span class="fw-bold text-dark fs-6">{{ $bankAccount->name }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <i class="bx bx-credit-card text-info fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Account Number</small>
                                        <span class="fw-bold text-dark fs-6">{{ $bankAccount->account_number }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <i class="bx bx-book text-warning fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Chart Account</small>
                                        <span
                                            class="fw-bold text-dark fs-6">{{ $bankAccount->chartAccount->account_name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <i class="bx bx-category text-danger fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Account Class</small>
                                        <span
                                            class="badge bg-primary fs-6">{{ $bankAccount->chartAccount->accountClassGroup->accountClass->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle p-2 me-3">
                                        <i class="bx bx-folder text-secondary fs-5"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Account Group</small>
                                        <span
                                            class="fw-bold text-dark fs-6">{{ $bankAccount->chartAccount->accountClassGroup->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Quick Actions -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bx bx-cog me-2 text-muted"></i>
                            Quick Actions
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            @can('edit bank account')
                            <a href="{{ route('accounting.bank-accounts.edit', Hashids::encode($bankAccount->id)) }}"
                                class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-edit me-1"></i> Edit Account
                            </a>
                            @endcan
                            @can('delete bank account')
                            <form
                                action="{{ route('accounting.bank-accounts.destroy', Hashids::encode($bankAccount->id)) }}"
                                method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                    data-name="{{ $bankAccount->name }}">
                                    <i class="bx bx-trash me-1"></i> Delete Account
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
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
    // Delete confirmation
    $('.delete-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const name = form.find('button[type="submit"]').data('name');

        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to delete "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form[0].submit();
            }
        });
    });
</script>
@endpush