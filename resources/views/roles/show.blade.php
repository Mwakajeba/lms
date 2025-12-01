<div class="modal-header bg-info text-white">
    <h5 class="modal-title"><i class="bx bx-show me-2"></i> Role Details</h5>
</div>
<div class="modal-body">
    <div class="row">
        <!-- Role Info -->
        <div class="col-md-4">
            <div class="text-center mb-3">
                <div class="d-flex justify-content-center mb-2">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bx bx-shield fs-1 text-white"></i>
                    </div>
                </div>
                <h4 class="mb-1">{{ ucfirst($role->name) }}</h4>
                <p class="text-muted mb-2">{{ $role->guard_name }}</p>
                <span class="badge bg-secondary">{{ $role->users->count() }} users</span>
            </div>
            
            <div class="mb-3">
                <h6 class="fw-semibold mb-2">Description</h6>
                <p class="text-muted small">{{ $role->description ?? 'No description available' }}</p>
            </div>
            
            <div class="row text-center">
                <div class="col-6">
                    <h5 class="mb-1 text-primary">{{ $role->permissions->count() }}</h5>
                    <small class="text-muted">Permissions</small>
                </div>
                <div class="col-6">
                    <h5 class="mb-1 text-success">{{ $role->users->count() }}</h5>
                    <small class="text-muted">Users</small>
                </div>
            </div>
        </div>
        
        <!-- Permissions -->
        <div class="col-md-8">
            <h6 class="fw-semibold mb-3">Permissions</h6>
            @if($role->permissions->count() > 0)
                <div class="row">
                    @foreach($permissionGroups as $group => $permissions)
                    @if(count($permissions) > 0)
                    <div class="col-md-6 mb-3">
                        <div class="card border">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0">{{ ucfirst($group) }}</h6>
                            </div>
                            <div class="card-body py-2" style="max-height: 150px; overflow-y: auto;">
                                @foreach($permissions as $permission)
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    <span class="small">{{ ucwords(str_replace(['-', '_'], ' ', $permission->name)) }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning mb-0">No permissions assigned to this role.</div>
            @endif
        </div>
    </div>
    
    <!-- Assigned Users -->
    <div class="mt-3">
        <h6 class="fw-semibold mb-2">Assigned Users ({{ $role->users->count() }})</h6>
        @if($role->users->count() > 0)
            <div class="row">
                @foreach($role->users->take(6) as $user)
                <div class="col-md-6 mb-2">
                    <div class="d-flex align-items-center p-2 border rounded">
                        <div class="me-2">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="bx bx-user text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 small">{{ $user->name }}</h6>
                            <small class="text-muted">{{ $user->email ?? 'N/A' }}</small>
                        </div>
                        <span class="badge bg-success small">Active</span>
                    </div>
                </div>
                @endforeach
                @if($role->users->count() > 6)
                <div class="col-12">
                    <p class="text-muted small text-center">+{{ $role->users->count() - 6 }} more users</p>
                </div>
                @endif
            </div>
        @else
            <div class="alert alert-info mb-0">No users assigned to this role.</div>
        @endif
    </div>
</div>
