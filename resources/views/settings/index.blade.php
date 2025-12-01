@extends('layouts.main')

@section('title', 'Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => '#', 'icon' => 'bx bx-cog']
        ]" />
        <h6 class="mb-0 text-uppercase">SETTINGS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @can('manage system configurations')
                        <h4 class="card-title mb-4">System Settings</h4>

                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if(isset($errors) && $errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bx bx-error-circle me-2"></i>
                            Please fix the following errors:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif
                        @endcan

                        <div class="row">
                            <!-- Company Settings -->
                            @can('manage campany setting')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-building fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Company Settings</h5>
                                        <p class="card-text">Manage your company information and preferences.</p>
                                        <a href="{{ route('settings.company') }}" class="btn btn-primary">
                                            <i class="bx bx-cog me-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            <!-- Branch Settings -->
                            @can('manage branch setting')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-git-branch fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Branch Settings</h5>
                                        <p class="card-text">Manage branches and their configurations.</p>
                                        <a href="{{ route('settings.branches') }}" class="btn btn-success">
                                            <i class="bx bx-cog me-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan


                            <!-- User Settings -->
                            @can('manage user setting')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-user fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">User Settings</h5>
                                        <p class="card-text">Manage user preferences and permissions.</p>
                                        <a href="{{ route('settings.user') }}" class="btn btn-info">
                                            <i class="bx bx-cog me-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            <!-- System Settings -->
                            @can('view system configurations')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-cog fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">System Settings</h5>
                                        <p class="card-text">Configure system-wide settings and preferences.</p>
                                        <a href="{{ route('settings.system') }}" class="btn btn-warning">
                                            <i class="bx bx-cog me-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan


                            <!-- Backup Settings -->
                            @can('view backup settings')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-data fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Backup Settings</h5>
                                        <p class="card-text">Manage data backup and restore operations.</p>
                                        <a href="{{ route('settings.backup') }}" class="btn btn-danger">
                                            <i class="bx bx-cog me-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            <!-- AI Assistant
                                                                @can('use AI assistant')
                                                                <div class="col-md-6 col-lg-4 mb-4">
                                                                    <div class="card border-purple">
                                                                        <div class="card-body text-center">
                                                                            <div class="mb-3">
                                                                                <i class="bx bx-bot fs-1 text-purple"></i>
                                                                            </div>
                                                                            <h5 class="card-title">AI Assistant</h5>
                                                                            <p class="card-text">Get intelligent reports and insights with AI-powered
                                                                                analysis.</p>
                                                                            <a href="{{ route('settings.ai') }}" class="btn btn-purple">
                                                                                <i class="bx bx-bot me-1"></i> Start Chat
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                @endcan -->

                            <!-- Roles & Permissions -->
                            @can('manage role & permission')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-secondary">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-shield fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="card-title">Roles & Permissions</h5>
                                        <p class="card-text">Manage user roles, permissions, and access control.</p>
                                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-shield me-1"></i> Role, Permissions & Menu
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
<!-- 
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-globe fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">Language Test</h5>
                                        <p class="card-text">Test the multi-language functionality of the system.</p>
                                        <a href="{{ route('test.language') }}" class="btn btn-info">
                                            <i class="bx bx-globe me-1"></i> Test Language
                                        </a>
                                    </div>
                                </div>
                            </div> -->

                            <!-- Fees Settings -->
                            @can('manage fee setting')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-teal">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-dollar-circle fs-1 text-teal"></i>
                                        </div>
                                        <h5 class="card-title">Fees Management</h5>
                                        <p class="card-text">Manage service fees, charges, and payment structures.</p>
                                        <a href="{{ route('accounting.fees.index') }}" class="btn btn-teal">
                                            <i class="bx bx-list-ul me-1"></i> Manage Fees
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            <!-- Penalties Settings -->
                            @can('manage penalty setting')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-error-circle fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Penalties Management</h5>
                                        <p class="card-text">Configure late payment penalties and fee structures.</p>
                                        <a href="{{ route('accounting.penalties.index') }}" class="btn btn-danger">
                                            <i class="bx bx-list-ul me-1"></i> Manage Penalties
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            <!-- Filetypes Settings -->
                            @can('manage filetype setting')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-file fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">File Types Management</h5>
                                        <p class="card-text">Configure the filetypes to be used in uploading documents.
                                        </p>
                                        <a href="{{ route('settings.filetypes.index') }}" class="btn btn-primary">
                                            <i class="bx bx-list-ul me-1"></i> Manage File Types
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan
                            <!-- activity logs Settings -->
                            @can('view logs activity')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-file fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Activity Logs Management</h5>
                                        <p class="card-text">
                                            Monitor and track all user activities in the system.
                                            Ensure accountability and review actions performed by users.
                                        </p>
                                        <a href="{{ route('settings.logs.index') }}" class="btn btn-primary">
                                            <i class="bx bx-list-ul me-1"></i>Manage Logs
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Subscription Management -->
                            @can('manage subscription')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-credit-card fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">Subscription Management</h5>
                                        <p class="card-text">
                                            Manage your subscription plans, billing information, and payment history.
                                            Upgrade, downgrade, or cancel your subscription as needed.
                                        </p>
                                        <a href="{{ route('settings.subscription') }}" class="btn btn-warning">
                                            <i class="bx bx-credit-card me-1"></i> Manage Subscription
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            <!-- Manual Subscription Management (Super Admin) -->
                            @role('super-admin')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-credit-card fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Manual Subscription</h5>
                                        <p class="card-text">
                                            Create and manage subscriptions for all companies.
                                            Mark payments, extend subscriptions, and monitor expiry.
                                        </p>
                                        <a href="{{ route('subscriptions.dashboard') }}" class="btn btn-success">
                                            <i class="bx bx-cog me-1"></i> Manage Subscriptions
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endrole

                            <!-- Payment Voucher Approval Process -->
                            @can('manage payment voucher approval')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-check-shield fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">Payment Voucher Approval</h5>
                                        <p class="card-text">
                                            Configure approval workflows for payment vouchers.
                                            Set up approval levels, approvers, and approval thresholds.
                                        </p>
                                        <a href="{{ route('settings.payment-voucher-approval') }}" class="btn btn-info">
                                            <i class="bx bx-check-shield me-1"></i> Configure Approval
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            @role('super-admin')
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-purple">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-envelope fs-1 text-purple"></i>
                                        </div>
                                        <h5 class="card-title">Bulk Email Management</h5>
                                        <p class="card-text">
                                            Send bulk emails to multiple recipients with customizable templates.
                                            Manage email campaigns and track delivery status.
                                        </p>
                                        <a href="{{ route('settings.bulk-email') }}" class="btn btn-purple">
                                            <i class="bx bx-envelope me-1"></i> Send Bulk Emails
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endrole

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
    <p class="mb-0">Copyright © {{ date('Y') }}. All right reserved. -- By SAFCO FINTECH</p>
</footer>

@endsection

@push('styles')
<style>
    .border-purple {
        border-color: #6f42c1 !important;
    }

    .text-purple {
        color: #6f42c1 !important;
    }

    .btn-purple {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: white;
    }

    .btn-purple:hover {
        background-color: #5a32a3;
        border-color: #5a32a3;
        color: white;
    }

    .border-orange {
        border-color: #fd7e14 !important;
    }

    .text-orange {
        color: #fd7e14 !important;
    }

    .btn-orange {
        background-color: #fd7e14;
        border-color: #fd7e14;
        color: white;
    }

    .btn-orange:hover {
        background-color: #e8690b;
        border-color: #e8690b;
        color: white;
    }

    .border-teal {
        border-color: #20c997 !important;
    }

    .text-teal {
        color: #20c997 !important;
    }

    .btn-teal {
        background-color: #20c997;
        border-color: #20c997;
        color: white;
    }

    .btn-teal:hover {
        background-color: #1ba37e;
        border-color: #1ba37e;
        color: white;
    }

    .border-danger {
        border-color: #dc3545 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #bb2d3b;
        border-color: #bb2d3b;
        color: white;
    }
</style>
@endpush