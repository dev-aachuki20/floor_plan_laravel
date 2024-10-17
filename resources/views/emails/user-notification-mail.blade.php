@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Dear, {{ ucwords($userName) }}</h4>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">{!! $message ? nl2br($message) : '' !!}</p>
    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">Access to your FLOORPLAN: {{ config('app.url') }}</p>


    <p style="font-size: 16px; line-height: 25.5px; font-weight: 600;color: #000; margin-bottom: 0; margin-top:27px;">Thank you</p>
    <div class="regards" style="color: #000; font-weight: 700; font-size: 16px;margin-bottom: 20px;">FLOORPLAN Support Team</div>

@endsection
