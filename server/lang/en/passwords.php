<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | outcome such as failure due to an invalid password / reset token.
    |
    */

    'reset' => 'Your password has been reset.',
    'sent' => 'We have emailed your password reset link.',
    'throttled' => 'Please wait before retrying.',
    'token' => 'This password reset token is invalid.',
    'user' => "We can't find a user with that email address.",

    // Reset password notification
    'reset_password_subject' => 'Reset Your Password',
    'greeting' => 'Hello :name,',
    'reset_password_line_1' => 'You are receiving this email because we received a password reset request for your account.',
    'reset_password_action' => 'Reset Password',
    'reset_password_line_2' => 'This password reset link will expire in :minutes minutes.',
    'reset_password_line_3' => 'If you did not request a password reset, no further action is required.',
    'salutation' => 'Regards, :app',

    // OTP password reset
    'otp_sent' => 'A verification code has been sent to your email.',
    'otp_verified' => 'Code verified successfully.',
    'otp_invalid' => 'The verification code is incorrect.',
    'otp_expired' => 'The verification code has expired. Please request a new one.',
    'otp_subject' => ':app - Password Reset Code',
    'otp_greeting' => 'Hello!',
    'otp_intro' => 'You requested a password reset. Use the following code:',
    'otp_expiry' => 'This code will expire in 10 minutes.',
    'otp_ignore' => 'If you did not request a password reset, please ignore this email.',
    'otp_salutation' => 'Regards, :app',

];
