@extends('emails.layouts.admin')

 @section('email-content')
		<p class="mail-title">
			Hello {{ ucwords($userName) }},
		</p>
		<div class="mail-desc">
            <p style="margin-bottom: 0;font-weight: normal;">{!! $message ? nl2br($message) : '' !!}</p>
		</div>
@endsection