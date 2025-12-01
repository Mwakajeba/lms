<?php

namespace App\Http\Controllers;

use App\Models\OtpCode;
use App\Models\LoginAttempt;
use App\Rules\PasswordValidation;
use App\Services\SystemSettingService;
use Illuminate\Support\Carbon;
use App\Helpers\SmsHelper;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Jenssegers\Agent\Facades\Agent;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $agent = new Agent();

        $deviceInfo = 'Unknown';
        if ($agent::isDesktop()) {
            $deviceInfo = 'Desktop';
        } elseif ($agent::isPhone()) {
            if ($agent::is('iPhone')) {
                $deviceInfo = 'iPhone';
            } elseif ($agent::is('AndroidOS')) {
                $deviceInfo = 'Android Phone';
            } else {
                $deviceInfo = 'Phone';
            }
        } elseif ($agent::isTablet()) {
            if ($agent::is('iPad')) {
                $deviceInfo = 'iPad';
            } else {
                $deviceInfo = 'Tablet';
            }
        }

        $deviceString = $deviceInfo . ' - ' . $agent::browser();

        if (LoginAttempt::isLockedOut($request->ip())) {
            $remainingTime = LoginAttempt::getRemainingLockoutTime($request->ip());

            ActivityLog::create([
                'user_id' => null,
                'model' => 'Auth',
                'action' => 'login_failed',
                'description' => "Login blocked - too many attempts for {$request->phone}",
                'ip_address' => $request->ip(),
                'device' => $deviceString,
                'activity_time' => now(),
            ]);

            return back()->withErrors([
                'phone' => "Account is temporarily locked. Please try again in {$remainingTime} minutes.",
            ])->withInput();
        }

        $user = find_user_by_phone($request->phone);

        if (!$user) {
            LoginAttempt::record($request->phone, $request->ip(), $request->userAgent(), false);

            ActivityLog::create([
                'user_id' => null,
                'model' => 'Auth',
                'action' => 'login_failed',
                'description' => "Login failed - phone not found ({$request->phone})",
                'ip_address' => $request->ip(),
                'device' => $deviceString,
                'activity_time' => now(),
            ]);

            return back()->withErrors([
                'phone' => 'Phone number not found.',
            ])->withInput();
        }

        // Check if user is active
        if ($user->is_active !== 'yes' || $user->status === 'inactive' || $user->status === 'suspended') {
            LoginAttempt::record($request->phone, $request->ip(), $request->userAgent(), false);

            ActivityLog::create([
                'user_id' => $user->id,
                'model' => 'Auth',
                'action' => 'login_failed',
                'description' => "Login failed - user account is {$user->status} (is_active: {$user->is_active})",
                'ip_address' => $request->ip(),
                'device' => $deviceString,
                'activity_time' => now(),
            ]);

            $errorMessage = 'Your account is currently inactive. Please contact your administrator.';
            if ($user->status === 'suspended') {
                $errorMessage = 'Your account has been suspended due to expired subscription. Please contact your administrator.';
            }

            return back()->withErrors([
                'phone' => $errorMessage,
            ])->withInput();
        }

        $credentials = [
            'phone' => $user->phone,
            'password' => $request->password
        ];

        if (Auth::attempt($credentials)) {
            LoginAttempt::record($user->phone, $request->ip(), $request->userAgent(), true);
            LoginAttempt::clearOldAttempts();

            ActivityLog::create([
                'user_id' => $user->id,
                'model' => 'Auth',
                'action' => 'login_success',
                'description' => 'User logged in successfully',
                'ip_address' => $request->ip(),
                'device' => $deviceString,
                'activity_time' => now(),
            ]);

            return redirect()->intended('/change-branch');
        }

        LoginAttempt::record($request->phone, $request->ip(), $request->userAgent(), false);

        ActivityLog::create([
            'user_id' => $user->id,
            'model' => 'Auth',
            'action' => 'login_failed',
            'description' => 'Login failed - wrong password',
            'ip_address' => $request->ip(),
            'device' => $deviceString,
            'activity_time' => now(),
        ]);

        if (LoginAttempt::isLockedOut($request->ip())) {
            $securityConfig = SystemSettingService::getSecurityConfig();
            $duration = $securityConfig['lockout_duration'] ?? 15;

            return back()->withErrors([
                'phone' => "Too many failed attempts. Account is locked for {$duration} minutes.",
            ])->withInput();
        }

        return back()->withErrors([
            'password' => 'Invalid password.',
        ])->withInput();
    }


    public function showForgotPasswordForm()
    {
        return view('auth.forgotPassword');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        // Find user by phone with flexible matching
        $user = find_user_by_phone($request->phone);

        if (!$user) {
            return back()->withErrors([
                'phone' => 'Phone number not found.',
            ])->withInput();
        }

        $verification_code = rand(100000, 999999);

        OtpCode::create([
            'phone' => $user->phone, // Use the normalized phone number
            'code' => $verification_code,
            'expires_at' => Carbon::now()->addMinutes(5)
        ]);

        // Send SMS
        $this->sendSmsVerification($user->phone, $verification_code);

        // Redirect to verification page
        session(['phone' => $user->phone]);
        return redirect()->route('verify-otp-password');
    }

    public function resendOtp($phone)
    {
        // Find user by phone with flexible matching
        $user = find_user_by_phone($phone);

        if (!$user) {
            return back()->withErrors([
                'phone' => 'Phone number not found.',
            ]);
        }

        // Optional: invalidate previous OTPs
        OtpCode::where('phone', $user->phone)->update(['is_used' => 1]);

        // Generate new OTP
        $otpCode = rand(100000, 999999);

        // Save OTP
        OtpCode::create([
            'phone' => $user->phone, // Use the normalized phone number
            'code' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        $this->sendSmsVerification($user->phone, $otpCode);

        // Redirect to verification page
        session(['phone' => $user->phone]);
        return redirect()->route('verify-otp-password');
    }

    protected function sendSmsVerification($phone, $code)
    {
        $message = 'OTP Code is ' . $code;
        SmsHelper::send($phone, $message);
    }

    public function showVerificationForm(Request $request)
    {
        // Get phone number from session
        $phone = session('phone');

        // If phone not in session, redirect or show error
        if (!$phone) {
            return redirect()->route('forgotPassword')->with('error', 'Session expired. Please try again.');
        }

        // Pass to view
        return view('auth.verify-otp-password', compact('phone'));
    }

    public function verifyPasswordCode(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'code' => 'required',
        ]);

        // Find user by phone with flexible matching
        $user = find_user_by_phone($request->phone);

        if (!$user) {
            return back()->withErrors(['phone' => 'Phone number not found.']);
        }

        $otp = OtpCode::where('phone', $user->phone)
            ->where('code', $request->code)
            ->where('expires_at', '>', Carbon::now())
            ->where('is_used', 0)
            ->latest()
            ->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        $otp->update(['is_used' => 1]);

        session(['verified_phone' => $user->phone]);

        return redirect()->route('new-password-form')->with('success', 'Phone verified successfully!');
    }

    public function showNewPasswordForm()
    {
        $phone = session('verified_phone');

        if (!$phone) {
            return redirect()->route('forgot-password')->with('error', 'Session expired.');
        }

        return view('auth.reset-password', compact('phone'));
    }

    public function storeNewPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => ['required', 'confirmed', new PasswordValidation],
        ]);

        // Find user by phone with flexible matching
        $user = find_user_by_phone($request->phone);

        if (!$user) {
            return back()->withErrors(['phone' => 'User not found.']);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        session()->forget('verified_phone');

        return redirect()->route('login')->with('success', 'Password reset successfully. You can now login.');
    }
}
