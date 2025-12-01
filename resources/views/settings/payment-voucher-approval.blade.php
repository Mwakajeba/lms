@extends('layouts.main')

@section('title', 'Payment Voucher Approval Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Payment Voucher Approval', 'url' => '#', 'icon' => 'bx bx-check-shield']
        ]" />
        <h6 class="mb-0 text-uppercase">PAYMENT VOUCHER APPROVAL SETTINGS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @can('manage payment voucher approval')
                        <h4 class="card-title mb-4">Payment Voucher Approval - Simple Configuration</h4>

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

                        <form action="{{ route('settings.payment-voucher-approval.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <!-- Approval Levels -->


                                <!-- Require Approval for All -->
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="require_approval_for_all" name="require_approval_for_all" value="1" {{ old('require_approval_for_all', $settings->require_approval_for_all ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="require_approval_for_all">
                                            Require Approval for All Vouchers
                                        </label>
                                        <small class="form-text text-muted d-block">If checked, all vouchers require approval regardless of amount</small>
                                    </div>
                                </div>
                            </div>
                                
                            <!-- Direct posting note when approvals are disabled -->
                            <div class="row" id="direct_post_note" style="display:none;">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        All payment vouchers will be saved directly to GL transactions. No approvals will be required.
                                    </div>
                                </div>
                            </div>

                            <!-- Approval Configuration (visible only if require_approval_for_all is checked) -->
                            <div id="approval_config">
                            <div class="row">
                                <div class="col-md-6 mb-3" id="levels_block">
                                    <label for="approval_levels" class="form-label">Number of Approval Levels</label>
                                    <select class="form-select" id="approval_levels" name="approval_levels">
                                        <option value="">Select Levels</option>
                                        <option value="1" {{ old('approval_levels', $settings->approval_levels ?? 2) == '1' ? 'selected' : '' }}>1 Level</option>
                                        <option value="2" {{ old('approval_levels', $settings->approval_levels ?? 2) == '2' ? 'selected' : '' }}>2 Levels</option>
                                        <option value="3" {{ old('approval_levels', $settings->approval_levels ?? 2) == '3' ? 'selected' : '' }}>3 Levels</option>
                                        <option value="4" {{ old('approval_levels', $settings->approval_levels ?? 2) == '4' ? 'selected' : '' }}>4 Levels</option>
                                        <option value="5" {{ old('approval_levels', $settings->approval_levels ?? 2) == '5' ? 'selected' : '' }}>5 Levels</option>
                                    </select>
                                </div>
                            </div>
                            

                            <!-- Approval Assignments -->
                            <div class="row mt-4" id="assignments_block">
                                <div class="col-12">
                                    <h6 class="mb-3">Approval Assignments</h6>
                                    <p class="text-muted">Assign roles or specific users to each approval level</p>
                                    
                                    <!-- Level 1 Approvers -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Level 1 Approvers</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="level1_approval_type" class="form-label">Approval Type</label>
                                                    <select class="form-select" id="level1_approval_type" name="level1_approval_type">
                                                        <option value="role" {{ old('level1_approval_type', $settings->level1_approval_type ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                        <option value="user" {{ old('level1_approval_type', $settings->level1_approval_type ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="level1_approvers" class="form-label">Approvers</label>
                                                    <select class="form-select" id="level1_approvers" name="level1_approvers[]" multiple>
                                                        @if(isset($roles))
                                                            @foreach($roles as $role)
                                                                @php
                                                                    $isSelected = false;
                                                                    if (old('level1_approvers')) {
                                                                        $isSelected = in_array('role_' . $role->name, old('level1_approvers'));
                                                                    } elseif (is_array($settings->level1_approvers ?? null) && $settings->level1_approval_type === 'role') {
                                                                        $isSelected = in_array($role->name, $settings->level1_approvers);
                                                                    }
                                                                @endphp
                                                                <option value="role_{{ $role->name }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }} (Role)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                        @if(isset($users))
                                                            @foreach($users as $user)
                                                                @php
                                                                    $isSelected = false;
                                                                    if (old('level1_approvers')) {
                                                                        $isSelected = in_array('user_' . $user->id, old('level1_approvers'));
                                                                    } elseif (is_array($settings->level1_approvers ?? null) && $settings->level1_approval_type === 'user') {
                                                                        $isSelected = in_array($user->id, $settings->level1_approvers);
                                                                    }
                                                                @endphp
                                                                <option value="user_{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Level 2 Approvers -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Level 2 Approvers</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="level2_approval_type" class="form-label">Approval Type</label>
                                                    <select class="form-select" id="level2_approval_type" name="level2_approval_type">
                                                        <option value="role" {{ old('level2_approval_type', $settings->level2_approval_type ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                        <option value="user" {{ old('level2_approval_type', $settings->level2_approval_type ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="level2_approvers" class="form-label">Approvers</label>
                                                    <select class="form-select" id="level2_approvers" name="level2_approvers[]" multiple>
                                                        @if(isset($roles))
                                                            @foreach($roles as $role)
                                                                @php
                                                                    $isSelected = false;
                                                                    if (old('level2_approvers')) {
                                                                        $isSelected = in_array('role_' . $role->name, old('level2_approvers'));
                                                                    } elseif (is_array($settings->level2_approvers ?? null) && $settings->level2_approval_type === 'role') {
                                                                        $isSelected = in_array($role->name, $settings->level2_approvers);
                                                                    }
                                                                @endphp
                                                                <option value="role_{{ $role->name }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }} (Role)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                        @if(isset($users))
                                                            @foreach($users as $user)
                                                                @php
                                                                    $isSelected = false;
                                                                    if (old('level2_approvers')) {
                                                                        $isSelected = in_array('user_' . $user->id, old('level2_approvers'));
                                                                    } elseif (is_array($settings->level2_approvers ?? null) && $settings->level2_approval_type === 'user') {
                                                                        $isSelected = in_array($user->id, $settings->level2_approvers);
                                                                    }
                                                                @endphp
                                                                <option value="user_{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Level 3 Approvers -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Level 3 Approvers</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="level3_approval_type" class="form-label">Approval Type</label>
                                                    <select class="form-select" id="level3_approval_type" name="level3_approval_type">
                                                        <option value="role" {{ old('level3_approval_type', $settings->level3_approval_type ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                        <option value="user" {{ old('level3_approval_type', $settings->level3_approval_type ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="level3_approvers" class="form-label">Approvers</label>
                                                    <select class="form-select" id="level3_approvers" name="level3_approvers[]" multiple>
                                                        @if(isset($roles))
                                                            @foreach($roles as $role)
                                                                @php
                                                                    $isSelected = false;
                                                                    if (old('level3_approvers')) {
                                                                        $isSelected = in_array('role_' . $role->name, old('level3_approvers'));
                                                                    } elseif (is_array($settings->level3_approvers ?? null) && $settings->level3_approval_type === 'role') {
                                                                        $isSelected = in_array($role->name, $settings->level3_approvers);
                                                                    }
                                                                @endphp
                                                                <option value="role_{{ $role->name }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }} (Role)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                        @if(isset($users))
                                                            @foreach($users as $user)
                                                                @php
                                                                    $isSelected = false;
                                                                    if (old('level3_approvers')) {
                                                                        $isSelected = in_array('user_' . $user->id, old('level3_approvers'));
                                                                    } elseif (is_array($settings->level3_approvers ?? null) && $settings->level3_approval_type === 'user') {
                                                                        $isSelected = in_array($user->id, $settings->level3_approvers);
                                                                    }
                                                                @endphp
                                                                <option value="user_{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Level 4 Approvers -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Level 4 Approvers</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="level4_approval_type" class="form-label">Approval Type</label>
                                                    <select class="form-select" id="level4_approval_type" name="level4_approval_type">
                                                        <option value="role" {{ old('level4_approval_type', $settings->level4_approval_type ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                        <option value="user" {{ old('level4_approval_type', $settings->level4_approval_type ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="level4_approvers" class="form-label">Approvers</label>
                                                    <select class="form-select" id="level4_approvers" name="level4_approvers[]" multiple>
                                                        @if(isset($roles))
                                                            @foreach($roles as $role)
                                                                @php
                                                                    $isSelected = false;
                                                                    if (old('level4_approvers')) {
                                                                        $isSelected = in_array('role_' . $role->name, old('level4_approvers'));
                                                                    } elseif (is_array($settings->level4_approvers ?? null) && $settings->level4_approval_type === 'role') {
                                                                        $isSelected = in_array($role->name, $settings->level4_approvers);
                                                                    }
                                                                @endphp
                                                                <option value="role_{{ $role->name }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }} (Role)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                        @if(isset($users))
                                                            @foreach($users as $user)
                                                                @php
                                                                    $isSelected = false;
                                                                    if (old('level4_approvers')) {
                                                                        $isSelected = in_array('user_' . $user->id, old('level4_approvers'));
                                                                    } elseif (is_array($settings->level4_approvers ?? null) && $settings->level4_approval_type === 'user') {
                                                                        $isSelected = in_array($user->id, $settings->level4_approvers);
                                                                    }
                                                                @endphp
                                                                <option value="user_{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Level 5 Approvers -->
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0">Level 5 Approvers</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="level5_approval_type" class="form-label">Approval Type</label>
                                                    <select class="form-select" id="level5_approval_type" name="level5_approval_type">
                                                        <option value="role" {{ old('level5_approval_type', $settings->level5_approval_type ?? 'role') == 'role' ? 'selected' : '' }}>By Role</option>
                                                        <option value="user" {{ old('level5_approval_type', $settings->level5_approval_type ?? 'role') == 'user' ? 'selected' : '' }}>By User</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="level5_approvers" class="form-label">Approvers</label>
                                                    <select class="form-select" id="level5_approvers" name="level5_approvers[]" multiple>
                                                        @if(isset($roles))
                                                            @foreach($roles as $role)
                                                                @php
                                                                    $isSelected = false;
                                                                    if (old('level5_approvers')) {
                                                                        $isSelected = in_array('role_' . $role->name, old('level5_approvers'));
                                                                    } elseif (is_array($settings->level5_approvers ?? null) && $settings->level5_approval_type === 'role') {
                                                                        $isSelected = in_array($role->name, $settings->level5_approvers);
                                                                    }
                                                                @endphp
                                                                <option value="role_{{ $role->name }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ ucfirst($role->name) }} (Role)
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                        @if(isset($users))
                                                            @foreach($users as $user)
                                                                @php
                                                                    $isSelected = false;
                                                                    if (old('level5_approvers')) {
                                                                        $isSelected = in_array('user_' . $user->id, old('level5_approvers'));
                                                                    } elseif (is_array($settings->level5_approvers ?? null) && $settings->level5_approval_type === 'user') {
                                                                        $isSelected = in_array($user->id, $settings->level5_approvers);
                                                                    }
                                                                @endphp
                                                                <option value="user_{{ $user->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                                    {{ $user->name }} ({{ $user->email }})
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div> <!-- /#approval_config -->

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i> Update Approval Settings
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
                            You don't have permission to manage payment voucher approval settings.
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const approvalLevelsSelect = document.getElementById('approval_levels');
    const requireAllCheckbox = document.getElementById('require_approval_for_all');
    const approvalConfig = document.getElementById('approval_config');
    const directPostNote = document.getElementById('direct_post_note');
    const levelsBlock = document.getElementById('levels_block');
    const assignmentsBlock = document.getElementById('assignments_block');
    const approvalCards = document.querySelectorAll('[id^="level"]');
    
    function toggleApprovalConfig() {
        const enabled = requireAllCheckbox.checked;
        approvalConfig.style.display = enabled ? 'block' : 'none';
        directPostNote.style.display = enabled ? 'none' : 'block';
        // Enable/disable inputs inside config
        approvalConfig.querySelectorAll('input, select, textarea').forEach(el => {
            el.disabled = !enabled;
        });
        // Explicitly hide/show blocks
        if (levelsBlock) levelsBlock.style.display = enabled ? 'block' : 'none';
        if (assignmentsBlock) assignmentsBlock.style.display = enabled ? 'block' : 'none';
    }

    function toggleApprovalLevels() {
        const selectedLevels = parseInt(approvalLevelsSelect.value) || 1;
        
        // Show/hide approval level cards based on selection
        for (let i = 1; i <= 5; i++) {
            const levelElement = document.querySelector(`#level${i}_approvers`);
            if (levelElement) {
                const levelCard = levelElement.closest('.card');
                if (levelCard) {
                    if (i <= selectedLevels) {
                        levelCard.style.display = 'block';
                    } else {
                        levelCard.style.display = 'none';
                    }
                }
            }
        }
    }
    
    // Initial call with delay to ensure DOM is fully loaded
    setTimeout(() => {
        toggleApprovalConfig();
        toggleApprovalLevels();
    }, 100);
    
    // Listen for changes
    requireAllCheckbox.addEventListener('change', toggleApprovalConfig);
    approvalLevelsSelect.addEventListener('change', toggleApprovalLevels);
    
    // Dynamic approver loading based on approval type
    @php
        $rolesNames = isset($roles) ? $roles->pluck('name')->values() : collect();
        $usersArr = isset($users) ? $users->map(function($u){ return ['id'=>$u->id,'name'=>$u->name,'email'=>$u->email]; })->values() : collect();
    @endphp
    const rolesData = @json($rolesNames);
    const usersData = @json($usersArr);
    const approvalTypeSelects = document.querySelectorAll('[id$="_approval_type"]');
    const approverSelects = document.querySelectorAll('[id$="_approvers"]');
    
    function updateApproverOptions(approvalTypeSelect, approverSelect) {
        const selectedType = approvalTypeSelect.value;
        const currentValue = approverSelect.value;
        
        // Clear current options
        approverSelect.innerHTML = '';
        
        if (selectedType === 'role') {
            rolesData.forEach(function(name){
                let opt = document.createElement('option');
                opt.value = 'role_' + name;
                opt.textContent = (name.charAt(0).toUpperCase() + name.slice(1)) + ' (Role)';
                approverSelect.appendChild(opt);
            });
        } else if (selectedType === 'user') {
            usersData.forEach(function(u){
                let opt = document.createElement('option');
                opt.value = 'user_' + u.id;
                opt.textContent = u.name + ' (' + (u.email || '') + ')';
                approverSelect.appendChild(opt);
            });
        }
    }
    
    // Initialize and add event listeners
    approvalTypeSelects.forEach((typeSelect) => {
        // Find the corresponding approver select by matching the level number
        const levelMatch = typeSelect.id.match(/level(\d+)_approval_type/);
        if (levelMatch) {
            const level = levelMatch[1];
            const approverSelect = document.getElementById(`level${level}_approvers`);
            if (approverSelect) {
                updateApproverOptions(typeSelect, approverSelect);
                
                typeSelect.addEventListener('change', function() {
                    updateApproverOptions(this, approverSelect);
                });
            }
        }
    });
});
</script>
@endpush 