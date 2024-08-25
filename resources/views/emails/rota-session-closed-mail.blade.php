@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;margin-top: 0;">Hello, {{ $user->full_name ?? ''}}</h4>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px;">I hope this mail finds you well.</p>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px;">We are writing to inform you that the session scheduled at {{ $rota_session_detail->roomDetail->hospital->hospital_name ?? ''}} on {{ dateFormat($rota_session_detail->week_day_date,'d-m-Y') }} during the {{ $rota_session_detail->time_slot ?? '' }} time slot has been closed. Please find the session details below:</p>

    <ul>
        <li><strong>Hospital:</strong> {{ $rota_session_detail->roomDetail->hospital->hospital_name ?? '' }}</li>
        <li><strong>Room Name:</strong> {{ $rota_session_detail->roomDetail->room_name ?? '' }}</li>
        <li><strong>Time Slot:</strong> {{ $rota_session_detail->time_slot ?? '' }}</li>
        <li><strong>Date:</strong> {{ dateFormat($rota_session_detail->week_day_date,'d-m-Y') }}</li>
    </ul>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px; margin-top:27px;">Thank you</p>

    <div class="regards" style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;">Regards,<br> {{ config('app.name') }}</div>
@endsection
