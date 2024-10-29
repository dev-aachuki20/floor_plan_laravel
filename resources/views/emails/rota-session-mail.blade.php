@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Dear, {{ $user->full_name ?? ''}}</h4>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal;color: #000; margin-bottom: 0px;">Please log in to your account to approve the following session request:</p>

    <!-- <p style="font-size: 16px; line-height: 25.5px; font-weight: normal;color: #000; margin-bottom: 27px;margin-top: 10px;">We are writing to inform you about the upcoming session at {{ $rota_session_detail->roomDetail->hospital->hospital_name ?? ''}} scheduled for {{ dateFormat($rota_session_detail->week_day_date,'d-m-Y').'('.$rota_session_detail->time_slot.')'  }}. Please find the important details below:</p> -->

    
    <ul style="padding: 0;margin: 0;list-style: none;">
        <li style="display: block;color: #000;"><strong>Hospital:</strong> {{ $rota_session_detail->roomDetail->hospital->hospital_name ?? '' }}</li>
        <li style="display: block;color: #000;"><strong>Room Name:</strong> {{ $rota_session_detail->roomDetail->room_name ?? '' }}</li>
        <li style="display: block;color: #000;"><strong>Speciality:</strong> {{ $rota_session_detail->specialityDetail->speciality_name ?? '' }}</li>
        <li style="display: block;color: #000;"><strong>Time Slot:</strong> {{ $rota_session_detail->time_slot ?? '' }}</li>
        <li style="display: block;color: #000;"><strong>Date:</strong> {{ dateFormat($rota_session_detail->week_day_date,'d-m-Y') }}</li>
    </ul>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal;color: #000; margin-bottom: 27px;margin-top: 10px;">Access to your FLOORPLAN: <a href="{{ config('app.site_url') }}">{{ config('app.site_url') }}</a></p>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: 600;color: #000; margin-bottom: 0; margin-top:27px;">Thank you</p>
    <div class="regards" style="color: #000; font-weight: 700; font-size: 16px;margin-bottom: 20px;">FLOORPLAN Support Team</div>

@endsection
