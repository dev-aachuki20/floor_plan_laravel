@extends('emails.layouts.admin')

@section('email-content')

    <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Dear {{ $name ?? "" }}</h4>
    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">We noticed a login attempt to your account. To proceed with logging in, please verify your identity using the OTP provided below:</p>
    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Never share OTP with anyone: <br><span style="background-color: #295597;color: #fff;padding: 7px 15px;display: table;margin: 10px auto 0;border-radius: 4px;font-weight: bold;">{{ $otp }}</span></p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">(This OTP will expire in {{ $otpExpire }} minutes.)</p>
    
    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Enter this OTP on the login screen to confirm your identity. If you did not attempt to log in, please secure your account immediately by resetting your password.</p>
    
    <p style="font-size: 16px; line-height: 25.5px; font-weight: 600;color: #000; margin-bottom: 0; margin-top:27px;">Thank you for helping us keep your account secure.</p>
    <div class="regards" style="color: #000; font-weight: 700; font-size: 16px;margin-bottom: 20px;">Regards,<br> {{ config('app.name') }}</div>

@endsection
