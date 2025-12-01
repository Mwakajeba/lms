@extends('layouts.main')

@section('title', 'Add New Fee')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fees', 'url' => route('accounting.fees.index'), 'icon' => 'bx bx-dollar-circle'],
            ['label' => 'Create Fee', 'url' => '#', 'icon' => 'bx bx-plus-circle']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">ADD NEW FEE</h6>
                    <p class="text-muted mb-0">Create a new fee record</p>
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