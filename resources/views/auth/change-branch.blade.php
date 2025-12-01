@extends('layouts.auth')

@section('title', 'Smartfinance – Select Branch')

@section('content')
    <div class="authentication-header"></div>
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                <div class="col mx-auto">
                    <div class="mb-4 text-center">
                        <img src="{{ asset('assets/images/logo1.png') }}" width="180" alt="" />
                    </div>
                    <div class="card rounded-4 shadow-lg">
                        <div class="card-body">
                            <div class="p-4 rounded">
                                <div class="text-center mb-3">
                                    <img src="{{ asset('assets/images/icons/smartfinance.png')}}" width="120" alt="" />
                                </div>
                                <div class="login-separater text-center mb-4">
                                    <span class="fw-bold fs-5">Select Branch</span>
                                    <hr />
                                </div>
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        {{ $errors->first() }}
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('change-branch.submit') }}">
                                    @csrf
                                    <div class="form-group mb-4">
                                        <label for="branch_id" class="form-label fw-semibold">Branch</label>
                                        <select name="branch_id" id="branch_id" class="form-control rounded-pill px-3 py-2" required>
                                            <option value="">Choose branch...</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill mt-2">Continue</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

