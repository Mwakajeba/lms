@extends('layouts.main')
@section('title', 'Edit Customer')

@section('content')
<div class="page-wrapper">
    <div class="page-content">        
        <x-breadcrumbs :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Customers', 'url' => route('customers.index')],
            ['label' => 'Edit Customer']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT CUSTOMER</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('customers.form')
            </div>
        </div>       
    </div>
</div>
@endsection