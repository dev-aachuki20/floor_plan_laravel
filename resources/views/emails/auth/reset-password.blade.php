@extends('emails.layouts.admin')

@section('email-content')

    <h4 style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;margin-top: 0;">Dear {{ $name ?? "" }}</h4>
    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px;">You are receiving this email because we received a password reset request for your account</p>
    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px;">Please click on the link below to reset your password and get access to your account :</p>

    <a href="{{ $reset_password_url }}" style="font-family: 'Barlow', sans-serif; color:#fff; text-transform: uppercase; font-size:18px; line-height: 13px; border-radius: 5px; background-color: #006AF2;box-shadow:8px 6px 15px 0px rgba(0, 97, 222, 0.25); padding: 21px 28px; display: inline-block; text-decoration: none;margin-bottom: 27px;">Reset Password</a>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px;"> If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: {{ $reset_password_url }}</p>

    <div class="regards" style="font-family: 'Barlow', sans-serif; color: #464B70; line-height: 10.5px; font-weight: 700; font-size: 18px;">Regards,<br><br><br> {{ config('app.name') }}</div>

@endsection
