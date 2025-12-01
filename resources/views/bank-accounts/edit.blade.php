@extends('layouts.main')

@section('title', 'Edit Bank Account')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Bank Accounts', 'url' => route('accounting.bank-accounts'), 'icon' => 'bx bx-bank'],
            ['label' => 'Edit Account', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

            <!-- Header Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0 text-dark fw-bold">
                                <i class="bx bx-bank me-2 text-primary"></i>
                                Edit Bank Account
                            </h4>
                        </div>
                        @can('view bank accounts')
                        <div>
                            <a href="{{ route('accounting.bank-accounts') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-edit me-2"></i>
                                Edit Bank Account Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            @include('bank-accounts.form')
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