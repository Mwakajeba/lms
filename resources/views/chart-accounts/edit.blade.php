@extends('layouts.main')
@section('title', 'Edit Chart Account')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Chart of Accounts', 'url' => route('accounting.chart-accounts.index'), 'icon' => 'bx bx-spreadsheet'],
            ['label' => 'Edit Account', 'url' => '#', 'icon' => 'bx bx-edit']
             ]" />
            <h6 class="mb-0 text-uppercase">EDIT CHART ACCOUNT</h6>
            <hr />
            <div class="card">
                <div class="card-body">
                    @include('chart-accounts.form')
                </div>
            </div>
        </div>
    </div>
@endsection