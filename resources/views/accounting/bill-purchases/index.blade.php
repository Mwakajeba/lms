@extends('layouts.main')

@section('title', 'Bill Purchases')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-12">
                <div class="page-breadcrumb d-flex align-items-center">
                    <div class="me-auto">
                        <x-breadcrumbs-with-icons :links="[
                            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                            ['label' => 'Bill Purchases', 'url' => '#', 'icon' => 'bx bx-receipt']
                        ]" />
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('accounting.bill-purchases.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> New Bill Purchase
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <h6 class="mb-0 text-uppercase">BILL PURCHASES</h6>
        <hr />

        <!-- Page Header -->
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-5">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-receipt me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Bill Purchases</h5>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card radius-10 bg-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Total Bills</p>
                                                <h4 class="text-white">{{ $stats['total'] }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-receipt"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Paid</p>
                                                <h4 class="text-white">{{ $stats['paid'] }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-check-circle"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-warning">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Pending</p>
                                                <h4 class="text-white">{{ $stats['pending'] }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-time"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-danger">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Overdue</p>
                                                <h4 class="text-white">{{ $stats['overdue'] }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-error-circle"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-5">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Quick Actions</h5>
                        </div>
                        <hr>
                        <div class="d-grid">
                            <a href="{{ route('accounting.bill-purchases.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Create New Bill
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bills Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Reference</th>
                                <th>Supplier</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th>Total Amount</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bills as $index => $bill)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('accounting.bill-purchases.show', $bill) }}" class="text-primary fw-bold">
                                            {{ $bill->reference }}
                                        </a>
                                    </td>
                                    <td>{{ $bill->supplier->name ?? 'N/A' }}</td>
                                    <td>{{ $bill->formatted_date }}</td>
                                    <td>{{ $bill->formatted_due_date ?? 'N/A' }}</td>
                                    <td class="text-end">TZS {{ $bill->formatted_total_amount }}</td>
                                    <td class="text-end">TZS {{ $bill->formatted_paid }}</td>
                                    <td class="text-end">TZS {{ $bill->formatted_balance }}</td>
                                    <td>{!! $bill->status_badge !!}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('accounting.bill-purchases.show', $bill) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="View">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="{{ route('accounting.bill-purchases.edit', $bill) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            @if(!$bill->isPaid())
                                                <a href="{{ route('accounting.bill-purchases.payment', $bill) }}" 
                                                   class="btn btn-sm btn-outline-success" 
                                                   title="Add Payment">
                                                    <i class="bx bx-money"></i>
                                                </a>
                                            @endif
                                            @if($bill->payments()->count() == 0)
                                            <form action="{{ route('accounting.bill-purchases.destroy', $bill) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to delete this bill?')"
                                                  style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                            @else
                                            <button type="button" class="btn btn-sm btn-outline-danger" disabled title="Cannot delete bill with existing payments">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bx bx-receipt fs-1"></i>
                                            <p class="mt-2">No bill purchases found</p>
                                            <a href="{{ route('accounting.bill-purchases.create') }}" class="btn btn-primary">
                                                Create Your First Bill
                                            </a>
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
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable().destroy();
        }
        
        // Initialize DataTable
        $('#example').DataTable({
            responsive: true,
            order: [[3, 'desc']], // Sort by date descending
            pageLength: 25,
            language: {
                search: "Search bills:",
                lengthMenu: "Show _MENU_ bills per page",
                info: "Showing _START_ to _END_ of _TOTAL_ bills",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    });
</script>
@endpush 