@extends('layouts.main')
@section('title', 'Journal Entry Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Journal Entries', 'url' => route('accounting.journals.index'), 'icon' => 'bx bx-book-open'],
            ['label' => 'Journal Entry #' . $journal->reference, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">JOURNAL ENTRY DETAILS</h6>
        <hr />

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-info">
                    <div class="card-body p-5">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-book-open me-1 font-22 text-info"></i></div>
                                    <h5 class="mb-0 text-info">Journal Entry Details</h5>
                                </div>
                                <p class="mb-0 text-muted">View complete details of this journal entry</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('accounting.journals.export-pdf', $journal) }}" class="btn btn-danger">
                                        <i class="bx bx-file-pdf me-1"></i> Export PDF
                                    </a>
                                    @can('edit journal')
                                    <a href="{{ route('accounting.journals.edit', $journal) }}" class="btn btn-warning">
                                        <i class="bx bx-edit me-1"></i> Edit
                                    </a>
                                    @endcan
                                    @can('view journals')
                                    <a href="{{ route('accounting.journals.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Journal Information -->
            <div class="col-12 col-lg-8">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Journal Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Entry Date</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-light text-dark">
                                        {{ $journal->date ? $journal->date->format('F d, Y') : 'N/A' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Reference</label>
                                <p class="form-control-plaintext">
                                    <strong>{{ $journal->reference ?? 'N/A' }}</strong>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Branch</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-info">
                                        {{ $journal->branch->name ?? 'N/A' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Created By</label>
                                <p class="form-control-plaintext">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="{{ asset('assets/images/avatars/avatar-1.png') }}" alt="User" class="rounded-circle" width="24">
                                        </div>
                                        <span>{{ $journal->user->name ?? 'N/A' }}</span>
                                    </div>
                                </p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <p class="form-control-plaintext">
                                    {{ $journal->description ?: 'No description provided' }}
                                </p>
                            </div>
                            @if($journal->attachment)
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Attachment</label>
                                    <p class="form-control-plaintext">
                                        <a href="{{ asset('storage/' . $journal->attachment) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-download me-1"></i>View Attachment
                                        </a>
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Journal Items -->
                <div class="card radius-10 border-0 shadow-sm mt-4">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Journal Entries</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Account</th>
                                        <th>Nature</th>
                                        <th class="text-end">Amount</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($journal->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $item->chartAccount->account_code ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $item->chartAccount->account_name ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                @if($item->nature === 'debit')
                                                    <span class="badge bg-success">Debit</span>
                                                @else
                                                    <span class="badge bg-danger">Credit</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <strong class="{{ $item->nature === 'debit' ? 'text-success' : 'text-danger' }}">
                                                    TZS {{ number_format($item->amount, 2) }}
                                                </strong>
                                            </td>
                                            <td>
                                                {{ $item->description ?: 'No description' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bx bx-list-ul font-48 text-muted mb-3"></i>
                                                    <h6 class="text-muted">No Journal Items Found</h6>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Sidebar -->
            <div class="col-12 col-lg-4">
                <!-- Totals Summary -->
                <div class="card radius-10 border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="text-muted mb-1">Total Debit</h6>
                                    <h4 class="mb-0 text-success">TZS {{ number_format($journal->debit_total, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <h6 class="text-muted mb-1">Total Credit</h6>
                                    <h4 class="mb-0 text-danger">TZS {{ number_format($journal->credit_total, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted mb-1">Balance</h6>
                                @if($journal->balance == 0)
                                    <h4 class="mb-0 text-success">
                                        <i class="bx bx-check-circle me-1"></i>Balanced
                                    </h4>
                                @else
                                    <h4 class="mb-0 text-warning">
                                        <i class="bx bx-error-circle me-1"></i>TZS {{ number_format(abs($journal->balance), 2) }}
                                    </h4>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card radius-10 border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @can('edit journal')
                            <a href="{{ route('accounting.journals.edit', $journal) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Edit Entry
                            </a>
                            @endcan
                            @can('delete journal')
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                                <i class="bx bx-trash me-1"></i> Delete Entry
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>

                <!-- Entry Details -->
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-detail me-2"></i>Entry Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Created</label>
                                <p class="form-control-plaintext">
                                    {{ $journal->created_at ? $journal->created_at->format('M d, Y \a\t g:i A') : 'N/A' }}
                                </p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Last Updated</label>
                                <p class="form-control-plaintext">
                                    {{ $journal->updated_at ? $journal->updated_at->format('M d, Y \a\t g:i A') : 'N/A' }}
                                </p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Total Items</label>
                                <p class="form-control-plaintext">
                                    <span class="badge bg-primary">{{ $journal->items->count() }} entries</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this journal entry? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('accounting.journals.destroy', $journal) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush
