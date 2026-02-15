@extends('layouts.main')

@section('title', 'Cash Deposit Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Customers', 'url' => route('customers.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Customer', 'url' => route('customers.show', Hashids::encode($cashCollateral->customer_id)), 'icon' => 'bx bx-user'],
            ['label' => 'Deposit', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />

        <!-- Header Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="text-primary mb-2">Deposit Account Details</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Customer:</strong> {{ $cashCollateral->customer->name }}</p>
                                        <p class="mb-1"><strong>Type:</strong> {{ $cashCollateral->type->name }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Current Balance:</strong>
                                            <span class="badge bg-success fs-6">TSHS {{ number_format($cashCollateral->amount, 2) }}</span>
                                        </p>
                                        <p class="mb-1"><strong>Branch:</strong> {{ $cashCollateral->branch->name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group" role="group">
                                    @can('deposit cash collateral')
                                    <a href="{{ route('cash_collaterals.deposit', Hashids::encode($cashCollateral->id)) }}"
                                        class="btn btn-success">
                                        <i class="bx bx-plus me-1"></i> Deposit
                                    </a>
                                    @endcan

                                    @can('withdraw cash collateral')
                                    <a href="{{ route('cash_collaterals.withdraw', Hashids::encode($cashCollateral->id)) }}"
                                        class="btn btn-warning">
                                        <i class="bx bx-minus me-1"></i> Withdraw
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0">Transaction History</h5>
                            </div>
                            @can('print transactions')
                            <div class="col-md-6 text-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary" onclick="printTransactions()">
                                        <i class="bx bx-printer me-1"></i> Print
                                    </button>

                                </div>
                            </div>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        @if($transactions->count() > 0 || true)
                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap" id="transactionsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                        <th>Bank Account</th>
                                        <th>Processed By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via Ajax -->
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="bx bx-money bx-lg text-muted mb-3"></i>
                            <h5 class="text-muted">No transactions found</h5>
                            <p class="text-muted">No deposits or withdrawals have been made for this cash deposit yet.</p>
                            @can('deposit cash collateral')
                            <a href="{{ route('cash_collaterals.deposit', Hashids::encode($cashCollateral->id)) }}"
                                class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Make First Deposit
                            </a>
                            @endcan
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        @if($transactions->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Transaction Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h6>Total Deposits</h6>
                                        <h4>TSHS {{ number_format($transactions->where('type', 'Deposit')->sum('amount'), 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h6>Total Withdrawals</h6>
                                        <h4>TSHS {{ number_format($transactions->where('type', 'Withdrawal')->sum('amount'), 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h6>Total Transactions</h6>
                                        <h4>{{ $transactions->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h6>Current Balance</h6>
                                        <h4>TSHS {{ number_format($cashCollateral->amount, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Hidden div for PDF content -->
<div id="pdfContent" style="display: none;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2>Cash Deposit Transaction Report</h2>
        <p><strong>Customer:</strong> {{ $cashCollateral->customer->name }}</p>
        <p><strong>Type:</strong> {{ $cashCollateral->type->name }}</p>
        <p><strong>Current Balance:</strong> TSHS {{ number_format($cashCollateral->amount, 2) }}</p>
        <p><strong>Report Date:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if($transactions->count() > 0)
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Date</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Type</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Description</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Amount</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $transaction['date']->format('d/m/Y') }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $transaction['type'] }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">{{ $transaction['description'] }}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                    TSHS {{ number_format($transaction['amount'], 2) }}
                </td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">
                    TSHS {{ number_format($transaction['balance'], 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function printTransactions() {
        const printContent = document.getElementById('pdfContent').innerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Cash Deposit Transactions - {{ $cashCollateral->customer->name }}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f8f9fa; }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        @media print {
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }

    function deleteTransaction(encodedId, type, transactionTypeName) {
        Swal.fire({
            title: 'Delete ' + type + '?',
            html: `
                <p class="mb-3">This will mark the ${transactionTypeName} as deleted. Deletion requires super-admin approval.</p>
                <div class="form-group text-start">
                    <label for="deletion_reason" class="form-label">Reason for Deletion <span class="text-danger">*</span></label>
                    <textarea id="deletion_reason" class="form-control" rows="3" placeholder="Please provide a reason for deleting this ${transactionTypeName}..." required></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            preConfirm: () => {
                const reason = document.getElementById('deletion_reason').value.trim();
                if (!reason) {
                    Swal.showValidationMessage('Please provide a reason for deletion');
                    return false;
                }
                if (reason.length < 10) {
                    Swal.showValidationMessage('Reason must be at least 10 characters');
                    return false;
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const url = type === 'Deposit' ?
                    `/receipts/${encodedId}` :
                    `/payments/${encodedId}`;

                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            deletion_reason: result.value
                        })
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (response.ok && data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message || 'Transaction deleted successfully. Awaiting super-admin approval.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false,
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'Failed to delete transaction.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        Swal.fire('Error', error.message || 'Unexpected error.', 'error');
                    });

            }
        });
    }

    function approveDeleteDeposit(id) {
        Swal.fire({
            title: 'Approve Deletion?',
            text: "This will permanently approve the deletion of this deposit.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/receipts/${id}/approve-delete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(async response => {
                    const data = await response.json();
                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Approved!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Failed to approve deletion.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to approve deletion.', 'error');
                });
            }
        });
    }

    function restoreDeposit(id) {
        Swal.fire({
            title: 'Restore Deposit?',
            text: "This will restore the deposit and reverse the deletion.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/receipts/${id}/restore`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                })
                .then(async response => {
                    const data = await response.json();
                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Restored!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Failed to restore deposit.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to restore deposit.', 'error');
                });
            }
            });
        }

        function showDeletionReason(reason) {
            Swal.fire({
                title: 'Deletion Reason',
                html: `<div class="text-start"><p class="mb-0">${reason}</p></div>`,
                icon: 'info',
                confirmButtonText: 'Close'
            });
        }
</script>
<script>
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("cash_collaterals.show", Hashids::encode($cashCollateral->id)) }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            },
            columns: [
                { data: 'formatted_date', name: 'date', title: 'Date' },
                { data: 'type_badge', name: 'type', title: 'Type', orderable: false },
                { data: 'description', name: 'description', title: 'Description' },
                { data: 'formatted_amount', name: 'amount', title: 'Amount', orderable: false },
                { data: 'formatted_balance', name: 'balance', title: 'Balance', orderable: false },
                { data: 'bank_account', name: 'bank_account', title: 'Bank Account' },
                { data: 'user', name: 'user', title: 'Processed By' },
                { 
                    data: 'actions', 
                    name: 'actions', 
                    title: 'Actions',
                    orderable: false, 
                    searchable: false 
                }
            ],
            responsive: true,
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Search transactions...",
                processing: "Loading transactions..."
            },
            columnDefs: [{
                    targets: -1,
                    responsivePriority: 1
                },
                {
                    targets: [1, 3, 4, 7],
                    className: 'text-center'
                }
            ],
            drawCallback: function(settings) {
                // Re-initialize tooltips after each draw
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
    });

    // Function to print deposit receipt from table action button
    function printDepositReceiptFromTable(receiptId) {
        const url = '{{ route("cash_collaterals.printDepositReceipt", ":id") }}'.replace(':id', receiptId);
        window.open(url, '_blank', 'width=800,height=600');
    }

    // Function to print withdrawal receipt from table action button
    function printWithdrawalReceiptFromTable(paymentId) {
        const url = '{{ route("cash_collaterals.printWithdrawalReceipt", ":id") }}'.replace(':id', paymentId);
        window.open(url, '_blank', 'width=800,height=600');
    }
</script>
@endpush