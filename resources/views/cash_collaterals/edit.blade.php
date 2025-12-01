@extends('layouts.main')
@section('title', 'Edit Cash Deposit')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Deposit Accounts', 'url' => route('cash_collaterals.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Edit Deposit Account', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />        
        <h6 class="mb-0 text-uppercase">EDIT DEPOSIT ACCOUNT</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('cash_collaterals.form')
            </div>
        </div>       
    </div>
</div>
@endsection