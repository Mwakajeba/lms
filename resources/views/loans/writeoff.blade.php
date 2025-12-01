

@extends('layouts.main')
@section('title', 'Write Off Loan')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.list'), 'icon' => 'bx bx-money'],
            ['label' => 'Write Off Loan', 'url' => '#', 'icon' => 'bx bx-block']
        ]" />
        <h6 class="mb-0 text-uppercase">WRITE OFF LOAN</h6>
        <hr/>
        <div class="card border-danger shadow">
            <div class="card-body">
                <form method="POST" action="{{ route('loans.writeoff.confirm', $hashid) }}">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Loan ID</label>
                            <div class="form-control bg-light">{{ $loan->loanNo ?? $loan->id }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Borrower</label>
                            <div class="form-control bg-light">{{ $loan->customer->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Outstanding Amount</label>
                            <div class="form-control bg-light">TZS {{ number_format($loan->amount_total, 2) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status</label>
                            <div class="form-control bg-light">{{ $loan->status }}</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Write-Off Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="writeoff_type" id="direct_writeoff" value="direct" checked>
                            <label class="form-check-label" for="direct_writeoff">Direct Write Off</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="writeoff_type" id="provision_writeoff" value="provision">
                            <label class="form-check-label" for="provision_writeoff">Using Provision</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-bold">Reason for Write-Off</label>
                        <textarea name="reason" id="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-danger"><i class="bx bx-check"></i> Confirm Write Off</button>
                        <a href="{{ route('loans.show', $hashid) }}" class="btn btn-secondary"><i class="bx bx-arrow-back"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
