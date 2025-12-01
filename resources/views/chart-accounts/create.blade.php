@extends('layouts.main')
@section('title', 'Create Chart Account')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Chart of Accounts', 'url' => route('accounting.chart-accounts.index'), 'icon' => 'bx bx-spreadsheet'],
            ['label' => 'Create Account', 'url' => '#', 'icon' => 'bx bx-plus']
             ]" />
            <h6 class="mb-0 text-uppercase">CREATE NEW CHART ACCOUNT</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    @include('chart-accounts.form')
                </div>
            </div>
        </div>
    </div>
@endsection