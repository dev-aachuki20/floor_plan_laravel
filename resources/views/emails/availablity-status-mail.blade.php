@extends('emails.layouts.admin')

@section('email-content')

    @php
      $staffLabel = 'Confirmed by';
      if($notification_type == 'session_cancelled'){
        $staffLabel = 'Cancelled by';
      }
    @endphp

    <h4 style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;margin-top: 0;">Hello, {{ $user->full_name ?? ''}}</h4>


    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px;">We would like to inform you that the scheduled session on {{ dateFormat($rota_session_detail->week_day_date,'d-m-Y') }} ({{ $rota_session_detail->time_slot ?? '' }}) in {{ $rota_session_detail->roomDetail->room_name ?? '' }} has been {{ strtolower($staffLabel) }} {{ $staffMember->full_name ?? '' }}.
    </p>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px; margin-top:27px;">Session Details:</p>
  
    <ul>
        <li><strong>Hospital:</strong> {{ $rota_session_detail->roomDetail->hospital->hospital_name ?? '' }}</li>
        <li><strong>Room Name:</strong> {{ $rota_session_detail->roomDetail->room_name ?? '' }}</li>
        <li><strong>Time Slot:</strong> {{ $rota_session_detail->time_slot ?? '' }}</li>
        <li><strong>Date:</strong> {{ dateFormat($rota_session_detail->week_day_date,'d-m-Y') }}</li>
        <li><strong>{{ $staffLabel }}:</strong> {{ $staffMember->full_name ?? '' }}</li>
    </ul>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px; margin-top:27px;">Please review the details to ensure everything is correctly set up for the session.</p>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px; margin-top:27px;">Thank you</p>

    <div class="regards" style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;">Regards,<br> {{ config('app.name') }}</div>

@endsection
