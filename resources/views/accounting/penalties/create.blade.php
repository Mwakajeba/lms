@extends('layouts.main')

@section('title', 'Add New Penalty')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Penalties', 'url' => route('accounting.penalties.index'), 'icon' => 'bx bx-error-circle'],
            ['label' => 'Create Penalty', 'url' => '#', 'icon' => 'bx bx-plus-circle']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">ADD NEW PENALTY</h6>
                    <p class="text-muted mb-0">Create a new penalty record</p>
                </div>
                <div>
                    <a href="{{ route('accounting.penalties.index') }}" class="btn btn-secondary">
                        Back to Penalties
                    </a>
                </div>
            </div>
            <hr />

            @include('accounting.penalties.form')
        </div>
    </div>
@endsection