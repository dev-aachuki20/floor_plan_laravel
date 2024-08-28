@extends('emails.layouts.admin')

@section('email-content')

    <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Dear {{ $name ?? "" }}</h4>
    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">You are receiving this email because we received a password reset request for your account</p>
    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Please click on the link below to reset your password and get access to your account :</p>

    <a href="{{ $reset_password_url }}" style="font-family: 'Barlow', sans-serif; color:#fff; text-transform: uppercase; font-size:18px; line-height: 13px; border-radius: 5px; background-color: #006AF2;box-shadow:8px 6px 15px 0px rgba(0, 97, 222, 0.25); padding: 21px 28px; display: inline-block; text-decoration: none;margin-bottom: 27px;">Reset Password</a>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;"> If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: {{ $reset_password_url }}</p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: 600;color: #000; margin-bottom: 0; margin-top:27px;">Thank you</p>
    <div class="regards" style="color: #000; font-weight: 700; font-size: 16px;margin-bottom: 20px;">Regards,<br> {{ config('app.name') }}</div>

@endsection
