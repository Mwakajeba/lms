@php
    use Vinkla\Hashids\Facades\Hashids;
@endphp

@extends('layouts.main')

@section('title', 'Edit Supplier')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Suppliers', 'url' => route('accounting.suppliers.index'), 'icon' => 'bx bx-store'],
            ['label' => 'Edit Supplier', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">EDIT SUPPLIER</h6>
                    <p class="text-muted mb-0">Update supplier information</p>
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