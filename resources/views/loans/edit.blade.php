@extends('layouts.main')
@section('title', 'Edit Loan')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Loans', 'url' => route('loans.list'), 'icon' => 'bx bx-money'],
            ['label' => 'Edit Loan', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT LOAN</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('loans.form')
            </div>
        </div>       
    </div>
</div>
@endsection