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
    
    'messages' => [
        'account_approval'=> 'Please wait for approval to access your account.',
        'registeration' => [
            'success'               => 'You have registered successfully.',
            'phone_unique'          => 'The phone number has already been taken.',
        ],
        'login' => [
            'success'               => 'Login Successful.',
            'failed'                => 'Invalid Credentials! Please try again.',
        ],
        'logout' => [
            'success'               => 'Successfully logged out.',
        ],
        'forgot_password' => [
            'success'               => 'We have sent email with reset password link. Please check your inbox!.',
            'success_update'        => 'Your password has been reset successfully',
            'otp_sent'              => 'We have sent email with OTP. Please check your inbox!.',
            'validation'            => [
                'phone_number_not_found'=> "We can't find a user with that phone number.",
				'verified_phone_number' => 'This password reset phone number is verified.',
				'email_not_found'       => "We can't find a user with that e-mail address.",
                'incorrect_password'    => 'Incorrect current password! Please try again.',
                'invalid_otp'           => 'This password reset otp is invalid.',
                'expire_otp'            => 'This password reset otp is expired.',
                'verified_otp'          => 'This password reset otp is verified.',				
                'expire_request'        => 'This password reset request is expired.',
				'invalid_request'       => 'This password reset request is invalid.',
                'invalid_token_email'   => 'Invalid Token or Email!',
            ],
        ],

        'could_not_create_token' => 'Could not create token. Please try again later.',
    ],

    'unauthorize'  => 'You are not authorized to perform this action.',
    'account_suspended' => 'Your account has been suspended!',

    'mfa_required' => 'MFA required',
    'mfa_verified' => 'MFA verified',
    'google_authenticator_not_setup' => 'MFA setup required',
    'invalid_token' => 'Invalid token',
    'invalid_otp'   => 'Invalid OTP',
    'verification_failed' => 'Verification failed',
    


];
