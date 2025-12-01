<div class="modal-header">
    <h5 class="modal-title">Edit Role: {{ ucfirst($role->name) }}</h5>
</div>
<form id="editRoleForm" method="POST" action="{{ route('roles.update', $role) }}">
    @csrf
    <input type="hidden" name="_method" value="PUT">
    <div class="modal-body">
        <div class="mb-3">
            <p class="text-muted">Update the role details and permissions below.</p>
        </div>
                <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="roleName" class="form-label">Role Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="roleName" name="name" 
                           value="{{ $role->name }}" required>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="roleDescription" class="form-label">Description</label>
            <textarea class="form-control" id="roleDescription" name="description" rows="3">{{ $role->description }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Permissions</label>
            <div class="row">
                @foreach($permissionGroups as $group => $permissions)
                <div class="col-md-4 mb-3">
                    <div class="card border">
                        <div class="card-header bg-light py-2">
                            <div class="form-check">
                                <input class="form-check-input select-all-permissions" 
                                       type="checkbox" 
                                       data-group="{{ $group }}"
                                       id="selectAll{{ ucfirst($group) }}">
                                <label class="form-check-label fw-bold mb-0" for="selectAll{{ ucfirst($group) }}">
                                    {{ ucfirst($group) }}
                                </label>
                            </div>
                        </div>
                        <div class="card-body py-2" style="max-height: 200px; overflow-y: auto;">
                            @foreach($permissions as $permission)
                            <div class="form-check">
                                <input class="form-check-input permission-checkbox" 
                                       type="checkbox" 
                                       name="permissions[]" 
                                       value="{{ $permission->id }}" 
                                       id="perm_{{ $permission->id }}"
                                       data-group="{{ $group }}"
                                       {{ $role->hasPermissionTo($permission) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                    {{ ucwords(str_replace(['-', '_'], ' ', $permission->name)) }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Role</button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Select all permissions for a group
    $('.select-all-permissions').on('change', function() {
        const group = $(this).data('group');
        const isChecked = $(this).is(':checked');
        
        $(`.permission-checkbox[data-group="${group}"]`).prop('checked', isChecked);
    });

    // Update select all checkbox when individual permissions change
    $('.permission-checkbox').on('change', function() {
        const group = $(this).data('group');
        const totalCheckboxes = $(`.permission-checkbox[data-group="${group}"]`).length;
        const checkedCheckboxes = $(`.permission-checkbox[data-group="${group}"]:checked`).length;
        
        const selectAllCheckbox = $(`.select-all-permissions[data-group="${group}"]`);
        
        if (checkedCheckboxes === 0) {
            selectAllCheckbox.prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            selectAllCheckbox.prop('indeterminate', false).prop('checked', true);
        } else {
            selectAllCheckbox.prop('indeterminate', true);
        }
    });

    // Initialize select all checkboxes
    $('.select-all-permissions').each(function() {
        const group = $(this).data('group');
        const totalCheckboxes = $(`.permission-checkbox[data-group="${group}"]`).length;
        const checkedCheckboxes = $(`.permission-checkbox[data-group="${group}"]:checked`).length;
        
        if (checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0) {
            $(this).prop('checked', true);
        } else if (checkedCheckboxes > 0) {
            $(this).prop('indeterminate', true);
        }
    });
});
</script> 