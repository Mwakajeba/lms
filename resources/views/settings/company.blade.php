@extends('layouts.main')

@section('title', 'Company Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Company Settings', 'url' => '#', 'icon' => 'bx bx-building']
        ]" />
        <h6 class="mb-0 text-uppercase">COMPANY SETTINGS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Company Information</h4>

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

                        <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control {{ isset($errors) && $errors->has('name') ? 'is-invalid' : '' }}" id="name" name="name" value="{{ old('name', $company->name ?? '') }}" placeholder="Enter company name" required>
                                        @if(isset($errors) && $errors->has('name'))
                                        <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control {{ isset($errors) && $errors->has('email') ? 'is-invalid' : '' }}" id="email" name="email" value="{{ old('email', $company->email ?? '') }}" placeholder="Enter email address" required>
                                        @if(isset($errors) && $errors->has('email'))
                                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control {{ isset($errors) && $errors->has('phone') ? 'is-invalid' : '' }}" id="phone" name="phone" value="{{ old('phone', $company->phone ?? '') }}" placeholder="Enter phone number" required>
                                        @if(isset($errors) && $errors->has('phone'))
                                        <div class="invalid-feedback">{{ $errors->first('phone') }}</div>
                                        @endif
                                    </div>
                                </div>
                                    <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="msp_code" class="form-label d-flex align-items-center gap-1">
                                            MSP Code
                                            <span class="text-muted" data-bs-toggle="tooltip" title="Used on all BOT reports as MSP identifier."><i class="bx bx-info-circle"></i></span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                                            <input type="text"
                                                   class="form-control {{ isset($errors) && $errors->has('msp_code') ? 'is-invalid' : '' }}"
                                                   id="msp_code"
                                                   name="msp_code"
                                                   value="{{ old('msp_code', $company->msp_code ?? '') }}"
                                                   placeholder="e.g., MSP-12345"
                                                   maxlength="50">
                                            @if(isset($errors) && $errors->has('msp_code'))
                                            <div class="invalid-feedback">{{ $errors->first('msp_code') }}</div>
                                            @endif
                                        </div>
                                        <small class="form-text text-muted">Example format: MSP-00001. Leave blank if not applicable.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="license_number" class="form-label">License Number</label>
                                        <input type="text" class="form-control {{ isset($errors) && $errors->has('license_number') ? 'is-invalid' : '' }}" id="license_number" name="license_number" value="{{ old('license_number', $company->license_number ?? '') }}" placeholder="Enter license number">
                                        @if(isset($errors) && $errors->has('license_number'))
                                        <div class="invalid-feedback">{{ $errors->first('license_number') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="registration_date" class="form-label">Registration Date</label>
                                        <input type="date" class="form-control {{ isset($errors) && $errors->has('registration_date') ? 'is-invalid' : '' }}" id="registration_date" name="registration_date" value="{{ old('registration_date', $company->registration_date ?? '') }}">
                                        @if(isset($errors) && $errors->has('registration_date'))
                                        <div class="invalid-feedback">{{ $errors->first('registration_date') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select {{ isset($errors) && $errors->has('status') ? 'is-invalid' : '' }}" id="status" name="status">
                                            <option value="active" {{ (old('status', $company->status ?? '') == 'active') ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ (old('status', $company->status ?? '') == 'inactive') ? 'selected' : '' }}>Inactive</option>
                                            <option value="suspended" {{ (old('status', $company->status ?? '') == 'suspended') ? 'selected' : '' }}>Suspended</option>
                                        </select>
                                        @if(isset($errors) && $errors->has('status'))
                                        <div class="invalid-feedback">{{ $errors->first('status') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control {{ isset($errors) && $errors->has('address') ? 'is-invalid' : '' }}" id="address" name="address" rows="3" placeholder="Enter company address">{{ old('address', $company->address ?? '') }}</textarea>
                                        @if(isset($errors) && $errors->has('address'))
                                        <div class="invalid-feedback">{{ $errors->first('address') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="logo" class="form-label">Company Logo</label>
                                        <input type="file" class="form-control {{ isset($errors) && $errors->has('logo') ? 'is-invalid' : '' }}" id="logo" name="logo" accept="image/*">
                                        @if(isset($errors) && $errors->has('logo'))
                                        <div class="invalid-feedback">{{ $errors->first('logo') }}</div>
                                        @endif
                                        <small class="form-text text-muted">Upload a logo image (JPG, PNG, GIF). Max size: 2MB.</small>
                                    </div>
                                </div>

                                @if(isset($company) && $company->logo)
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Current Logo</label>
                                        <div>
                                            <img src="{{ asset('storage/' . $company->logo) }}" alt="Company Logo" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('settings.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Settings
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Changes
                                </button>
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

@endsection
