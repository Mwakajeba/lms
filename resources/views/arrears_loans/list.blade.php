
@extends('layouts.main')

@section('title', 'Loans in Arrears (30+ days)')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Arrears (30+ days)', 'url' => '#', 'icon' => 'bx bx-error']
        ]" />
        <h6 class="mb-0 text-uppercase">Loans in Arrears (30+ days)</h6>
        <hr />

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>
                <strong>Error:</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="card-title mb-0">Loans in Arrears (30+ days)</h6>
                            <a href="{{ route('arrears.loans.pdf') }}" class="btn btn-primary" target="_blank">
                                <i class="bx bx-printer"></i> Export PDF
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered dt-responsive nowrap table-striped" id="arrearsLoansTable">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Customer No</th>
                                        <th>Loan No</th>
                                        <th>Total Loan Outstanding</th>
                                        <th>Total Amount in Arrears</th>
                                        <th>Days in Arrears</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#arrearsLoansTable').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: '{{ route('arrears.loans.list') }}',
        columns: [
            { data: 'customer_name' },
            { data: 'customer_no' },
            { data: 'loan_no' },
            { data: 'total_outstanding', render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: 'amount_in_arrears', render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: 'days_in_arrears' }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search arrears...",
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>'
        },
        pageLength: 25
    });
});
</script>
@endpush
@section('scripts')
<script>
$(document).ready(function() {
    $('#arrearsLoansTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: '{{ route('arrears.loans.list') }}',
        columns: [
            { data: 'customer_name' },
            { data: 'customer_no' },
            { data: 'loan_no' },
            { data: 'total_outstanding', render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: 'amount_in_arrears', render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: 'days_in_arrears' }
        ]
    });
});
</script>
@endsection
