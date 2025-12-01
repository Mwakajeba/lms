@extends('layouts.main')

@section('title', 'Backup Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Backup Settings', 'url' => '#', 'icon' => 'bx bx-data']
        ]" />
        <h6 class="mb-0 text-uppercase">BACKUP & RESTORE</h6>
        <hr/>

        <div class="row">
            <!-- Statistics Cards -->
            <div class="col-12">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bx bx-data fs-1"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                                        <p class="mb-0">Total Backups</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bx bx-check-circle fs-1"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0">{{ $stats['completed'] ?? 0 }}</h4>
                                        <p class="mb-0">Completed</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bx bx-x-circle fs-1"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0">{{ $stats['failed'] ?? 0 }}</h4>
                                        <p class="mb-0">Failed</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bx bx-hdd fs-1"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0">{{ number_format(($stats['total_size'] ?? 0) / 1024 / 1024, 2) }} MB</h4>
                                        <p class="mb-0">Total Size</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Backup Section -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Create New Backup</h5>
                        
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

                        <form action="{{ route('settings.backup.create') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Backup Type <span class="text-danger">*</span></label>
                                        <select class="form-select" id="type" name="type" required>
                                            <option value="">Select backup type</option>
                                            <option value="database">Database Only</option>
                                            <option value="files">Files Only</option>
                                            <option value="full">Full Backup (Database + Files)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <input type="text" class="form-control" id="description" name="description" 
                                               placeholder="Optional description for this backup">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bx bx-plus me-1"></i> Create Backup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Backup List -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Backup History</h5>
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#cleanModal">
                                <i class="bx bx-trash me-1"></i> Clean Old Backups
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($backups ?? [] as $backup)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $backup->name }}</div>
                                            @if($backup->description)
                                                <small class="text-muted">{{ $backup->description }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($backup->type === 'database')
                                                <span class="badge bg-primary">Database</span>
                                            @elseif($backup->type === 'files')
                                                <span class="badge bg-success">Files</span>
                                            @else
                                                <span class="badge bg-info">Full</span>
                                            @endif
                                        </td>
                                        <td>{{ $backup->formatted_size }}</td>
                                        <td>
                                            @if($backup->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($backup->status === 'failed')
                                                <span class="badge bg-danger">Failed</span>
                                            @else
                                                <span class="badge bg-warning">In Progress</span>
                                            @endif
                                        </td>
                                        <td>{{ $backup->creator->name ?? 'Unknown' }}</td>
                                        <td>{{ $backup->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            @if($backup->status === 'completed')
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('settings.backup.download', $backup->hash_id) }}" 
                                                       class="btn btn-sm btn-info" title="Download">
                                                        <i class="bx bx-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="confirmRestore({{ $backup->id }}, '{{ $backup->name }}')" 
                                                            title="Restore">
                                                        <i class="bx bx-reset"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="confirmDelete('{{ $backup->hash_id }}', '{{ $backup->name }}')" 
                                                            title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-muted">No actions available</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No backups found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(isset($backups) && $backups->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $backups->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Restore Form (Hidden) -->
        <form id="restoreForm" action="{{ route('settings.backup.restore') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" id="restoreBackupId" name="backup_id">
        </form>
    </div>
</div>

<!-- Clean Old Backups Modal -->
<div class="modal fade" id="cleanModal" tabindex="-1" aria-labelledby="cleanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cleanModalLabel">Clean Old Backups</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('settings.backup.clean') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="days" class="form-label">Delete backups older than (days)</label>
                        <input type="number" class="form-control" id="days" name="days" 
                               value="30" min="1" max="365" required>
                        <div class="form-text">This will permanently delete backups older than the specified number of days.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Clean Old Backups</button>
                </div>
            </form>
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
    // Make functions globally available
    window.confirmRestore = function(backupId, backupName) {
        Swal.fire({
            title: 'Confirm Restore',
            text: `Are you sure you want to restore from "${backupName}"? This will overwrite current data.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, restore it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('restoreBackupId').value = backupId;
                document.getElementById('restoreForm').submit();
            }
        });
    };

    window.confirmDelete = function(backupId, backupName) {
        Swal.fire({
            title: 'Confirm Delete',
            text: `Are you sure you want to delete "${backupName}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('settings/backup') }}/${backupId}`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                
                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    };
});
</script>
@endpush 