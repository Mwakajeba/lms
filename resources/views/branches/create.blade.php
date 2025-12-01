@extends('layouts.main')
@section('title', 'Create Company')

@section('content')
<div class="page-wrapper">
    <div class="page-content">        
        <h6 class="mb-0 text-uppercase">CREATE NEW BRANCH</h6>
        <hr/>
        <div class="card">
            <div class="card-body">
                @include('branches.form')
            </div>
        </div>       
    </div>
</div>
@endsection