@extends('layouts.main')

@section('title', 'Fees Settings')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
                ['label' => 'Fees Settings', 'url' => '#', 'icon' => 'bx bx-money']
            ]" />
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">FEES SETTINGS</h6>
                    <p class="text-muted mb-0">Manage service fees, charges, and payment structures</p>
                </div>
                <div>
                    <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                        Back to Settings
                    </a>
                </div>
            </div>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Fees Configuration</h4>

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

                            <form action="{{ route('settings.fees.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- Service Fee Configuration -->
                                    <div class="col-12">
                                        <h5 class="mb-3 text-primary">Service Fee Configuration</h5>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="service_fee_percentage" class="form-label">Service Fee Percentage
                                            (%)</label>
                                        <input type="number"
                                            class="form-control @error('service_fee_percentage') is-invalid @enderror"
                                            id="service_fee_percentage" name="service_fee_percentage"
                                            value="{{ old('service_fee_percentage', 2.5) }}" min="0" max="100" step="0.01"
                                            placeholder="Enter service fee percentage">
                                        <div class="form-text">Default service fee percentage applied to transactions</div>
                                        @error('service_fee_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="transaction_fee" class="form-label">Transaction Fee (TZS)</label>
                                        <input type="number"
                                            class="form-control @error('transaction_fee') is-invalid @enderror"
                                            id="transaction_fee" name="transaction_fee"
                                            value="{{ old('transaction_fee', 1000) }}" min="0" step="0.01"
                                            placeholder="Enter transaction fee">
                                        <div class="form-text">Fixed transaction fee per operation</div>
                                        @error('transaction_fee')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Fee Limits -->
                                    <div class="col-12">
                                        <h5 class="mb-3 text-primary">Fee Limits</h5>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="minimum_fee" class="form-label">Minimum Fee (TZS)</label>
                                        <input type="number" class="form-control @error('minimum_fee') is-invalid @enderror"
                                            id="minimum_fee" name="minimum_fee" value="{{ old('minimum_fee', 500) }}"
                                            min="0" step="0.01" placeholder="Enter minimum fee">
                                        <div class="form-text">Minimum fee amount that can be charged</div>
                                        @error('minimum_fee')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="maximum_fee" class="form-label">Maximum Fee (TZS)</label>
                                        <input type="number" class="form-control @error('maximum_fee') is-invalid @enderror"
                                            id="maximum_fee" name="maximum_fee" value="{{ old('maximum_fee', 50000) }}"
                                            min="0" step="0.01" placeholder="Enter maximum fee">
                                        <div class="form-text">Maximum fee amount that can be charged</div>
                                        @error('maximum_fee')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Currency Configuration -->
                                    <div class="col-12">
                                        <h5 class="mb-3 text-primary">Currency Configuration</h5>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="fee_currency" class="form-label">Fee Currency</label>
                                        <select class="form-select @error('fee_currency') is-invalid @enderror"
                                            id="fee_currency" name="fee_currency">
                                            <option value="">-- Select Currency --</option>
                                            <option value="TZS" {{ old('fee_currency', 'TZS') == 'TZS' ? 'selected' : '' }}>
                                                Tanzanian Shilling (TZS)</option>
                                            <option value="USD" {{ old('fee_currency', 'TZS') == 'USD' ? 'selected' : '' }}>US
                                                Dollar (USD)</option>
                                            <option value="EUR" {{ old('fee_currency', 'TZS') == 'EUR' ? 'selected' : '' }}>
                                                Euro (EUR)</option>
                                            <option value="GBP" {{ old('fee_currency', 'TZS') == 'GBP' ? 'selected' : '' }}>
                                                British Pound (GBP)</option>
                                        </select>
                                        <div class="form-text">Default currency for fee calculations</div>
                                        @error('fee_currency')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                        Back to Settings
                                    </a>
                                    <button type="submit" class="btn btn-teal">
                                        Update Fees Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
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
    </style>
@endpush