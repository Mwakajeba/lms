@extends('layouts.main')

@section('title', 'Edit Fee')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fees', 'url' => route('accounting.fees.index'), 'icon' => 'bx bx-dollar-circle'],
            ['label' => 'Edit Fee', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">EDIT FEE</h6>
                    <p class="text-muted mb-0">Update fee information</p>
                </div>
                <div>
                    <a href="{{ route('accounting.fees.index') }}" class="btn btn-secondary">
                        Back to Fees
                    </a>
                </div>
            </div>
            <hr />

            @include('accounting.fees.form')
        </div>
    </div>
@endsection