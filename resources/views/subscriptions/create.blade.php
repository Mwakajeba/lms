@extends('layouts.main')

@section('title', 'Create Subscription')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Subscriptions', 'url' => route('subscriptions.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
            <h6 class="mb-0 text-uppercase">CREATE NEW SUBSCRIPTION</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Create New Subscription</h4>

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

                            <form action="{{ route('subscriptions.store') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <!-- Company Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label for="company_id" class="form-label">Company</label>
                                        <select class="form-select" id="company_id" name="company_id" required>
                                            <option value="">Select Company</option>
                                            @foreach($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Plan Name -->
                                    <div class="col-md-6 mb-3">
                                        <label for="plan_name" class="form-label">Plan Name</label>
                                        <input type="text" class="form-control" id="plan_name" name="plan_name"
                                            value="{{ old('plan_name') }}" placeholder="e.g., Premium Plan" required>
                                    </div>

                                    <!-- Plan Description -->
                                    <div class="col-12 mb-3">
                                        <label for="plan_description" class="form-label">Plan Description</label>
                                        <textarea class="form-control" id="plan_description" name="plan_description"
                                            rows="3"
                                            placeholder="Describe the plan features and benefits">{{ old('plan_description') }}</textarea>
                                    </div>

                                    <!-- Amount -->
                                    <div class="col-md-4 mb-3">
                                        <label for="amount" class="form-label">Amount</label>
                                        <input type="number" class="form-control" id="amount" name="amount"
                                            value="{{ old('amount') }}" step="0.01" min="0" placeholder="0.00" required>
                                    </div>

                                    <!-- Currency -->
                                    <div class="col-md-4 mb-3">
                                        <label for="currency" class="form-label">Currency</label>
                                        <select class="form-select" id="currency" name="currency" required>
                                            <option value="TZS" {{ old('currency', 'TZS') == 'TZS' ? 'selected' : '' }}>TZS
                                            </option>
                                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                            <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                        </select>
                                    </div>

                                    <!-- Billing Cycle -->
                                    <div class="col-md-4 mb-3">
                                        <label for="billing_cycle" class="form-label">Billing Cycle</label>
                                        <select class="form-select" id="billing_cycle" name="billing_cycle" required>
                                            <option value="">Select Billing Cycle</option>
                                            <option value="monthly" {{ old('billing_cycle') == 'monthly' ? 'selected' : '' }}>
                                                Monthly</option>
                                            <option value="quarterly" {{ old('billing_cycle') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                            <option value="half-yearly" {{ old('billing_cycle') == 'half-yearly' ? 'selected' : '' }}>Half-Yearly</option>
                                            <option value="yearly" {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}>
                                                Yearly</option>
                                        </select>
                                    </div>

                                    <!-- Start Date -->
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            value="{{ old('start_date', date('Y-m-d')) }}" required>
                                    </div>

                                    <!-- End Date -->
                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                            value="{{ old('end_date') }}" required>
                                    </div>

                                    <!-- Auto Renewal -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="auto_renew"
                                                name="auto_renew" value="1" {{ old('auto_renew') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="auto_renew">
                                                Auto Renewal
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Create Subscription
                                        </button>
                                        <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Back to Subscriptions
                                        </a>
                                    </div>
                                </div>
                            </form>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const billingCycleSelect = document.getElementById('billing_cycle');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            // Auto-calculate end date based on billing cycle
            function calculateEndDate() {
                const startDate = new Date(startDateInput.value);
                const billingCycle = billingCycleSelect.value;

                if (startDate && billingCycle) {
                    let endDate = new Date(startDate);

                    switch (billingCycle) {
                        case 'monthly':
                            endDate.setMonth(endDate.getMonth() + 1);
                            break;
                        case 'quarterly':
                            endDate.setMonth(endDate.getMonth() + 3);
                            break;
                        case 'half-yearly':
                            endDate.setMonth(endDate.getMonth() + 6);
                            break;
                        case 'yearly':
                            endDate.setFullYear(endDate.getFullYear() + 1);
                            break;
                    }

                    endDateInput.value = endDate.toISOString().split('T')[0];
                }
            }

            billingCycleSelect.addEventListener('change', calculateEndDate);
            startDateInput.addEventListener('change', calculateEndDate);
        });
    </script>
@endsection