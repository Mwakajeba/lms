@extends('layouts.main')
@section('title', 'Edit File Type')

@section('content')
<div class="page-wrapper">
    <div class="page-content"> 
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'File Types', 'url' => route('settings.filetypes.index'), 'icon' => 'bx bx-file'],
            ['label' => 'Edit File Type', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT FILE TYPE</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('settings.filetypes.form')
            </div>
        </div>       
    </div>
</div>
@endsection