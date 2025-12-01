@extends('layouts.main')

@section('title', 'Cash Deposit Account Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Cash Deposit Accounts', 'url' => route('cash_collateral_types.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => $cashCollateral->name, 'url' => '#', 'icon' => 'bx bx-info-circle']
        ]" />
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-dark fw-bold">
                        <i class="bx bx-bookmark me-2 text-primary"></i>
                        Cash Deposit Account Details
                    </h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('cash_collateral_types.edit', $cashCollateral->id) }}" class="btn btn-primary">
                            <i class="bx bx-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('cash_collateral_types.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Card -->
 <!-- Wrapper Row for Details + Actions -->
        <div class="row">
            <!-- Left Column: Details -->
            <div class="col-md-8">
                <!-- Details Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i> Type Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Name</small>
                                <h6 class="fw-bold text-dark">{{ $cashCollateral->name }}</h6>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted d-block">Status</small>
                                <span class="badge {{ $cashCollateral->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $cashCollateral->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted d-block">Chart Account</small>
                                @if($cashCollateral->chartAccount)
                                    <h6 class="fw-bold text-dark">
                                        {{ $cashCollateral->chartAccount->name }} 
                                        <span class="text-muted">({{ $cashCollateral->chartAccount->account_code }})</span>
                                    </h6>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>

                            <div class="col-12">
                                <small class="text-muted d-block">Description</small>
                                <p class="text-dark">{{ $cashCollateral->description ?? '-' }}</p>
                            </div>

                            <div class="col-md-6">
                                <small class="text-muted d-block">Created At</small>
                                <h6 class="fw-bold text-dark">{{ $cashCollateral->created_at->format('Y-m-d') }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

    <!-- Right Column: Quick Actions -->
            <div class="col-md-4">
                <!-- Quick Actions -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-cog me-2 text-muted"></i> Quick Actions</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-grid gap-2">
                            <a href="{{ route('cash_collateral_types.edit', $cashCollateral->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-edit me-1"></i> Edit
                            </a>
                            <form action="{{ route('cash_collateral_types.destroy', $cashCollateral->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100" data-name="{{ $cashCollateral->name }}">
                                    <i class="bx bx-trash me-1"></i> Delete
                                </button>
                            </form>
                            <a href="{{ route('cash_collateral_types.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>
@endsection
