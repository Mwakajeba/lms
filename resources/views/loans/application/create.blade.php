@extends('layouts.main')

@section('title', 'Apply for Loan')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Loan Applications', 'url' => route('loans.application.index'), 'icon' => 'bx bx-file-plus'],
            ['label' => 'Apply for Loan', 'url' => '#', 'icon' => 'bx bx-plus'],
        ]" />
        <!-- @can('view loan') -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">APPLY FOR LOAN</h6>
            <a href="{{ route('loans.application.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Applications
            </a>
        </div>
        <!-- @endcan -->
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-file-plus me-2"></i>New Loan Application</h6>
                    </div>
                    <div class="card-body">
                        @include('loans.application.form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection