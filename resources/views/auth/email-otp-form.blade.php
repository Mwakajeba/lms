@extends('layouts.auth')

@section('title', 'Smartfinance – Forgot Password')

@section('content')
    <div class="authentication-header"></div>
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                <div class="col mx-auto">
                    <div class="mb-4 text-center">
                        <img src="{{ asset('assets/images/logo1.png') }}" width="180" alt="" />
                    </div>
                    <div class="card rounded-4">
                        <div class="card-body">
                            <div class="p-4 rounded">
                                <div class="text-center">
							       <img src="{{ asset('assets/images/icons/lock.png')}}" width="120" alt="" />
						        </div>
                                <div class="login-separater text-center mb-4">
                                    <span>FORGOT PASSWORD</span>
                                    <hr />
                                </div>

                                {{-- Show error message --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <form class="row g-3" method="POST" action="{{ route('email-otp-send') }}">
                                    @csrf
                                    <div class="col-12">
                                        <label for="inputPhone" class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" required> 
                                    </div>
                                    <div class="col-md-6 text">
                                        <a href="{{ route('login') }}">Sign In</a>
                                    </div>
            
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bxs-key"></i> Get Password
                                            </button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

