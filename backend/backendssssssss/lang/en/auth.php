<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'back_home' => 'Back to Home Page',

    // Pages
    'login' => [
        'title' => 'Sign In',
        'description' => 'Enter your email and password to sign in!',
        'forgot_password' => 'Forgot password?',
        'button' => 'Sign In',
        'dont_have_account' => "Don't have an account?",
    ],
    'register' => [
        'title' => 'Sign Up',
        'description' => 'Enter your email and password to sign up!',
        'button' => 'Sign Up',
        'already_have_account' => "Already have an account?",
    ],
    'forgot_passwrod' => [
        'title' => 'Forgot password',
        'description' => 'Forgot your password? No problem. Just let us know your email address and we will email you a password reset OTP.',
        'button' => 'Email Password Reset OTP',
    ],
    'reset_password' => [
        'title' => 'Reset password',
        'description' => 'Enter your new password!',
        'button' => 'Reset password',
    ],
    'verify-otp' => [
        'title' => 'Verify OTP',
        'description' => 'We\'ve sent a 6-digit OTP code to your email. Please enter it here.',
    ],
    'verify_email' => [
        'title' => 'Verify Email Address',
        'email_sent' => 'A new verification link has been sent to the email address you provided during registration.',
        'description' => 'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.',
        'button' => 'Resend Verification Email',
    ],
    'logout' => 'Log Out'

];
