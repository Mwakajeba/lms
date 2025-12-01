@extends('layouts.main')

@section('title', 'Penalty Settings')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
                ['label' => 'Penalty Settings', 'url' => '#', 'icon' => 'bx bx-time']
            ]" />
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">PENALTY SETTINGS</h6>
                    <p class="text-muted mb-0">Configure late payment penalties and fee structures</p>
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
                            <h4 class="card-title mb-4">Penalty Configuration</h4>

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

                            <form action="{{ route('settings.penalty.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <!-- Late Payment Penalty -->
                                    <div class="col-md-6 mb-3">
                                        <label for="late_payment_penalty" class="form-label">Late Payment Penalty
                                            (%)</label>
                                        <input type="number"
                                            class="form-control @error('late_payment_penalty') is-invalid @enderror"
                                            id="late_payment_penalty" name="late_payment_penalty"
                                            value="{{ old('late_payment_penalty', 5) }}" min="0" max="100" step="0.01"
                                            placeholder="Enter penalty percentage">
                                        <div class="form-text">Percentage of penalty applied to late payments</div>
                                        @error('late_payment_penalty')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Grace Period -->
                                    <div class="col-md-6 mb-3">
                                        <label for="penalty_grace_period" class="form-label">Grace Period (Days)</label>
                                        <input type="number"
                                            class="form-control @error('penalty_grace_period') is-invalid @enderror"
                                            id="penalty_grace_period" name="penalty_grace_period"
                                            value="{{ old('penalty_grace_period', 7) }}" min="0" max="365"
                                            placeholder="Enter grace period in days">
                                        <div class="form-text">Number of days before penalty is applied</div>
                                        @error('penalty_grace_period')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Calculation Method -->
                                    <div class="col-md-6 mb-3">
                                        <label for="penalty_calculation_method" class="form-label">Calculation
                                            Method</label>
                                        <select
                                            class="form-select @error('penalty_calculation_method') is-invalid @enderror"
                                            id="penalty_calculation_method" name="penalty_calculation_method">
                                            <option value="">-- Select Method --</option>
                                            <option value="percentage" {{ old('penalty_calculation_method', 'percentage') == 'percentage' ? 'selected' : '' }}>
                                                Percentage of Outstanding Amount
                                            </option>
                                            <option value="fixed" {{ old('penalty_calculation_method', 'percentage') == 'fixed' ? 'selected' : '' }}>
                                                Fixed Amount
                                            </option>
                                        </select>
                                        <div class="form-text">How the penalty should be calculated</div>
                                        @error('penalty_calculation_method')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Currency -->
                                    <div class="col-md-6 mb-3">
                                        <label for="penalty_currency" class="form-label">Penalty Currency</label>
                                        <select class="form-select @error('penalty_currency') is-invalid @enderror"
                                            id="penalty_currency" name="penalty_currency">
                                            <option value="">-- Select Currency --</option>
                                            <option value="TZS" {{ old('penalty_currency', 'TZS') == 'TZS' ? 'selected' : '' }}>Tanzanian Shilling (TZS)</option>
                                            <option value="USD" {{ old('penalty_currency', 'TZS') == 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                                            <option value="EUR" {{ old('penalty_currency', 'TZS') == 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                                            <option value="GBP" {{ old('penalty_currency', 'TZS') == 'GBP' ? 'selected' : '' }}>British Pound (GBP)</option>
                                        </select>
                                        <div class="form-text">Currency for penalty calculations</div>
                                        @error('penalty_currency')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                        Back to Settings
                                    </a>
                                    <button type="submit" class="btn btn-orange">
                                        Update Penalty Settings
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
    </style>
@endpush