@extends('layouts.main')

@section('title', 'Group Repayment')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Groups', 'url' => route('groups.index'), 'icon' => 'bx bx-group'],
                ['label' => 'Group Details', 'url' => route('groups.show', Hashids::encode($group->id)), 'icon' => 'bx bx-group'],
                ['label' => 'Group Repayments', 'url' => '#', 'icon' => 'bx bx-info-circle']
            ]" />
        </div>
        <h6 class="mb-0 text-uppercase">GROUP REPAYMENT FOR {{ $group->name }}</h6>
        <hr />

        <div class="card radius-10">
            <div class="card-body">
                <form action="{{ route('groups.groupStore', Hashids::encode($group->id)) }}" method="POST">
                    @csrf

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Repayment Schedule Details</h4>
                        <div class="d-flex gap-2 align-items-center">
                            <h5 class="mb-0">Total Amount to Pay: <strong id="total-amount-display">{{ number_format($totalAmountToPay, 2) }}</strong></h5>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                @if($totalAmountToPay <=0) disabled @endif>
                                <i class="bx bx-save"></i> Generate Repayment
                            </button>
                        </div>
                    </div>

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="repayment-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Due Date</th>
                                    <th>Installment Amount</th>
                                    <th>Fee</th>
                                    <th>Penalty</th>
                                    <th>Already Paid</th>
                                    <th>Total Due</th>
                                    <th>Amount to Pay</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($repaymentData as $customerData)
                                @foreach($customerData['loans'] as $loanData)
                                <tr id="customer-row-{{ $customerData['customer']->id }}-loan-{{ $loanData['loan']->id }}"
                                    data-original-amount="{{ $loanData['amount_to_pay'] }}">

                                    <td>{{ $loop->parent->index + 1 }}</td>
                                    <td>
                                        @if($loop->first)
                                        <strong>{{ $customerData['customer']->name }}</strong><br>
                                        @endif
                                    </td>
                                    <td>{{ Carbon\Carbon::parse($loanData['schedule']->due_date)->format('d M, Y') }}</td>
                                    <td>{{ number_format($loanData['installment_amount'], 2) }}</td>
                                    <td>{{ number_format($loanData['fee_amount'], 2) }}</td>
                                    <td>{{ number_format($loanData['penalty_amount'], 2) }}</td>
                                    <td>{{ number_format($loanData['amount_already_paid'], 2) }}</td>
                                    <td>{{ number_format($loanData['total_due'], 2) }}</td>
                                    <td>
                                        <input type="hidden" name="repayments[{{ $customerData['customer']->id }}][{{ $loanData['loan']->id }}][schedule_id]" value="{{ $loanData['schedule']->id }}">
                                        <input type="hidden" name="repayments[{{ $customerData['customer']->id }}][{{ $loanData['loan']->id }}][customer_id]" value="{{ $customerData['customer']->id }}">
                                        <input type="hidden" name="repayments[{{ $customerData['customer']->id }}][{{ $loanData['loan']->id }}][loan_id]" value="{{ $loanData['loan']->id }}">

                                        <input type="number" step="0.01" name="repayments[{{ $customerData['customer']->id }}][{{ $loanData['loan']->id }}][amount_paid]"
                                            class="form-control amount-input"
                                            value="{{ old('repayments.'.$customerData['customer']->id.'.'.$loanData['loan']->id.'.amount_paid', number_format($loanData['amount_to_pay'], 2, '.', '')) }}"
                                            min="0" max="{{ number_format($loanData['amount_to_pay'], 2, '.', '') }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-customer" data-row-id="customer-row-{{ $customerData['customer']->id }}-loan-{{ $loanData['loan']->id }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No unpaid schedules found for this group.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: none;
    }

    .form-control,
    .form-select {
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
    }

    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    .table-responsive {
        overflow-x: auto;
    }

    th,
    td {
        white-space: nowrap;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const repaymentTable = document.querySelector('#repayment-table');
        const totalAmountDisplay = document.querySelector('#total-amount-display');

        function updateTotalAmount() {
            let total = 0;
            document.querySelectorAll('.amount-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            totalAmountDisplay.textContent = total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        repaymentTable.addEventListener('click', function(event) {
            if (event.target.closest('.remove-customer')) {
                const button = event.target.closest('.remove-customer');
                const rowId = button.getAttribute('data-row-id');
                const row = document.getElementById(rowId);
                if (row) {
                    row.remove();
                    updateTotalAmount();
                }
            }
        });

        repaymentTable.addEventListener('input', function(event) {
            if (event.target.classList.contains('amount-input')) {
                updateTotalAmount();
            }
        });

        updateTotalAmount();
    });
</script>
@endpush