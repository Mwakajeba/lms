<?php
namespace App\Http\Controllers;

use App\Models\OtpCode;
use Illuminate\Support\Carbon;
use App\Helpers\SmsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class OtpEmailController extends Controller
{
    public function showEmailForm()
    {
        return view('auth.email-otp-form');
    }

    public function sendOtpEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
        ]);

        $otp = rand(100000, 999999);

        // Store the OTP in DB
        OtpCode::create([
            'phone' => $request->email,
            'code' => $otp,
            'expires_at' => now()->addMinutes(5),
            'is_used' => 0
        ]);

        // Send email
        Mail::raw("Your OTP is: $otp", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('SmartFinance OTP Code');
        });

        // Redirect to verification page
        session(['phone' => $request->email]);
        return redirect()->route('verify-otp-password');
    }



}
