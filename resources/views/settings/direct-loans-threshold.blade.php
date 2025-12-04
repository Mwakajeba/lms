@extends('layouts.main')

@section('title', 'Direct Loans Threshold Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Direct Loans Threshold', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />
        <h6 class="mb-0 text-uppercase">DIRECT LOANS THRESHOLD SETTINGS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
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

                        <!-- Add New Threshold Button -->
                        <div class="d-flex justify-content-end mb-3">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addThresholdModal">
                                <i class="bx bx-plus me-1"></i> Add Threshold
                            </button>
                        </div>

                        <!-- Thresholds Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Loan Product</th>
                                        <th>Maximum Amount</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($thresholds as $threshold)
                                    <tr>
                                        <td>{{ $threshold->loanProduct->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($threshold->max_amount, 2) }}</td>
                                        <td>{{ $threshold->description ?? '-' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning me-1" onclick="editThreshold({{ $threshold->id }}, '{{ $threshold->loanProduct->name ?? '' }}', {{ $threshold->max_amount }}, '{{ $threshold->description ?? '' }}', {{ $threshold->loan_product_id }})">
                                                <i class="bx bx-edit"></i> Edit
                                            </button>
                                            <form method="POST" action="{{ route('settings.direct-loans-threshold.destroy', $threshold) }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this threshold?')">
                                                    <i class="bx bx-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No thresholds found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        {{ $thresholds->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Threshold Modal -->
<div class="modal fade" id="addThresholdModal" tabindex="-1" aria-labelledby="addThresholdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addThresholdModalLabel">Add Direct Loan Threshold</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('settings.direct-loans-threshold.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="loan_product_id" class="form-label">Loan Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="loan_product_id" name="loan_product_id" required>
                            <option value="">Select Loan Product</option>
                            @foreach($loanProducts as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="max_amount" class="form-label">Maximum Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="max_amount" name="max_amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Threshold</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Threshold Modal -->
<div class="modal fade" id="editThresholdModal" tabindex="-1" aria-labelledby="editThresholdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editThresholdModalLabel">Edit Direct Loan Threshold</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="" id="editThresholdForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_loan_product_id" class="form-label">Loan Product <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_loan_product_id" name="loan_product_id" required>
                            <option value="">Select Loan Product</option>
                            @foreach($loanProducts as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_max_amount" class="form-label">Maximum Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_max_amount" name="max_amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Threshold</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function editThreshold(id, productName, maxAmount, description, productId) {
    document.getElementById('editThresholdForm').action = '{{ url("settings/direct-loans-threshold") }}/' + id;
    document.getElementById('edit_loan_product_id').value = productId;
    document.getElementById('edit_max_amount').value = maxAmount;
    document.getElementById('edit_description').value = description;
    new bootstrap.Modal(document.getElementById('editThresholdModal')).show();
}
</script>
@endpush