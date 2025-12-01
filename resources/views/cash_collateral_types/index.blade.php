@extends('layouts.main')

@section('title', 'Cash Deposit Accounts')

@section('content')
<div class="page-wrapper">
    <div class="page-content">

        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Cash Deposit Accounts', 'url' => '#', 'icon' => 'bx bx-credit-card']
        ]" /> 
        <h6 class="mb-0 text-uppercase">CASH DEPOSIT ACCOUNTS</h6>
        <hr/>

        <!-- Stats Card -->
        <div class="row row-cols-1 row-cols-lg-4 mb-4">
            <div class="col">
                <div class="card radius-10 shadow-sm border-0">
                    <div class="card-body d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Types</p>
                            <h4 class="mb-0 fw-bold">{{ $cashCollaterals->count() }}</h4>
                        </div>
                        <div class="widgets-icons bg-gradient-warning text-primary"><i class='bx bx-refresh'></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Cash Deposit Accounts</h4>
                    <a href="{{ route('cash_collateral_types.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus"></i> Add Type
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered dt-responsive nowrap" id="collateralTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Chart Account</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cashCollaterals as $type)
                                <tr>
                                    <td>{{ $type->name }}</td>
                                    <td>{{ $type->chartAccount->account_name ?? 'N/A' }}</td>
                                    <td>{{ $type->description ?? '-' }}</td>
                                    <td>
                                        @if($type->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $type->created_at->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('cash_collateral_types.show', $type) }}"
                                        class="btn btn-sm btn-outline-info"
                                        title="View Details">
                                        View
                                        </a>
                                        <a href="{{ route('cash_collateral_types.edit', $type) }}" 
                                        class="btn btn-sm btn-outline-warning" 
                                        title="Edit">
                                        Edit
                                        </a>

                                        <form action="{{ route('cash_collateral_types.destroy', $type) }}" 
                                            method="POST" 
                                            class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Delete" 
                                                    data-name="{{ $type->name }}">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#collateralTable').DataTable({
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search types..."
            },
            columnDefs: [
                { targets: -1, orderable: false, searchable: false, responsivePriority: 1 },
                { targets: [0, 1], responsivePriority: 2 }
            ]
        });
    });
</script>
@endpush
