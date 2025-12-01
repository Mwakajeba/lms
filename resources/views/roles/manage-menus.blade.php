@extends('layouts.main')

@section('title', 'Manage Role Menus - ' . $role->name)

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Roles & Permissions', 'url' => route('roles.index'), 'icon' => 'bx bx-shield'],
            ['label' => 'Manage Menus - ' . ucfirst($role->name), 'url' => '#', 'icon' => 'bx bx-menu']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Manage Menus for Role: <span class="text-primary">{{ ucfirst($role->name) }}</span>
                    </h4>
                    <p class="text-muted mb-0">{{ $role->description ?? 'No description available' }}</p>
                </div>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Roles
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Current Role Menus -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-list-check me-2"></i>
                                Current Role Menus
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($role->menus->count() > 0)
                                <div class="menu-list">
                                    @foreach($role->menus->whereNull('parent_id') as $parentMenu)
                                        <div class="menu-item mb-3">
                                            <div
                                                class="d-flex align-items-center justify-content-between p-3 border rounded bg-light">
                                                <div class="d-flex align-items-center">
                                                    <i class="{{ $parentMenu->icon }} me-2 text-primary"></i>
                                                    <span class="fw-semibold">{{ $parentMenu->name }}</span>
                                                </div>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="removeMenu({{ $parentMenu->id }})">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>

                                            @if($parentMenu->children->count() > 0)
                                                <div class="ms-4 mt-2">
                                                    @foreach($parentMenu->children as $childMenu)
                                                        <div
                                                            class="d-flex align-items-center justify-content-between p-2 border-start border-2 border-primary ms-3 mb-2">
                                                            <div class="d-flex align-items-center">
                                                                <i class="bx bx-right-arrow-alt me-2 text-muted"></i>
                                                                <span>{{ $childMenu->name }}</span>
                                                                @if($childMenu->route)
                                                                    <small class="text-muted ms-2">({{ $childMenu->route }})</small>
                                                                @endif
                                                            </div>
                                                            <button class="btn btn-sm btn-outline-danger"
                                                                onclick="removeMenu({{ $childMenu->id }})">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bx bx-menu bx-lg text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No menus assigned to this role yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Assign New Menus -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-plus-circle me-2"></i>
                                Assign New Menus
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="assignMenusForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Select Menus to Assign</label>
                                    <div class="menu-selection" style="max-height: 400px; overflow-y: auto;">
                                        @foreach($allMenus as $parentMenu)
                                            <div class="menu-group mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input parent-menu" type="checkbox"
                                                        id="menu_{{ $parentMenu->id }}" value="{{ $parentMenu->id }}"
                                                        data-has-children="{{ $parentMenu->children->count() > 0 ? 'true' : 'false' }}">
                                                    <label class="form-check-label fw-semibold"
                                                        for="menu_{{ $parentMenu->id }}">
                                                        <i class="{{ $parentMenu->icon }} me-2"></i>
                                                        {{ $parentMenu->name }}
                                                    </label>
                                                </div>

                                                @if($parentMenu->children->count() > 0)
                                                    <div class="ms-4 mt-2">
                                                        @foreach($parentMenu->children as $childMenu)
                                                            <div class="form-check">
                                                                <input class="form-check-input child-menu" type="checkbox"
                                                                    id="menu_{{ $childMenu->id }}" value="{{ $childMenu->id }}"
                                                                    data-parent="{{ $parentMenu->id }}">
                                                                <label class="form-check-label" for="menu_{{ $childMenu->id }}">
                                                                    <i class="bx bx-right-arrow-alt me-2"></i>
                                                                    {{ $childMenu->name }}
                                                                    @if($childMenu->route)
                                                                        <small class="text-muted">({{ $childMenu->route }})</small>
                                                                    @endif
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bx bx-plus me-1"></i>
                                        Assign Selected Menus
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
        .menu-list {
            max-height: 500px;
            overflow-y: auto;
        }

        .menu-item {
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            transform: translateX(5px);
        }

        .menu-selection {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
        }

        .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }

        .parent-menu:checked+label {
            color: #198754;
            font-weight: 600;
        }

        .child-menu:checked+label {
            color: #198754;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function () {
            // Handle parent menu selection
            $('.parent-menu').on('change', function () {
                const parentId = $(this).val();
                const isChecked = $(this).is(':checked');
                const hasChildren = $(this).data('has-children');

                if (hasChildren) {
                    $(`.child-menu[data-parent="${parentId}"]`).prop('checked', isChecked);
                }
            });

            // Handle child menu selection
            $('.child-menu').on('change', function () {
                const parentId = $(this).data('parent');
                const parentCheckbox = $(`#menu_${parentId}`);
                const siblings = $(`.child-menu[data-parent="${parentId}"]`);
                const checkedSiblings = siblings.filter(':checked');

                if (checkedSiblings.length === 0) {
                    parentCheckbox.prop('checked', false);
                } else if (checkedSiblings.length === siblings.length) {
                    parentCheckbox.prop('checked', true);
                }
            });

            // Handle form submission
            $('#assignMenusForm').on('submit', function (e) {
                e.preventDefault();

                const selectedMenus = [];
                $('input[type="checkbox"]:checked').each(function () {
                    selectedMenus.push($(this).val());
                });

                if (selectedMenus.length === 0) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Please select at least one menu to assign.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();

                submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Assigning...');

                $.ajax({
                    url: '{{ route("roles.menus.assign", $role) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        menu_ids: selectedMenus
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 3000
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 4000
                            });
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = 'Failed to assign menus';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: errorMessage,
                            showConfirmButton: false,
                            timer: 4000
                        });
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });

        function removeMenu(menuId) {
            Swal.fire({
                title: 'Confirm Removal',
                text: 'Are you sure you want to remove this menu from the role?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("roles.menus.remove", $role) }}',
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            menu_id: menuId
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 3000
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: response.message,
                                    showConfirmButton: false,
                                    timer: 4000
                                });
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Failed to remove menu';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: errorMessage,
                                showConfirmButton: false,
                                timer: 4000
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush