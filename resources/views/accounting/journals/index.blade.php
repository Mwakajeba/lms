@extends('layouts.main')
@section('title', 'Journal Entries')

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
                            ['label' => 'Journal Entries', 'url' => '#', 'icon' => 'bx bx-book-open']
                        ]" />
                    </div>
                    <div class="ms-auto">
                        @can('create journal')
                        <a href="{{ route('accounting.journals.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus"></i> New Journal Entry
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        <h6 class="mb-0 text-uppercase">JOURNAL ENTRIES</h6>
        <hr />

        <!-- Statistics Cards -->
        <div class="row row-cols-1 row-cols-lg-4 g-3 mb-4">
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-muted">Total Entries</h6>
                                <h4 class="mb-0 text-primary">{{ $journals->count() }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-primary text-white">
                                <i class='bx bx-book'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-muted">Total Debit</h6>
                                <h4 class="mb-0 text-success">TZS {{ number_format($journals->sum('debit_total'), 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white">
                                <i class='bx bx-plus'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-muted">Total Credit</h6>
                                <h4 class="mb-0 text-danger">TZS {{ number_format($journals->sum('credit_total'), 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-danger text-white">
                                <i class='bx bx-minus'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-muted">Balance</h6>
                                <h4 class="mb-0 {{ $journals->sum('balance') == 0 ? 'text-success' : 'text-warning' }}">
                                    TZS {{ number_format($journals->sum('balance'), 2) }}
                                </h4>
                            </div>
                            <div class="widgets-icons bg-gradient-{{ $journals->sum('balance') == 0 ? 'success' : 'warning' }} text-white">
                                <i class='bx bx-check-circle'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Entries Table -->
        <div class="card radius-10 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Journal Entries</h6>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="journalsTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Reference</th>
                                <th>Date</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th class="text-center">Balance</th>
                                <th>Created By</th>
                                <th class="border-0 text-center">
                                    <i class="bx bx-cog me-1"></i>Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journals as $i => $journal)
                                <tr class="journal-row">
                                    <td class="fw-bold text-muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="journal-icon me-3">
                                                <i class="bx bx-book-open text-primary"></i>
                                            </div>
                                            <div>
                                                <a href="{{ route('accounting.journals.show', $journal) }}" 
                                                   class="text-decoration-none fw-bold text-dark">
                                                    {{ $journal->reference ?? 'N/A' }}
                                                </a>
                                                @if($journal->description)
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($journal->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $journal->date ? $journal->date->format('M d, Y') : 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-success fw-bold">
                                            TZS {{ number_format($journal->debit_total, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-danger fw-bold">
                                            TZS {{ number_format($journal->credit_total, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($journal->balance == 0)
                                            <span class="badge bg-success">Balanced</span>
                                        @else
                                            <span class="badge bg-warning">
                                                TZS {{ number_format(abs($journal->balance), 2) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <i class="bx bx-user-circle text-primary"></i>
                                            </div>
                                            {{ $journal->user->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            @can('view journal details')
                                            <a href="{{ route('accounting.journals.show', $journal) }}" 
                                               class="btn btn-sm btn-primary" 
                                               title="View Details">
                                            <i class="bx bx-show"></i>
                                        </a>
                                            @endcan
                                            @can('edit journal')
                                            <a href="{{ route('accounting.journals.edit', $journal) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                            @endcan
                                            @can('delete journal')
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Delete"
                                                    onclick="confirmDelete('{{ route('accounting.journals.destroy', $journal) }}')">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bx bx-book-open font-48 text-muted mb-3"></i>
                                            <h6 class="text-muted">No Journal Entries Found</h6>
                                            <p class="text-muted mb-3">Start by creating your first journal entry</p>
                                            @can('create journal')
                                            <a href="{{ route('accounting.journals.create') }}" class="btn btn-primary">
                                                <i class="bx bx-plus me-1"></i> Create First Entry
                                            </a>
                                            @endcan
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
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Ensure DataTable controls are visible */
    .dataTables_wrapper .dataTables_filter {
        display: block !important;
        margin-bottom: 10px;
    }
    
    .dataTables_wrapper .dataTables_length {
        display: block !important;
        margin-bottom: 10px;
    }
    
    .dataTables_wrapper .dataTables_info {
        display: block !important;
        margin-top: 10px;
    }
    
    .dataTables_wrapper .dataTables_paginate {
        display: block !important;
        margin-top: 10px;
    }
    
    /* Improve search box styling */
    .dataTables_filter input {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 6px 12px;
        margin-left: 8px;
    }
    
    /* Improve length filter styling */
    .dataTables_length select {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 4px 8px;
        margin: 0 4px;
    }
    
    /* Ensure action column is visible and properly styled */
    #journalsTable th:last-child,
    #journalsTable td:last-child {
        min-width: 120px;
        text-align: center;
    }
    
    /* Style action buttons */
    #journalsTable .btn-group {
        display: flex;
        gap: 2px;
        justify-content: center;
    }
    
    #journalsTable .btn-group .btn {
        padding: 4px 8px;
        font-size: 12px;
    }
    
    /* Ensure table is responsive */
    .table-responsive {
        overflow-x: auto;
    }
    
    /* Fix DataTable responsive issues */
    .dataTables_wrapper .dataTables_scroll {
        overflow-x: auto;
    }
</style>
@endpush

@push('scripts')
<script>
    // Fix perfect-scrollbar passive event listener warning
    if (typeof PerfectScrollbar !== 'undefined') {
        const originalBind = PerfectScrollbar.prototype.bind;
        PerfectScrollbar.prototype.bind = function() {
            try {
                return originalBind.apply(this, arguments);
            } catch (e) {
                console.warn('PerfectScrollbar binding error:', e);
            }
        };
    }

    // Fix Highcharts error #13
    if (typeof Highcharts !== 'undefined') {
        Highcharts.error = function(code, stop) {
            if (code === 13) {
                console.warn('Highcharts error #13: Container not found, skipping chart rendering');
                return;
            }
            console.error('Highcharts error #' + code);
        };
    }

    $(document).ready(function() {
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#journalsTable')) {
            $('#journalsTable').DataTable().destroy();
        }
        
        $('#journalsTable').DataTable({
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function(row) {
                            var data = row.data();
                            return 'Details for ' + data[1]; // Reference column
                        }
                    }),
                    renderer: $.fn.dataTable.Responsive.renderer.tableAll()
                }
            },
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthChange: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[1, 'desc']],
            language: {
                search: "",
                searchPlaceholder: "Search journal entries...",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                emptyTable: "No journal entries found",
                zeroRecords: "No matching journal entries found",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            columnDefs: [
                { targets: 0, orderable: false, searchable: false, width: '50px' }, // Row number
                { targets: -1, orderable: false, searchable: false, width: '120px' } // Actions column
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            initComplete: function() {
                // Add custom styling
                $('.dataTables_wrapper').addClass('mt-3');
                
                // Ensure search box and length filter are visible
                $('.dataTables_filter').show();
                $('.dataTables_length').show();
                
                // Ensure action column is properly displayed
                $('#journalsTable th:last-child').show();
                $('#journalsTable td:last-child').show();
                
                // Fix any responsive issues
                $(window).trigger('resize');
            }
        });
    });

    function confirmDelete(url) {
        document.getElementById('deleteForm').action = url;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endpush
