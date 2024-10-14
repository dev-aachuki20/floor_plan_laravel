@extends('emails.layouts.admin')

@section('email-content')

    <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Dear {{ $name ?? "" }}</h4>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">For security reasons, you need to re-enable your Google Authenticator for your account. Please use the QR code below to complete the setup.</p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 10px;margin-top: 10px;"><strong> Instructions to Set Up Google Authenticator:</strong></p>
    <ul style="list-style: none;padding: 0;margin: 0 0 25px;">
        <li style="margin-bottom: 5px;color: #000;">1. Download or open the Google Authenticator app on your mobile device.</li>
        <li style="margin-bottom: 5px;color: #000;">2. Tap the “+” icon to add a new account.</li>
        <li style="margin-bottom: 5px;color: #000;">3. Select “Scan QR code.”</li>
        <li style="color: #000;">4. Point your camera at the QR code provided below to link your account.</li>
    </ul>

    
    @if($qrCodeImageUrl)

        <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;"><strong>QR Code:</strong></p>

        <div style="text-align: center;"><img src="{{ $qrCodeImageUrl }}" style="max-width: 250px;width: 100%;"/></div>

    @endif

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">If you have any questions, feel free to reach out to us.</p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: 600;color: #000; margin-bottom: 0; margin-top:27px;">Thank you for helping us keep your account secure.</p>
    <div class="regards" style="color: #000; font-weight: 700; font-size: 16px;margin-bottom: 20px;">Regards,<br> {{ config('app.name') }}</div>

@endsection
