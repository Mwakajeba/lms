@extends('layouts.auth')

@section('title', 'Smartfinance – Phone Verification')

@section('content')
    <div class="authentication-header"></div>
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                <div class="col mx-auto">
                    <div class="card rounded-4">
                        <div class="card-body">
                            <div class="p-4 rounded">
                                <div class="text-center">
                                    <img src="{{ asset('assets/images/icons/lock.png') }}" width="100" alt="Lock Icon" />
                                </div>
                                <div class="login-separater text-center mb-4">
                                    <span>VERIFY YOUR PHONE</span>
                                    <hr />
                                    <div id="countdown-timer" class="text-danger fw-bold mt-2"></div>
                                </div>

                                {{-- Error Message --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <p class="text-center">We sent an OTP to {{ substr($phone, 0, 4) }}****{{ substr($phone, -2) }}</p>

                                <form class="row g-3" method="POST" action="{{ url('/verify-otp-password') }}">
                                    @csrf
                                    <input type="hidden" name="phone" value="{{ $phone }}" />
                                    
                                    <div class="col-12">
                                        <label for="code" class="form-label">Verification Code</label>
                                        <input type="text" class="form-control" name="code" id="code" required autofocus>
                                    </div>

                                    <div class="col-12 text-center">
                                        <a href="{{ route('resend.otp', ['phone' => $phone]) }}">Resend Code</a>
                                    </div>


                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bxs-lock-open"></i> Verify
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let seconds = 300; // 5 minutes
    const timerElement = document.getElementById("countdown-timer");

    function updateTimer() {
        if (!timerElement) return;

        let minutes = Math.floor(seconds / 60);
        let secs = seconds % 60;
        timerElement.textContent = `You have ${minutes}:${secs.toString().padStart(2, '0')} to verify.`;

        if (seconds <= 0) {
            clearInterval(timerInterval);
            window.location.href = "{{ url('/login?expired=1') }}";
        }

        seconds--;
    }

    updateTimer(); // initial run
    const timerInterval = setInterval(updateTimer, 1000);
});
</script>
@endsection




