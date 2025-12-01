@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Add New Supplier')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Suppliers', 'url' => route('accounting.suppliers.index'), 'icon' => 'bx bx-store'],
            ['label' => 'Create Supplier', 'url' => '#', 'icon' => 'bx bx-plus-circle']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">ADD NEW SUPPLIER</h6>
                    <p class="text-muted mb-0">Create a new supplier record</p>
                </div>
                <div>
                    <a href="{{ route('accounting.suppliers.index') }}" class="btn btn-secondary">
                        Back to Suppliers
                    </a>
                </div>
            </div>
            <hr />

            @include('accounting.suppliers.form')
        </div>
    </div>
@endsection