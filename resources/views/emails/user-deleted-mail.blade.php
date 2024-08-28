@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Hello, {{ ucwords($user->full_name) }}</h4>
   
    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">
        {{ trans('messages.mail_content.user_deleted_by_admin',['user_name'=>$user->full_name,'user_email'=>$user->user_email,'admin_name'=>$authUser->full_name]) }}
    </p>


    <p style="font-size: 16px; line-height: 25.5px; font-weight: 600;color: #000; margin-bottom: 0; margin-top:27px;">Thank you</p>
    <div class="regards" style="color: #000; font-weight: 700; font-size: 16px;margin-bottom: 20px;">Regards,<br> {{ config('app.name') }}</div>

@endsection
