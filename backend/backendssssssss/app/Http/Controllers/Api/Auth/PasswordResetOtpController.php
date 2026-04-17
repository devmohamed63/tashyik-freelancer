<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordResetOtpController extends Controller
{
    /**
     * Step 1: Send OTP to user's email
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = $request->email;

        // Delete any previous OTPs for this email
        DB::table('password_reset_otps')->where('email', $email)->delete();

        // Generate a 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store hashed OTP with 10-minute expiry
        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
        ]);

        // Send OTP via email
        $user = User::where('email', $email)->first();
        $user->notify(new PasswordResetOtpNotification($otp));

        return response()->json([
            'message' => __('passwords.otp_sent'),
        ]);
    }

    /**
     * Step 2: Verify OTP
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $record = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->first();

        if (! $record) {
            return response()->json([
                'errors' => ['otp' => [__('passwords.otp_invalid')]],
            ], 422);
        }

        // Check expiry
        if (now()->isAfter($record->expires_at)) {
            DB::table('password_reset_otps')->where('email', $request->email)->delete();

            return response()->json([
                'errors' => ['otp' => [__('passwords.otp_expired')]],
            ], 422);
        }

        // Check OTP
        if (! Hash::check($request->otp, $record->otp)) {
            return response()->json([
                'errors' => ['otp' => [__('passwords.otp_invalid')]],
            ], 422);
        }

        return response()->json([
            'message' => __('passwords.otp_verified'),
        ]);
    }

    /**
     * Step 3: Reset password using OTP
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $record = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->first();

        if (! $record) {
            return response()->json([
                'errors' => ['otp' => [__('passwords.otp_invalid')]],
            ], 422);
        }

        // Check expiry
        if (now()->isAfter($record->expires_at)) {
            DB::table('password_reset_otps')->where('email', $request->email)->delete();

            return response()->json([
                'errors' => ['otp' => [__('passwords.otp_expired')]],
            ], 422);
        }

        // Check OTP
        if (! Hash::check($request->otp, $record->otp)) {
            return response()->json([
                'errors' => ['otp' => [__('passwords.otp_invalid')]],
            ], 422);
        }

        // Reset password
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'errors' => ['email' => [__('passwords.user')]],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all tokens for security
        $user->tokens()->delete();

        // Delete OTP record
        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        return response()->json([
            'message' => __('passwords.reset'),
            'phone' => $user->phone,
        ]);
    }
}
