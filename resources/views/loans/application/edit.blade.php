@extends('layouts.main')

@section('title', 'Edit Loan Application')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Loan Applications', 'url' => route('loans.application.index'), 'icon' => 'bx bx-file-plus'],
            ['label' => 'Edit Application', 'url' => '#', 'icon' => 'bx bx-edit'],
        ]" />
        <!-- @can('view loan') -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">EDIT LOAN APPLICATION</h6>
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
                        <h6 class="mb-0"><i class="bx bx-edit me-2"></i>Edit Loan Application
                            #{{ $loanApplication->id }}</h6>
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