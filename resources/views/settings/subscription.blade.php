@extends('layouts.main')

@section('title', 'Subscription Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Subscription', 'url' => '#', 'icon' => 'bx bx-credit-card']
        ]" />
        <h6 class="mb-0 text-uppercase">SUBSCRIPTION SETTINGS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @can('manage subscription')
                        <h4 class="card-title mb-4">Subscription Management</h4>

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

                        <form action="{{ route('settings.subscription.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Current Subscription Plan -->
                                <div class="col-md-6 mb-3">
                                    <label for="subscription_plan" class="form-label">Current Plan</label>
                                    <select class="form-select" id="subscription_plan" name="subscription_plan" required>
                                        <option value="">Select Plan</option>
                                        <option value="basic" {{ old('subscription_plan', 'basic') == 'basic' ? 'selected' : '' }}>Basic Plan</option>
                                        <option value="premium" {{ old('subscription_plan', 'premium') == 'premium' ? 'selected' : '' }}>Premium Plan</option>
                                        <option value="enterprise" {{ old('subscription_plan', 'enterprise') == 'enterprise' ? 'selected' : '' }}>Enterprise Plan</option>
                                    </select>
                                </div>

                                <!-- Billing Cycle -->
                                <div class="col-md-6 mb-3">
                                    <label for="billing_cycle" class="form-label">Billing Cycle</label>
                                    <select class="form-select" id="billing_cycle" name="billing_cycle" required>
                                        <option value="">Select Billing Cycle</option>
                                        <option value="monthly" {{ old('billing_cycle', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="quarterly" {{ old('billing_cycle', 'quarterly') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                        <option value="yearly" {{ old('billing_cycle', 'yearly') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                    </select>
                                </div>

                                <!-- Start Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="subscription_start_date" class="form-label">Subscription Start Date & Time</label>
                                    <input type="datetime-local" class="form-control" id="subscription_start_date" name="subscription_start_date" 
                                        value="{{ old('subscription_start_date', isset($subscription) && $subscription->start_date ? \Carbon\Carbon::parse($subscription->start_date)->format('Y-m-d\TH:i') : '') }}" 
                                        required>
                                </div>

                                <!-- End Date -->
                                <div class="col-md-6 mb-3">
                                    <label for="subscription_end_date" class="form-label">Subscription End Date & Time</label>
                                    <input type="datetime-local" class="form-control" id="subscription_end_date" name="subscription_end_date" 
                                        value="{{ old('subscription_end_date', isset($subscription) && $subscription->end_date ? \Carbon\Carbon::parse($subscription->end_date)->format('Y-m-d\TH:i') : '') }}" 
                                        required>
                                </div>

                                <!-- Auto Renewal -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="auto_renewal" name="auto_renewal" value="1" {{ old('auto_renewal', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_renewal">
                                            Auto Renewal
                                        </label>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="col-md-6 mb-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">Select Payment Method</option>
                                        <option value="credit_card" {{ old('payment_method', 'credit_card') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                        <option value="bank_transfer" {{ old('payment_method', 'bank_transfer') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                        <option value="mobile_money" {{ old('payment_method', 'mobile_money') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                    </select>
                                </div>

                                <!-- Billing Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="billing_email" class="form-label">Billing Email</label>
                                    <input type="email" class="form-control" id="billing_email" name="billing_email" value="{{ old('billing_email', 'billing@company.com') }}" required>
                                </div>

                                <!-- Billing Address -->
                                <div class="col-md-6 mb-3">
                                    <label for="billing_address" class="form-label">Billing Address</label>
                                    <textarea class="form-control" id="billing_address" name="billing_address" rows="3" required>{{ old('billing_address', '123 Business Street, City, Country') }}</textarea>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i> Update Subscription Settings
                                    </button>
                                    <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Settings
                                    </a>
                                </div>
                            </div>
                        </form>
                        @else
                        <div class="alert alert-warning" role="alert">
                            <i class="bx bx-lock me-2"></i>
                            You don't have permission to manage subscription settings.
                        </div>
                        @endcan
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