@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Account Class Group Details')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Account Class Groups', 'url' => route('accounting.account-class-groups.index'), 'icon' => 'bx bx-category'],
            ['label' => 'Group Details', 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />

            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 text-dark fw-bold">
                                <i class="bx bx-category me-2 text-primary"></i>
                                Account Class Group Details
                            </h4>
                        </div>
                        <div class="d-flex gap-2">
                            @can('edit account class group')
                            <a href="{{ route('accounting.account-class-groups.edit', Hashids::encode($accountClassGroup->id)) }}"
                                class="btn btn-primary">
                                <i class="bx bx-edit me-1"></i> Edit Group
                            </a>
                            @endcan
                            <a href="{{ route('accounting.account-class-groups.index') }}"
                                class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <!-- Left Column - Basic Information -->
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-info-circle me-2"></i>
                                Group Information
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
                                            <small class="text-muted d-block">Group ID</small>
                                            <span class="fw-bold text-dark">#{{ $accountClassGroup->id }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-tag text-success fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Group Code</small>
                                            <span
                                                class="fw-bold text-dark">{{ $accountClassGroup->group_code ?? 'Not Assigned' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-category text-info fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Account Class</small>
                                            <span
                                                class="badge bg-primary fs-6">{{ $accountClassGroup->accountClass->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-folder text-warning fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Group Name</small>
                                            <span class="fw-bold text-dark fs-6">{{ $accountClassGroup->name }}</span>
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
                                @can('edit account class group')
                                <a href="{{ route('accounting.account-class-groups.edit', Hashids::encode($accountClassGroup->id)) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit Group
                                </a>
                                @endcan
                                @can('delete account class group')
                                <form
                                    action="{{ route('accounting.account-class-groups.destroy', Hashids::encode($accountClassGroup->id)) }}"
                                    method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                        data-name="{{ $accountClassGroup->name }}">
                                        <i class="bx bx-trash me-1"></i> Delete Group
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