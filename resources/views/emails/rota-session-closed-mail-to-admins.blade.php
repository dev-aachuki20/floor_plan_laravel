@extends('emails.layouts.admin')

@section('email-content')
        <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Hello, {{ $user->full_name ?? ''}}</h4>
        <!-- <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 0px;">I hope this mail finds you well.</p> -->
        <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Please note that this scheduled session has been closed.</p>
        <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Hospital: <span style="color: #295597">{{ $hospitalName }}</span></h4>
        <table style="width: 100%;border-color: #ecf1f5;" border="1" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="padding: 8px;text-align: left;">Date</th>
                    <th style="padding: 8px;text-align: left;">Room</th>
                    <th style="padding: 8px;text-align: left;">Time Slot</th>
                    <th style="padding: 8px;text-align: left;">Speciality</th>
                </tr>
            </thead>
            <tbody>
                @foreach($all_session as $session)
                    <tr>
                        <td style="padding: 8px;">{{ dateFormat($session->week_day_date,'d-m-Y') }}</td>
                        <td style="padding: 8px;"> {{ $session->roomDetail->room_name ?? '' }}</td>
                        <td style="padding: 8px;">{{ $session->time_slot ?? '' }}</td>
                        <td style="padding: 8px;">{{ $session->specialityDetail ? $session->specialityDetail->speciality_name : ''  }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="font-size: 16px; line-height: 25.5px; font-weight: 600;color: #000; margin-bottom: 0; margin-top:27px;">Thank you</p>
        <div class="regards" style="color: #000; font-weight: 700; font-size: 16px;margin-bottom: 20px;">Regards,<br> {{ config('app.name') }}</div>
@endsection
