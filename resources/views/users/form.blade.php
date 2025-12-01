@extends('layouts.main')

@section('title', isset($user) ? 'Edit User' : 'Create New User')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'User Management', 'url' => route('users.index'), 'icon' => 'bx bx-user'],
            ['label' => isset($user) ? 'Edit User' : 'Create User', 'url' => '#', 'icon' => isset($user) ? 'bx bx-edit' : 'bx bx-plus-circle']
        ]" />

        <h6 class="mb-0 text-uppercase">{{ isset($user) ? 'EDIT USER' : 'CREATE NEW USER' }}</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">{{ isset($user) ? 'Edit User Information' : 'User Information' }}</h5>
                </div>
                        
                        <form action="{{ isset($user) ? route('users.update', $user) : route('users.store') }}" 
                              method="POST" id="userForm">
                            @csrf
                            @if(isset($user))
                                @method('PUT')
                            @endif

                            <!-- General Error Display -->
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-2"></i>
                                    <strong>Please fix the following errors:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            
                            <div class="row">
                                <!-- Personal Information -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" 
                                               value="{{ old('name', $user->name ?? '') }}" 
                                               placeholder="Enter full name" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" 
                                               value="{{ old('phone', $user->phone ?? '') }}" 
                                               placeholder="e.g., 0715123456 or +255715123456" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Enter phone number in any format (0715123456, +255715123456, 255715123456). Will be automatically formatted to 255715123456.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" 
                                               value="{{ old('email', $user->email ?? '') }}" 
                                               placeholder="Enter email address">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" 
                                                id="status" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="active" {{ old('status', $user->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status', $user->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="suspended" {{ old('status', $user->status ?? '') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Branch Assignment -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
                                        <select class="form-select @error('branch_id') is-invalid @enderror" 
                                                id="branch_id" name="branch_id" required>
                                            <option value="">Select Branch</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" 
                                                        {{ old('branch_id', $user->branch_id ?? '') == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }} - {{ $branch->location ?? 'No location' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            {{ isset($user) ? 'New Password' : 'Password' }} 
                                            <span class="text-danger">{{ isset($user) ? '' : '*' }}</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                   id="password" name="password" 
                                                   placeholder="{{ isset($user) ? 'Leave blank to keep current password' : 'Enter password' }}"
                                                   {{ isset($user) ? '' : 'required' }}>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="bx bx-show"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            @php
                                                $securityConfig = \App\Services\SystemSettingService::getSecurityConfig();
                                                $minLength = $securityConfig['password_min_length'] ?? 8;
                                                $requirements = ["Minimum {$minLength} characters"];
                                                
                                                if ($securityConfig['password_require_uppercase'] ?? true) {
                                                    $requirements[] = 'At least one uppercase letter';
                                                }
                                                if ($securityConfig['password_require_numbers'] ?? true) {
                                                    $requirements[] = 'At least one number';
                                                }
                                                if ($securityConfig['password_require_special'] ?? true) {
                                                    $requirements[] = 'At least one special character';
                                                }
                                            @endphp
                                            {{ isset($user) ? 'Leave blank to keep current password. ' : '' }}
                                            Password requirements: {{ implode(', ', $requirements) }}
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">
                                            Confirm {{ isset($user) ? 'New ' : '' }}Password 
                                            <span class="text-danger">{{ isset($user) ? '' : '*' }}</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" 
                                                   id="password_confirmation" name="password_confirmation" 
                                                   placeholder="Confirm password">
                                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                                <i class="bx bx-show"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Role Assignment -->
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                            <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ old('role_id', isset($user) ? ($user->roles->first()->id ?? '') : '') == $role->id ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                            @endforeach
                                        </select>
                                        @error('role_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Current User Info (only for edit) -->
                            @if(isset($user))
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Current User Information</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>User ID:</strong> {{ $user->user_id }}</p>
                                                <p class="mb-1"><strong>Created:</strong> {{ $user->created_at->format('M d, Y H:i') }}</p>
                                                <p class="mb-1"><strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y H:i') }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Current Branch:</strong> {{ $user->branch->name ?? 'N/A' }}</p>
                                                <p class="mb-1"><strong>Current Role:</strong> 
                                                    @if($user->roles->first())
                                                        <span class="badge bg-primary me-1">{{ $user->roles->first()->name }}</span>
                                                    @else
                                                        <span class="text-muted">No role assigned</span>
                                                    @endif
                                                </p>
                                                <p class="mb-1"><strong>Status:</strong> 
                                                    @if($user->status === 'active')
                                                        <span class="badge bg-success">Active</span>
                                                    @elseif($user->status === 'inactive')
                                                        <span class="badge bg-warning">Inactive</span>
                                                    @else
                                                        <span class="badge bg-danger">Suspended</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Password Strength Indicator -->
                            <div class="row" id="passwordStrengthSection" style="display: none;">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Password Strength</label>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="form-text text-muted" id="passwordFeedback"></small>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        @can('view users')
                                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Cancel
                                        </a>
                                        @endcan
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> {{ isset($user) ? 'Update User' : 'Create User' }}
                                        </button>
                                    </div>
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

@endsection

@push('scripts')
<script>
// Password toggle functionality
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    }
});

document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password_confirmation');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bx-show');
        icon.classList.add('bx-hide');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bx-hide');
        icon.classList.add('bx-show');
    }
});

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthSection = document.getElementById('passwordStrengthSection');
    const strengthBar = document.getElementById('passwordStrength');
    const feedback = document.getElementById('passwordFeedback');
    
    if (password.length > 0) {
        strengthSection.style.display = 'block';
        
        let strength = 0;
        let feedbackText = '';
        
        // Get system settings for password requirements
        const securityConfig = @json(\App\Services\SystemSettingService::getSecurityConfig());
        const minLength = securityConfig.password_min_length || 8;
        const requireUppercase = securityConfig.password_require_uppercase || true;
        const requireNumbers = securityConfig.password_require_numbers || true;
        const requireSpecial = securityConfig.password_require_special || true;
        
        // Check length
        if (password.length >= minLength) strength += 25;
        if (password.length >= minLength + 4) strength += 25;
        
        // Check for lowercase
        if (/[a-z]/.test(password)) strength += 25;
        
        // Check for uppercase (if required)
        if (requireUppercase && /[A-Z]/.test(password)) strength += 25;
        else if (!requireUppercase) strength += 25; // Give points even if not required
        
        // Check for numbers (if required)
        if (requireNumbers && /[0-9]/.test(password)) strength += 25;
        else if (!requireNumbers) strength += 25; // Give points even if not required
        
        // Check for special characters (if required)
        if (requireSpecial && /[^A-Za-z0-9]/.test(password)) strength += 25;
        else if (!requireSpecial) strength += 25; // Give points even if not required
        
        // Cap at 100%
        strength = Math.min(strength, 100);
        
        // Update progress bar
        strengthBar.style.width = strength + '%';
        
        // Update color and feedback
        if (strength < 25) {
            strengthBar.className = 'progress-bar bg-danger';
            feedbackText = 'Very Weak';
        } else if (strength < 50) {
            strengthBar.className = 'progress-bar bg-warning';
            feedbackText = 'Weak';
        } else if (strength < 75) {
            strengthBar.className = 'progress-bar bg-info';
            feedbackText = 'Good';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            feedbackText = 'Strong';
        }
        
        feedback.textContent = feedbackText;
    } else {
        strengthSection.style.display = 'none';
    }
});

// Form validation
document.getElementById('userForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;
    const role = document.getElementById('role_id').value;
    const isEdit = {{ isset($user) ? 'true' : 'false' }};
    
    // Password validation
    if (!isEdit && password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (isEdit && password && password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    // Role validation
    if (!role) {
        e.preventDefault();
        alert('Please select a role!');
        return false;
    }
    
    // Password length validation
    if ((!isEdit && password.length < 8) || (isEdit && password && password.length < 8)) {
        e.preventDefault();
        alert('Password must be at least 8 characters long!');
        return false;
    }
});
</script>
@endpush