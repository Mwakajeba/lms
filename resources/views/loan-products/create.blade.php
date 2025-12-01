@extends('layouts.main')
@section('title', 'Create Loan Product')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Loan Products', 'url' => route('loan-products.index')],
            ['label' => 'Create Loan Product']
        ]" />
            <h6 class="mb-0 text-uppercase">CREATE NEW LOAN PRODUCT</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    @include('loan-products.form')
                </div>
            </div>
        </div>
    </div>
@endsection