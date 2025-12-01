@extends('layouts.auth')

@section('title', 'Smartfinance – Login')

@section('content')
    <div class="authentication-header"></div>
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                <div class="col mx-auto">
                    <div class="mb-4 text-center">
                        <img src="{{ asset('assets/images/logo1.png') }}" width="180" alt="" />
                    </div>
                    
                    <!-- Language Switcher for Auth Pages -->
                    <div class="text-center mb-3">
                        @include('incs.languageSwitcher')
                    </div>
                    <div class="card rounded-4">
                        <div class="card-body">
                            <div class="p-4 rounded">
                                <!-- <div class="text-center">
                                    <img src="{{ asset('assets/images/logo1.png') }}" width="180" alt="inviteMe" />

                                </div> -->
                                <div class="text-center">
							       <img src="{{ asset('assets/images/icons/smartfinance.png')}}" width="200" alt="" />
						        </div>
                                <div class="login-separater text-center mb-4">
                                    <span>{{ __('app.sign_in') }} {{ __('app.with_phone') }}</span>
                                    <hr />
                                </div>

                                {{-- Show error message --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <form class="row g-3" method="POST" action="{{ url('/login') }}">
                                    @csrf
                                    <div class="col-12">
                                        <label for="inputPhone" class="form-label">{{ __('app.phone_number') }}</label>
                                        <input type="text" class="form-control" name="phone" placeholder="+255715XXXXXX or 0715XXXXXX or 255715XXXXXX" id="phone" value="{{ old('phone') }}" required> 
                                        <small class="form-text text-muted">
                                            <i class="bx bx-info-circle me-1"></i>
                                            {{ __('app.phone_number_help') }}
                                        </small>
                                    </div>
                                    <div class="col-12">
                                        <label for="inputChoosePassword" class="form-label">{{ __('app.enter_password') }}</label>
                                        <div class="input-group" id="show_hide_password">
                                            <input type="password" name="password" class="form-control border-end-0" placeholder="{{ __('app.password') }}" id="password" required>
                                            <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <a href="{{ route('forgotPassword') }}">{{ __('app.reset_password_by_phone') }}</a>
                                    </div>
                                    <div class="col-md-12">
                                        <a href="{{ route('email-otp-form') }}">{{ __('app.reset_password_by_email') }}</a>
                                    </div> 
            
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bxs-lock-open"></i> {{ __('app.sign_in') }}
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.querySelector('#show_hide_password');
        if (!container) return;
        var input = container.querySelector('input');
        var toggle = container.querySelector('a');
        var icon = container.querySelector('i');
        if (!input || !toggle || !icon) return;
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            } else {
                input.type = 'password';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            }
        });
    });
</script>
@endpush

