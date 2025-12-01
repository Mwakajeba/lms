@extends('layouts.main')
@section('title', 'Create Account Class Group')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Account Class Groups', 'url' => route('accounting.account-class-groups.index'), 'icon' => 'bx bx-category'],
            ['label' => 'Create Group', 'url' => '#', 'icon' => 'bx bx-plus-circle']
        ]" />

            <h6 class="mb-0 text-uppercase">CREATE NEW ACCOUNT CLASS GROUP</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    @include('account-class-groups.form')
                </div>
            </div>
        </div>
    </div>
@endsection