@extends('layouts.main')

@section('title', 'Double Entries - ' . $account_name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Double Entries - ' . $account_name, 'url' => '#', 'icon' => 'bx bx-list-ul']
        ]" />

        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-book-open me-1 font-22 text-primary"></i></div>
                                    <h5 class="mb-0 text-primary">Double Entries</h5>
                                </div>
                                <p class="mb-0 text-muted">All transactions for {{ $account_name }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button onclick="exportDoubleEntries()" class="btn btn-outline-success" id="exportBtn">
                                        <i class="bx bx-download me-1"></i> Export CSV
                                    </button>
                                    <button onclick="printDoubleEntries()" class="btn btn-outline-info" id="printBtn">
                                        <i class="bx bx-printer me-1"></i> Print
                                    </button>
                                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Account Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Account Name</label>
                                    <p class="form-control-plaintext">{{ $account_name }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Account Code</label>
                                    <p class="form-control-plaintext">{{ $chartAccount->account_code ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Account Class</label>
                                    <p class="form-control-plaintext">{{ $chartAccount->accountClassGroup->accountClass->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="form-label fw-bold">Account Group</label>
                                    <p class="form-control-plaintext">{{ $chartAccount->accountClassGroup->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row row-cols-1 row-cols-lg-3">
            <div class="col">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0">Total Debit</p>
                                <h4 class="font-weight-bold text-success">{{ number_format($totalDebit, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-success text-white">
                                <i class='bx bx-trending-up'></i>
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
                                <p class="mb-0">Total Credit</p>
                                <h4 class="font-weight-bold text-danger">{{ number_format($totalCredit, 2) }}</h4>
                            </div>
                            <div class="widgets-icons bg-gradient-danger text-white">
                                <i class='bx bx-trending-down'></i>
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
                                <p class="mb-0">Balance</p>
                                <h4 class="font-weight-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($balance, 2) }}
                                </h4>
                            </div>
                            <div class="widgets-icons {{ $balance >= 0 ? 'bg-gradient-success' : 'bg-gradient-danger' }} text-white">
                                <i class='bx bx-balance'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Transactions Table -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Transaction History</h6>
                    </div>
                    <div class="card-body">
                        <!-- Help Information -->
                        <div class="alert alert-info mb-3">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-info-circle me-2"></i>
                                <div>
                                    <strong>Tip:</strong> Click on any <strong class="text-success">Debit</strong> or <strong class="text-danger">Credit</strong> amount to view the complete double entry details for that transaction.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Loading Overlay -->
                        <div id="tableLoadingOverlay" class="position-relative">
                        <div class="table-responsive">
                                <table id="doubleEntriesTable" class="table table-bordered table-striped nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference</th>
                                        <th>Description</th>
                                        <th>Type</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                        <th class="text-end">Running Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transactionData)
                                    @php $transaction = $transactionData['transaction']; @endphp
                                    <tr>
                                        <td>{{ $transaction->date ? $transaction->date->format('d-m-Y') : 'N/A' }}</td>
                                        <td>
                                            @if($transaction->journal)
                                                <a href="{{ route('accounting.journals.show', $transaction->journal) }}" class="text-decoration-none">
                                                    {{ $transaction->journal->reference }}
                                                </a>
                                            @elseif($transaction->paymentVoucher)
                                                <a href="{{ route('accounting.payment-vouchers.show', $transaction->paymentVoucher) }}" class="text-decoration-none">
                                                    {{ $transaction->paymentVoucher->reference }}
                                                </a>
                                            @elseif($transaction->bill)
                                                <a href="{{ route('accounting.bill-purchases.show', $transaction->bill) }}" class="text-decoration-none">
                                                    {{ $transaction->bill->reference }}
                                                </a>
                                            @elseif($transaction->receipt)
                                                <a href="{{ route('accounting.receipt-vouchers.show', $transaction->receipt) }}" class="text-decoration-none">
                                                    {{ $transaction->receipt->reference }}
                                                </a>
                                            @else
                                                {{ $transaction->transaction_id }}
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($transaction->description, 50) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $transaction->transaction_type == 'journal' ? 'primary' : ($transaction->transaction_type == 'payment' ? 'success' : ($transaction->transaction_type == 'receipt' ? 'info' : 'warning')) }}">
                                                {{ ucfirst($transaction->transaction_type) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($transactionData['debit_amount'] > 0)
                                                <a href="{{ route('accounting.transactions.details', Hashids::encode($transaction->id), $transaction->transaction_type) }}" 
                                                   class="text-decoration-none fw-bold text-success">
                                                    {{ number_format($transactionData['debit_amount'], 2) }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($transactionData['credit_amount'] > 0)
                                                <a href="{{ route('accounting.transactions.details', Hashids::encode($transaction->id), $transaction->transaction_type) }}" 
                                                   class="text-decoration-none fw-bold text-danger">
                                                    {{ number_format($transactionData['credit_amount'], 2) }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold {{ $transactionData['running_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($transactionData['running_balance'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle me-2"></i>No transactions found for this account
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
        </div>

        <!-- Balance Summary -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 border-0 shadow-sm">
                    <div class="card-header bg-transparent border-0">
                        <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Account Balance Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="alert alert-info mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-trending-up me-2"></i>
                                        <div>
                                            <strong>Total Debits:</strong> {{ number_format($totalDebit, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-trending-down me-2"></i>
                                        <div>
                                            <strong>Total Credits:</strong> {{ number_format($totalCredit, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.info-item {
    margin-bottom: 1rem;
}

.info-item label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-item p {
    font-size: 1rem;
    color: #212529;
    margin-bottom: 0;
}

/* Custom DataTables styling */
.dataTables_processing {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    font-size: 16px;
    z-index: 9999;
}

.dataTables_length label,
.dataTables_filter label {
    font-weight: 500;
    margin-bottom: 0;
}

.dataTables_filter input {
    border-radius: 6px;
    border: 1px solid #ddd;
    padding: 8px 12px;
    margin-left: 8px;
}

.table-responsive .table {
    margin-bottom: 0;
}

/* Custom styling for transaction amounts */
.text-success {
    color: #28a745 !important;
}

.text-danger {
    color: #dc3545 !important;
}

/* Badge styling */
.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.75rem;
}

/* Loading overlay styles */
#tableLoadingOverlay {
    position: relative;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    border-radius: 8px;
}

.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.spinner-border-custom {
    width: 3rem;
    height: 3rem;
    border: 0.3em solid #007bff;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}

/* Button loading states */
.btn-loading {
    position: relative;
    pointer-events: none;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

/* Search highlight */
.highlight {
    background-color: #fff3cd;
    padding: 2px 4px;
    border-radius: 3px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let table;
    
    // Initialize DataTable for Double Entries
    function initializeTable() {
        table = $('#doubleEntriesTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[0, 'desc']], // Sort by date descending (newest first)
        columnDefs: [
            {
                targets: [4, 5, 6], // Debit, Credit, Running Balance columns
                className: 'text-end'
            },
            {
                targets: [3], // Type column
                orderable: true,
                searchable: true
                },
                {
                    targets: [0, 1, 2], // Priority columns for responsive
                    responsivePriority: 1
            }
        ],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
                search: "",
                searchPlaceholder: "Search transactions...",
            lengthMenu: "Show _MENU_ transactions per page",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
                infoEmpty: "Showing 0 to 0 of 0 transactions",
                infoFiltered: "(filtered from _MAX_ total transactions)",
                emptyTable: "No transactions found for this account",
                zeroRecords: "No matching transactions found",
                processing: '<div class="loading-spinner"><div class="spinner-border-custom"></div><div>Loading transactions...</div></div>',
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        initComplete: function() {
            // Add custom filters
            this.api().columns().every(function() {
                var column = this;
                var title = column.header().textContent;
                
                    
                });
            },
            drawCallback: function(settings) {
                // Reinitialize tooltips after each draw
                $('[data-bs-toggle="tooltip"]').tooltip();
                
                // Remove loading overlay
                $('#tableLoadingOverlay .loading-overlay').remove();
            }
        });
    }
    
    // Initialize table
    initializeTable();
    
    // Show loading overlay
    function showLoadingOverlay() {
        if ($('#tableLoadingOverlay .loading-overlay').length === 0) {
            $('#tableLoadingOverlay').append(`
                <div class="loading-overlay">
                    <div class="loading-spinner">
                        <div class="spinner-border-custom"></div>
                        <div>Loading transactions...</div>
                    </div>
                </div>
            `);
        }
    }
    
    // Hide loading overlay
    function hideLoadingOverlay() {
        $('#tableLoadingOverlay .loading-overlay').remove();
    }
    
    // Apply advanced filters
    function applyFilters() {
        showLoadingOverlay();
        
        // Get filter values
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        const transactionType = $('#transactionTypeFilter').val();
        const amountRange = $('#amountRangeFilter').val();
        const descriptionSearch = $('#descriptionSearch').val();
        const referenceSearch = $('#referenceSearch').val();
        
        // Build search query
        let searchQuery = '';
        
        if (descriptionSearch) {
            searchQuery += descriptionSearch + ' ';
        }
        if (referenceSearch) {
            searchQuery += referenceSearch + ' ';
        }
        
        // Apply global search
        table.search(searchQuery.trim()).draw();
        
        // Apply column-specific filters
        if (transactionType) {
            table.column(3).search('^' + transactionType + '$', true, false);
        }
        
        // Apply date filters (custom logic needed)
        if (dateFrom || dateTo) {
            table.column(0).search(function(data, type, val, meta) {
                if (type === 'display' || type === 'type') {
                    const cellDate = new Date(data.split('-').reverse().join('-'));
                    const fromDate = dateFrom ? new Date(dateFrom) : null;
                    const toDate = dateTo ? new Date(dateTo) : null;
                    
                    if (fromDate && cellDate < fromDate) return false;
                    if (toDate && cellDate > toDate) return false;
                }
                return true;
            });
        }
        
        // Apply amount range filter
        if (amountRange) {
            const [min, max] = amountRange.split('-').map(v => v.replace(/[^\d]/g, ''));
            table.column(4).search(function(data, type, val, meta) {
                if (type === 'display' || type === 'type') {
                    const amount = parseFloat(data.replace(/[^\d.-]/g, '')) || 0;
                    if (max) {
                        return amount >= parseFloat(min) && amount <= parseFloat(max);
                    } else {
                        return amount >= parseFloat(min);
                    }
                }
                return true;
            });
        }
        
        // Redraw table
        table.draw();
        
        setTimeout(() => {
            hideLoadingOverlay();
        }, 500);
    }
    
    // Clear all filters
    function clearFilters() {
        showLoadingOverlay();
        
        // Clear filter inputs
        $('#dateFrom').val('');
        $('#dateTo').val('');
        $('#transactionTypeFilter').val('');
        $('#amountRangeFilter').val('');
        $('#descriptionSearch').val('');
        $('#referenceSearch').val('');
        
        // Clear table filters
        table.search('').columns().search('').draw();
        
        setTimeout(() => {
            hideLoadingOverlay();
        }, 500);
    }
    
    // Refresh table
    function refreshTable() {
        showLoadingOverlay();
        table.ajax.reload(null, false);
        setTimeout(() => {
            hideLoadingOverlay();
        }, 1000);
    }
    
    // Handle transaction detail clicks
    $('#doubleEntriesTable').on('click', 'a[href*="transactions/details"]', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        
    // Show loading state
        // Swal.fire({
        //     title: 'Loading...',
        //     text: 'Loading transaction details',
        //     allowOutsideClick: false,
        //     didOpen: () => {
        //         Swal.showLoading();
        //     }
        // });
        
        // Redirect to transaction details
        window.location.href = href;
    });
    
    // Real-time search for description and reference
    $('#descriptionSearch, #referenceSearch').on('input', function() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            applyFilters();
        }, 500);
    });
    
    // Make functions global
    window.applyFilters = applyFilters;
    window.clearFilters = clearFilters;
    window.refreshTable = refreshTable;
});

// Export functionality with loading
function exportDoubleEntries() {
    const btn = document.getElementById('exportBtn');
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Exporting...';
    btn.disabled = true;
    
    setTimeout(() => {
    var table = $('#doubleEntriesTable').DataTable();
    var data = table.data().toArray();
    
    // Create CSV content
    var csv = 'Date,Reference,Description,Type,Debit,Credit,Running Balance\n';
    data.forEach(function(row) {
            // Clean the data for CSV export
            var cleanRow = row.map(function(cell) {
                // Remove HTML tags and clean up the content
                var cleanCell = $('<div>').html(cell).text().trim();
                // Handle empty cells
                if (cleanCell === '' || cleanCell === '-') {
                    return '';
                }
                // Escape commas and quotes
                if (cleanCell.includes(',') || cleanCell.includes('"')) {
                    return '"' + cleanCell.replace(/"/g, '""') + '"';
                }
                return cleanCell;
            });
            csv += cleanRow.join(',') + '\n';
    });
    
    // Download CSV file
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        var url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', 'double-entries-{{ $account_name }}-{{ date("Y-m-d") }}.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Reset button
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Export Successful!',
            text: 'CSV file has been downloaded.',
            timer: 2000,
            showConfirmButton: false
        });
    }, 1000);
}

// Print functionality with loading
function printDoubleEntries() {
    const btn = document.getElementById('printBtn');
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Preparing...';
    btn.disabled = true;
    
    setTimeout(() => {
    var table = $('#doubleEntriesTable').DataTable();
        var originalPageLength = table.page.len();
        
        // Show all records for printing
        table.page.len(-1).draw();
        
        // Create print window
        var printWindow = window.open('', '_blank');
        var tableHtml = $('#doubleEntriesTable').parent().html();
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Double Entries - {{ $account_name }}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; font-weight: bold; }
                    .text-end { text-align: right; }
                    .text-success { color: #28a745; }
                    .text-danger { color: #dc3545; }
                    .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
                    .bg-primary { background-color: #007bff; color: white; }
                    .bg-success { background-color: #28a745; color: white; }
                    .bg-info { background-color: #17a2b8; color: white; }
                    .bg-warning { background-color: #ffc107; color: black; }
                    @media print {
                        body { margin: 0; }
                        table { font-size: 12px; }
                    }
                </style>
            </head>
            <body>
                <h1>Double Entries - {{ $account_name }}</h1>
                <p><strong>Account Code:</strong> {{ $chartAccount->account_code ?? 'N/A' }}</p>
                <p><strong>Account Class:</strong> {{ $chartAccount->accountClassGroup->accountClass->name ?? 'N/A' }}</p>
                <p><strong>Generated:</strong> {{ date('d-m-Y H:i:s') }}</p>
                ${tableHtml}
            </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.print();
        
        // Reset to original page length
        table.page.len(originalPageLength).draw();
        
        // Reset button
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        // Close print window after printing
        setTimeout(function() {
            printWindow.close();
        }, 1000);
    }, 1000);
}
</script>
@endpush