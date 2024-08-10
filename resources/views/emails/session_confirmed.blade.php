@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;margin-top: 0;">Dear Admin,</h4>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px;">The following sessions have been confirmed by {{ $user->full_name }}:</p>

    <ul>
        @foreach ($sessionIds as $sessionId)
            @php
                $session = \App\Models\RotaSession::find($sessionId);
            @endphp
            <li>
                @if ($session)
                    - **Session ID**: {{ $session->id }}
                    - **Hospital Name**: {{ $session->roomDetail->hospital->hospital_name ?? '' }}
                    - **Speciality Name**: {{ $session->specialityDetail->name ?? '' }}
                    - **Room Name**: {{ $session->roomDetail->room_name ?? '' }}
                    - **Time Slot**: {{ $session->time_slot ?? '' }}
                    - **Date**: {{ dateFormat($session->week_day_date, 'd-m-Y') }}
                @endif
            </li>
        @endforeach
    </ul>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px; margin-top:27px;">Thank you</p>

    <div class="regards" style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;">Regards,<br> {{ config('app.name') }}</div>
@endsection
