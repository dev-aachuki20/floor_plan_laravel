@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;margin-top: 0;">Reminder: Unconfirmed Sessions</h4>

    <h4 style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;margin-top: 0;">Dear {{ $user->full_name }},</h4>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px;">This is a reminder that the following sessions have not been confirmed for more than a week:</p>

    <table>
        <thead>
            <tr>
                <th>Session ID</th>
                <th>Session Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sessions as $session)
                <tr>
                    <td>{{ $session->id }}</td>
                    <td>{{ $session->week_day_date->format('Y-m-d') }}</td>
                    <td>Pending</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px; margin-top:27px;">Please review these sessions and confirm them as soon as possible.</p>

    <p style="font-size: 18px; line-height: 25.5px; font-weight: 600; font-family: 'Nunito Sans', sans-serif; color: #464B70; margin-bottom: 27px; margin-top:27px;">Thank you</p>

    <div class="regards" style="font-family: 'Barlow', sans-serif; color: #464B70; font-weight: 700; font-size: 18px;">Regards,<br> {{ config('app.name') }}</div>
@endsection
