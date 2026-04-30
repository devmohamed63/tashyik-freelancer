<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordResetOtpController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Step 1: Send OTP to user's email
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = $request->email;

        DB::table('password_reset_otps')->where('email', $email)->delete();
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        $user = User::where('email', $email)->first();
        $user->notify(new PasswordResetOtpNotification($otp));

        session()->put('reset_email', $email);

        return redirect()->route('dashboard.password.otp.verify.form')
            ->with('status', __('passwords.otp_sent'));
    }

    /**
     * Show the verify OTP form.
     */
    public function showVerifyForm()
    {
        if (!session()->has('reset_email')) {
            return redirect()->route('dashboard.password.request');
        }

        return view('auth.verify-otp');
    }

    /**
     * Step 2: Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = session()->get('reset_email');

        if (!$email) {
            return redirect()->route('dashboard.password.request');
        }

        $record = DB::table('password_reset_otps')
            ->where('email', $email)
            ->first();

        if (! $record) {
            return back()->withErrors(['otp' => __('passwords.otp_invalid')]);
        }

        if (now()->isAfter($record->expires_at)) {
            DB::table('password_reset_otps')->where('email', $email)->delete();
            return back()->withErrors(['otp' => __('passwords.otp_expired')]);
        }

        if (! Hash::check($request->otp, $record->otp)) {
            return back()->withErrors(['otp' => __('passwords.otp_invalid')]);
        }

        session()->put('otp_verified', true);

        return redirect()->route('dashboard.password.reset.form');
    }

    /**
     * Show the reset password form.
     */
    public function showResetForm()
    {
        if (!session()->get('otp_verified') || !session()->has('reset_email')) {
            return redirect()->route('dashboard.password.request');
        }

        return view('auth.reset-password');
    }

    /**
     * Step 3: Reset password using OTP
     */
    public function resetPassword(Request $request)
    {
        $email = session()->get('reset_email');

        if (!$email || !session()->get('otp_verified')) {
            return redirect()->route('dashboard.password.request');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $record = DB::table('password_reset_otps')
            ->where('email', $email)
            ->first();

        // Security check again to make sure OTP didn't expire
        if (! $record || now()->isAfter($record->expires_at)) {
            DB::table('password_reset_otps')->where('email', $email)->delete();
            return redirect()->route('dashboard.password.request')->withErrors(['email' => __('passwords.otp_expired')]);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('dashboard.password.request')->withErrors(['email' => __('passwords.user')]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $user->tokens()->delete();
        DB::table('password_reset_otps')->where('email', $email)->delete();

        session()->forget(['reset_email', 'otp_verified']);

        return redirect()->route('login')->with('status', __('passwords.reset'));
    }
}
