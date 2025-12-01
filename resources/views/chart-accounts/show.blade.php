@extends('layouts.main')

@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@section('title', 'Chart Account Details')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <!-- Breadcrumbs -->
            <x-breadcrumbs-with-icons :links="[ 
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Chart of Accounts', 'url' => route('accounting.chart-accounts.index'), 'icon' => 'bx bx-spreadsheet'],
                ['label' => 'Account Details', 'url' => '#', 'icon' => 'bx bx-book-open']
            ]" />
            <!-- End Breadcrumbs -->
            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 text-dark fw-bold">
                                <i class="bx bx-book-open me-2 text-primary"></i>
                                Chart Account Details
                            </h4>
                        </div>
                        <div class="d-flex gap-2">
                            @can('edit chart account')
                            <a href="{{ route('accounting.chart-accounts.edit', Hashids::encode($chartAccount->id)) }}"
                                class="btn btn-primary">
                                <i class="bx bx-edit me-1"></i> Edit Account
                            </a>
                            @endcan
                            <a href="{{ route('accounting.chart-accounts.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
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
                                Account Information
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
                                            <span class="fw-bold text-dark">#{{ $chartAccount->id }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-code text-success fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Account Code</small>
                                            <span class="fw-bold text-dark fs-6">{{ $chartAccount->account_code }}</span>
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
                                                class="badge bg-primary fs-6">{{ $chartAccount->accountClassGroup->accountClass->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-folder text-warning fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Account Group</small>
                                            <span
                                                class="fw-bold text-dark fs-6">{{ $chartAccount->accountClassGroup->name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-bookmark text-danger fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Account Name</small>
                                            <span class="fw-bold text-dark fs-5">{{ $chartAccount->account_name }}</span>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bx bx-money text-success fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Account Balance</small>
                                            <span class="fw-bold text-dark fs-6 {{ $accountBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($accountBalance, 2) }}
                                                <small class="text-muted ms-1">TZS</small>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Account Flags & Actions -->
                <div class="col-lg-4">
                    <!-- Account Flags Card -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-gradient-primary text-white">
                            <h6 class="mb-0">
                                <i class="bx bx-flag me-2"></i>
                                Account Flags
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <!-- Cash Flow Impact -->
                            <div class="p-4 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <h6 class="mb-0 fw-semibold text-dark">Cash Flow Impact</h6>
                                            <span
                                                class="badge ms-auto {{ $chartAccount->has_cash_flow ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                                {{ $chartAccount->has_cash_flow ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>

                                        @if($chartAccount->has_cash_flow && $chartAccount->cashFlowCategory)
                                            <div class="bg-light rounded-3 p-3 border-start border-3 border-success">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bx bx-category text-success me-2"></i>
                                                    <span
                                                        class="fw-semibold text-success">{{ $chartAccount->cashFlowCategory->name }}</span>
                                                </div>
                                                @if($chartAccount->cashFlowCategory->description)
                                                    <p class="text-muted small mb-0">
                                                        <i class="bx bx-info-circle me-1"></i>
                                                        {{ $chartAccount->cashFlowCategory->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-muted small mb-0">
                                                <i class="bx bx-info-circle me-1"></i>
                                                No cash flow impact configured
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Equity Impact -->
                            <div class="p-4">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <h6 class="mb-0 fw-semibold text-dark">Equity Impact</h6>
                                            <span
                                                class="badge ms-auto {{ $chartAccount->has_equity ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                                {{ $chartAccount->has_equity ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>

                                        @if($chartAccount->has_equity && $chartAccount->equityCategory)
                                            <div class="bg-light rounded-3 p-3 border-start border-3 border-warning">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="bx bx-category text-warning me-2"></i>
                                                    <span
                                                        class="fw-semibold text-warning">{{ $chartAccount->equityCategory->name }}</span>
                                                </div>
                                                @if($chartAccount->equityCategory->description)
                                                    <p class="text-muted small mb-0">
                                                        <i class="bx bx-info-circle me-1"></i>
                                                        {{ $chartAccount->equityCategory->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-muted small mb-0">
                                                <i class="bx bx-info-circle me-1"></i>
                                                No equity impact configured
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bx bx-cog me-2 text-muted"></i>
                                Quick Actions
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-grid gap-2">
                                @can('edit chart account')
                                <a href="{{ route('accounting.chart-accounts.edit', Hashids::encode($chartAccount->id)) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit Account
                                </a>
                                @endcan
                                @can('delete chart account')
                                <form
                                    action="{{ route('accounting.chart-accounts.destroy', Hashids::encode($chartAccount->id)) }}"
                                    method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                        data-name="{{ $chartAccount->account_name }}">
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