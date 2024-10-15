@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Hello, {{ $user->full_name ?? ''}}</h4>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Welcome to {{ config('app.name') }}! You are receiving this email because the {{ config('app.name') }} system admin added you as a user.</p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 10px;margin-top: 10px;">Here are your account details:</p>
    <ul style="list-style: none;padding: 0;margin: 0 0 25px;">
        <li style="margin-bottom: 5px;color: #000;"><strong>Email:</strong> {{ $user->user_email ?? '' }}</li>
        <li style="color: #000;"><strong>Role:</strong> {{ $user->role->role_name ?? '' }}</li>
    </ul>

    @if(getSetting('mfa_method') == 'google')
     
       @if($qrCodeImageUrl)

       <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">To access the system you first need to activate your security MFA token by scanning the following QR code using your Google authenticator app</p>

       <div style="text-align: center;"><img src="{{ $qrCodeImageUrl ?? '' }}" style="max-width: 250px;width: 100%;"/></div>

       @else

       <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">To access the system, please enter your current MFA code from your Google Authenticator app.</p>

       @endif

    @elseif(getSetting('mfa_method') == 'email')
        <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">To proceed with logging in, please verify your identity using the OTP provided below:</p>

        <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Never share OTP with anyone: <br><span style="background-color: #295597;color: #fff;padding: 7px 15px;display: table;margin: 10px auto 0;border-radius: 4px;font-weight: bold;">{{ $otp ?? '' }}</span></p>

        <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">(This OTP will expire in {{ $otp_expiry ?? '' }} minutes.)</p>

    @endif

    <a href="{{ config('app.site_url') }}/login" style="font-family: 'Barlow', sans-serif; color:#fff; text-transform: uppercase; font-size:18px; line-height: 13px; border-radius: 5px; background-color: #006AF2;box-shadow:8px 6px 15px 0px rgba(0, 97, 222, 0.25); padding: 21px 28px; display: inline-block; text-decoration: none;margin-bottom: 27px;width: 100%;text-align: center;">Login</a>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Click the link to "Set Your Password" or Copy and Paste below URL into your web browser: <a href="{{ $setPasswordUrl ?? '#' }}" style="word-break: break-all;">{{ $setPasswordUrl ?? '' }}</a></p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">If you have any questions, feel free to reach out to us.</p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: 800; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 0;margin-top: 10px;">Thank you</p>

    <div class="regards" style="color: #000; font-weight: 800; font-family: 'Nunito Sans', sans-serif; font-size: 16px;margin-bottom: 20px;">Regards,<br> {{ config('app.name') }}</div>

@endsection
