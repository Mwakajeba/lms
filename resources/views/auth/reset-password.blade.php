@extends('layouts.auth')

@section('title', 'Smartfinance – Reset Password')

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
                                    <span>RESET PASSWORD</span>
                                    <hr />
                                </div>

                                {{-- Show error message --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <form class="row g-3" method="POST" action="{{ url('/reset-password') }}">
                                    @csrf
                                   <input type="hidden" name="phone" value="{{ $phone }}" />
                                    <div class="col-12">
                                        <label for="inputChoosePassword" class="form-label">Enter Password</label>
                                        <div class="input-group" id="show_hide_password">
                                            <input type="password" name="password" class="form-control border-end-0" placeholder="Password" id="password" required>
                                            <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="inputChoosePassword" class="form-label">Confirm Password</label>
                                        <div class="input-group" id="show_hide_password">
                                            <input type="password" name="password_confirmation" class="form-control border-end-0" placeholder="Confirm password" id="password_confirmation" required>
                                            <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                        </div>
                                    </div>
            
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bxs-lock-open"></i> Reset password
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
