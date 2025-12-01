@extends('layouts.main')

@section('title', 'Loan Top-Up')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.list'), 'icon' => 'bx bx-money'],
            ['label' => 'Loan Details', 'url' => route('loans.show', $loan->encodedId), 'icon' => 'bx bx-detail'],
            ['label' => 'Top-Up', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">LOAN TOP-UP</h6>
        <hr/>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Loan Top-Up</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('loans.top_up.store', $loan->encodedId) }}">
                        @csrf
                        @php
                            $outstandingBalance = $loan->schedule->sum('remaining_amount');
                        @endphp
                        <div class="mb-3">
                            <label>Outstanding Balance: </label>
                            <strong>{{ number_format($outstandingBalance, 2) }}</strong>
                            <input type="hidden" name="outstanding_balance" value="{{ $outstandingBalance }}">
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Top-Up Amount</label>
                            <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="topup_type" class="form-label">Top-Up Type</label>
                            <select name="topup_type" class="form-control">
                                <option value="restructure">Restructure (replace old loan)</option>
                                <option value="additional">Additional (new loan on top)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Submit Top-Up</button>
                        <a href="{{ route('loans.show', $loan->encodedId) }}" class="btn btn-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
</div>
@endsection
