@extends('layouts.main')

@section('title', 'User Profile')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'User Management', 'url' => route('users.index'), 'icon' => 'bx bx-user'],
            ['label' => 'My Profile', 'url' => '#', 'icon' => 'bx bx-user-circle']
        ]" />

            <h6 class="mb-0 text-uppercase">USER PROFILE</h6>
            <hr />
            <div class="row">
                <!-- Profile Card -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="avatar-lg mx-auto mb-4">
                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle font-size-24">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                </div>
                                <h5 class="font-size-16 mb-1 text-truncate">{{ $user->name }}</h5>
                                <p class="text-muted text-truncate mb-3">{{ $user->email ?? 'No email' }}</p>

                                <!-- Status Badge -->
                                @if($user->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($user->status === 'inactive')
                                    <span class="badge bg-warning">Inactive</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                            </div>

                            <hr class="my-4">

                            <div class="text-muted">
                                <div class="table-responsive">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <th scope="row">User ID :</th>
                                                <td>{{ $user->user_id }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Phone :</th>
                                                <td>{{ $user->phone }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Branch :</th>
                                                <td>{{ $user->branch->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Company :</th>
                                                <td>{{ $user->company->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Joined :</th>
                                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Last Updated :</th>
                                                <td>{{ $user->updated_at->format('M d, Y') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Profile Information</h4>

                            {{-- Success message is now handled by toast notification in main layout --}}

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

                            <form action="{{ route('users.profile.update') }}" method="POST" id="profileForm">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Full Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control {{ isset($errors) && $errors->has('name') ? 'is-invalid' : '' }}"
                                                id="name" name="name" value="{{ old('name', $user->name) }}"
                                                placeholder="Enter full name" required>
                                            @if(isset($errors) && $errors->has('name'))
                                                <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="tel"
                                                class="form-control {{ isset($errors) && $errors->has('phone') ? 'is-invalid' : '' }}"
                                                id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                                placeholder="Enter phone number" required>
                                            @if(isset($errors) && $errors->has('phone'))
                                                <div class="invalid-feedback">{{ $errors->first('phone') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email"
                                                class="form-control {{ isset($errors) && $errors->has('email') ? 'is-invalid' : '' }}"
                                                id="email" name="email" value="{{ old('email', $user->email) }}"
                                                placeholder="Enter email address">
                                            @if(isset($errors) && $errors->has('email'))
                                                <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Current Branch</label>
                                            <input type="text" class="form-control"
                                                value="{{ $user->branch->name ?? 'N/A' }}" readonly>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Password Change Section -->
                                <h5 class="mb-3">Change Password</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <div class="input-group">
                                                <input type="password"
                                                    class="form-control {{ isset($errors) && $errors->has('current_password') ? 'is-invalid' : '' }}"
                                                    id="current_password" name="current_password"
                                                    placeholder="Enter current password">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    id="toggleCurrentPassword">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                            </div>
                                            @if(isset($errors) && $errors->has('current_password'))
                                                <div class="invalid-feedback">{{ $errors->first('current_password') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <div class="input-group">
                                                <input type="password"
                                                    class="form-control {{ isset($errors) && $errors->has('new_password') ? 'is-invalid' : '' }}"
                                                    id="new_password" name="new_password" placeholder="Enter new password">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    id="toggleNewPassword">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                            </div>
                                            @if(isset($errors) && $errors->has('new_password'))
                                                <div class="invalid-feedback">{{ $errors->first('new_password') }}</div>
                                            @endif
                                            <small class="form-text text-muted">Leave blank to keep current password. Must
                                                be at least 8 characters if changed.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="new_password_confirmation" class="form-label">Confirm New
                                                Password</label>
                                            <div class="input-group">
                                                <input type="password"
                                                    class="form-control {{ isset($errors) && $errors->has('new_password_confirmation') ? 'is-invalid' : '' }}"
                                                    id="new_password_confirmation" name="new_password_confirmation"
                                                    placeholder="Confirm new password">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    id="toggleConfirmPassword">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                            </div>
                                            @if(isset($errors) && $errors->has('new_password_confirmation'))
                                                <div class="invalid-feedback">{{ $errors->first('new_password_confirmation') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Password Strength Indicator -->
                                <div class="row" id="passwordStrengthSection" style="display: none;">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label class="form-label">Password Strength</label>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar" id="passwordStrength" role="progressbar"
                                                    style="width: 0%"></div>
                                            </div>
                                            <small class="form-text text-muted" id="passwordFeedback"></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Roles and Permissions Card -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Roles & Permissions</h4>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-3">Assigned Roles</h6>
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-primary me-2">{{ $role->name }}</span>
                                                <small class="text-muted">{{ $role->permissions->count() }} permissions</small>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No roles assigned</p>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <h6 class="mb-3">Direct Permissions</h6>
                                    @if($user->permissions->count() > 0)
                                        @foreach($user->permissions as $permission)
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-info me-2">{{ $permission->name }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No direct permissions</p>
                                    @endif
                                </div>
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

@push('scripts')
    <script>
        // Password toggle functionality
        document.getElementById('toggleCurrentPassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('current_password');
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

        document.getElementById('toggleNewPassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('new_password');
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

        document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('new_password_confirmation');
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
        document.getElementById('new_password').addEventListener('input', function () {
            const password = this.value;
            const strengthSection = document.getElementById('passwordStrengthSection');
            const strengthBar = document.getElementById('passwordStrength');
            const feedback = document.getElementById('passwordFeedback');

            if (password.length > 0) {
                strengthSection.style.display = 'block';

                let strength = 0;
                let feedbackText = '';

                // Check length
                if (password.length >= 8) strength += 25;
                if (password.length >= 12) strength += 25;

                // Check for lowercase
                if (/[a-z]/.test(password)) strength += 25;

                // Check for uppercase
                if (/[A-Z]/.test(password)) strength += 25;

                // Check for numbers
                if (/[0-9]/.test(password)) strength += 25;

                // Check for special characters
                if (/[^A-Za-z0-9]/.test(password)) strength += 25;

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
        document.getElementById('profileForm').addEventListener('submit', function (e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('new_password_confirmation').value;
            const currentPassword = document.getElementById('current_password').value;

            if (newPassword && !currentPassword) {
                e.preventDefault();
                alert('Please enter your current password to change it.');
                return false;
            }

            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
                return false;
            }

            if (newPassword && newPassword.length < 8) {
                e.preventDefault();
                alert('New password must be at least 8 characters long!');
                return false;
            }
        });
    </script>
@endpush