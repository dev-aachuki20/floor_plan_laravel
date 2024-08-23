@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;margin-top: 0;">Hello, {{ $user->full_name ?? ''}}</h4>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px;">I hope this mail finds you well.</p>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px;">Please find below the scheduled sessions for hospital rooms</p>

    <h2>Hospital: {{ $hospitalName }}</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Room</th>
                <th>Time Slot</th>
                <th>Speciality</th>
            </tr>
        </thead>
        <tbody>
            @foreach($all_session as $session)
                <tr>
                    <td>{{ dateFormat($session->week_day_date,'d-m-Y') }}</td>
                    <td> {{ $session->roomDetail->room_name ?? '' }}</td>
                    <td>{{ $session->time_slot ?? '' }}</td>
                    <td>{{ $session->specialityDetail ? $session->specialityDetail->speciality_name : ''  }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px; margin-top:27px;">We appreciate your cooperation and understanding. Let’s work together to ensure the best possible care for our patients.</p>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px; margin-top:27px;">Thank you</p>

    <div class="regards" style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;">Regards,<br> {{ config('app.name') }}</div>

@endsection
