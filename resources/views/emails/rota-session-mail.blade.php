@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Hello, {{ $user->full_name ?? ''}}</h4>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 0px;">I hope this mail finds you well.</p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">We are writing to inform you about the upcoming session at {{ $rota_session_detail->roomDetail->hospital->hospital_name ?? ''}} scheduled for {{ dateFormat($rota_session_detail->week_day_date,'d-m-Y').'('.$rota_session_detail->time_slot.')'  }}. Please find the important details below:</p>

    
    <ul>
        <li><strong>Hospital:</strong> {{ $rota_session_detail->roomDetail->hospital->hospital_name ?? '' }}</li>
        <li><strong>Room Name:</strong> {{ $rota_session_detail->roomDetail->room_name ?? '' }}</li>
        <li><strong>Time Slot:</strong> {{ $rota_session_detail->time_slot ?? '' }}</li>
        <li><strong>Date:</strong> {{ dateFormat($rota_session_detail->week_day_date,'d-m-Y') }}</li>
    </ul>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px; margin-top:27px;">We appreciate your cooperation and understanding. Let’s work together to ensure the best possible care for our patients.</p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: 600;color: #000; margin-bottom: 0; margin-top:27px;">Thank you</p>
    <div class="regards" style="color: #000; font-weight: 700; font-size: 16px;margin-bottom: 20px;">Regards,<br> {{ config('app.name') }}</div>

@endsection
