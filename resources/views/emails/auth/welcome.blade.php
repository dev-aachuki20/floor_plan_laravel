@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Hello, {{ $user->full_name ?? ''}}</h4>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Thank you for registering with us. We are excited to have you on board.</p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Here are your account details:</p>
    <ul>
        <li><strong>Email:</strong> {{ $user->user_email ?? '' }}</li>
        <li><strong>Role:</strong> {{ $user->role->role_name ?? '' }}</li>
    </ul>

    @if($mfaMethod == 'google')
     
       @if($qrCodeImageUrl)

       <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">To access the system you first need to activate your security MFA token by scanning the following QR code using your Google authenticator app</p>

       <img src="{{ $qrCodeImageUrl }}"/>

       @else

       <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">To access the system, please enter your current MFA code from your Google Authenticator app.</p>

       @endif

    @elseif($mfaMethod == 'email')
        <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">To proceed with logging in, please verify your identity using the OTP provided below:</p>

        <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Never share OTP with anyone: <br><span style="background-color: #295597;color: #fff;padding: 7px 15px;display: table;margin: 10px auto 0;border-radius: 4px;font-weight: bold;">{{ $otp }}</span></p>

        <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">(This OTP will expire in {{ $otp_expiry }} minutes.)</p>

    @endif


    <a href="{{ config('app.site_url') }}/login" style="font-family: 'Barlow', sans-serif; color:#fff; text-transform: uppercase; font-size:18px; line-height: 13px; border-radius: 5px; background-color: #006AF2;box-shadow:8px 6px 15px 0px rgba(0, 97, 222, 0.25); padding: 21px 28px; display: inline-block; text-decoration: none;margin-bottom: 27px;">Login</a>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">If you're having trouble click the below link for "Reset Password" or Copy and Paste below URL into your web browser  <a href="{{ $setPasswordUrl }}">{{ $setPasswordUrl }}</a></p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">If you have any questions, feel free to reach out to us.</p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Thank you for using our application!</p>

    <div class="regards" style="color: #000; font-weight: 700; font-size: 16px;margin-bottom: 20px;">Regards,<br> {{ config('app.name') }}</div>

@endsection
