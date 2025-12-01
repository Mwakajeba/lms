@extends('layouts.main')

@section('title', 'Edit Penalty')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Penalties', 'url' => route('accounting.penalties.index'), 'icon' => 'bx bx-error-circle'],
            ['label' => 'Edit Penalty', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h6 class="mb-0 text-uppercase">EDIT PENALTY</h6>
                    <p class="text-muted mb-0">Update penalty information</p>
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