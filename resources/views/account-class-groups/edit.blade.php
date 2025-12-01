@extends('layouts.main')
@section('title', 'Edit Account Class Group')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Account Class Groups', 'url' => route('accounting.account-class-groups.index'), 'icon' => 'bx bx-category'],
            ['label' => 'Edit Group', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

            <h6 class="mb-0 text-uppercase">EDIT ACCOUNT CLASS GROUP</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    @include('account-class-groups.form')
                </div>
            </div>
        </div>
    </div>
@endsection