@extends('emails.layouts.admin')

@section('email-content')
    <h4 style="font-family: 'Barlow', sans-serif; color: #000; font-weight: 700; font-size: 16px;margin-top: 20px;">Hello, {{ ucwords($user->full_name) }}</h4>

    <p style="font-size: 16px; line-height: 25.5px; font-weight: normal; font-family: 'Nunito Sans', sans-serif; color: #000; margin-bottom: 27px;margin-top: 10px;">{{ trans('messages.mail_content.admin_updated_own_user') }}</p>

    
    @php
        $hospitals = $user->getHospitals()->pluck('hospital_name')->toArray();
        $hospitals = implode(', ',$hospitals);

    @endphp
    <ul>

        @if(isset($updatedFields['full_name']) && ($updatedFields['full_name'] == true))
            <li><strong>Full Name:</strong> {{ $user->full_name ?? '' }}</li>
        @endif

        @if(isset($updatedFields['user_email']) && ($updatedFields['user_email'] == true))
            <li><strong>Email:</strong> {{ $user->user_email ?? '' }}</li>
        @endif

        @if(isset($updatedFields['primary_role']) && ($updatedFields['primary_role'] == true))
            <li><strong>Role:</strong> {{ $user->role->role_name ?? '' }}</li>
        @endif

        @if(isset($updatedFields['trust']) && ($updatedFields['trust'] == true))
        <li><strong>Trust:</strong> {{ $user->trusts ? $user->trusts()->value('trust_name') : null }}</li>
        @endif

        @if(isset($updatedFields['hospital']) && ($updatedFields['hospital'] == true))
            <li><strong>Hospital:</strong> {{ $hospitals }}</li>
        @endif

        @if($user->primary_role != config('constant.roles.booker'))

            @if(isset($updatedFields['speciality']) && ($updatedFields['speciality'] == true))
            <li><strong>Speciality:</strong> {{ $user->specialityDetail()->value('speciality_name') }}</li>
            @endif

            @if(isset($updatedFields['sub_speciality']) && ($updatedFields['sub_speciality'] == true))
            <li><strong>Sub Speciality:</strong> {{ $user->subSpecialityDetail()->value('sub_speciality_name') }}</li>
            @endif

        @endif

        @if(isset($updatedFields['password']) && ($updatedFields['password'] == true))
         <li><strong>Your password has been changed</strong>
        @endif

    </ul>


    <p style="font-size: 16px; line-height: 25.5px; font-weight: 600;color: #000; margin-bottom: 0; margin-top:27px;">Thank you</p>
    <div class="regards" style="color: #000; font-weight: 700; font-size: 16px;margin-bottom: 20px;">Regards,<br> {{ config('app.name') }}</div>

@endsection
