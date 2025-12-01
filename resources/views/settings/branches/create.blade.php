@extends('layouts.main')

@section('title', 'Create Branch')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Branch Settings', 'url' => route('settings.branches'), 'icon' => 'bx bx-building'],
            ['label' => 'Create Branch', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE BRANCH</h6>
        <hr/>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Add New Branch</h4>
                        
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

                        <form action="{{ route('settings.branches.store') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Branch Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control {{ isset($errors) && $errors->has('name') ? 'is-invalid' : '' }}" 
                                               id="name" name="name" value="{{ old('name') }}" 
                                               placeholder="Enter branch name" required>
                                        @if(isset($errors) && $errors->has('name'))
                                            <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="branch_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control {{ isset($errors) && $errors->has('branch_name') ? 'is-invalid' : '' }}" 
                                               id="branch_name" name="branch_name" value="{{ old('branch_name') }}" 
                                               placeholder="Enter display name" required>
                                        @if(isset($errors) && $errors->has('branch_name'))
                                            <div class="invalid-feedback">{{ $errors->first('branch_name') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control {{ isset($errors) && $errors->has('phone') ? 'is-invalid' : '' }}" 
                                               id="phone" name="phone" value="{{ old('phone') }}" 
                                               placeholder="Enter phone number" required>
                                        @if(isset($errors) && $errors->has('phone'))
                                            <div class="invalid-feedback">{{ $errors->first('phone') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control {{ isset($errors) && $errors->has('email') ? 'is-invalid' : '' }}" 
                                               id="email" name="email" value="{{ old('email') }}" 
                                               placeholder="Enter email address">
                                        @if(isset($errors) && $errors->has('email'))
                                            <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" class="form-control {{ isset($errors) && $errors->has('location') ? 'is-invalid' : '' }}" 
                                               id="location" name="location" value="{{ old('location') }}" 
                                               placeholder="Enter location">
                                        @if(isset($errors) && $errors->has('location'))
                                            <div class="invalid-feedback">{{ $errors->first('location') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="manager_name" class="form-label">Manager Name</label>
                                        <input type="text" class="form-control {{ isset($errors) && $errors->has('manager_name') ? 'is-invalid' : '' }}" 
                                               id="manager_name" name="manager_name" value="{{ old('manager_name') }}" 
                                               placeholder="Enter manager name">
                                        @if(isset($errors) && $errors->has('manager_name'))
                                            <div class="invalid-feedback">{{ $errors->first('manager_name') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select {{ isset($errors) && $errors->has('status') ? 'is-invalid' : '' }}" 
                                                id="status" name="status" required>
                                            <option value="active" {{ (old('status', 'active') == 'active') ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ (old('status', 'inactive') == 'inactive') ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @if(isset($errors) && $errors->has('status'))
                                            <div class="invalid-feedback">{{ $errors->first('status') }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control {{ isset($errors) && $errors->has('address') ? 'is-invalid' : '' }}" 
                                                  id="address" name="address" rows="3" 
                                                  placeholder="Enter branch address" required>{{ old('address') }}</textarea>
                                        @if(isset($errors) && $errors->has('address'))
                                            <div class="invalid-feedback">{{ $errors->first('address') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('settings.branches') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Branches
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Branch
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