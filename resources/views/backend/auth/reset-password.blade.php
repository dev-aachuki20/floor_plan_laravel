@extends('layouts.admin_auth')
@section('title', trans('global.forgot_password_title'))
@section('main-content')

<div class="row">
    <div class="col">
        <a href="#" title="logo" class="header-logo">
            <img src="{{ asset('backend/images/hipl-logo.png') }}" alt="logo">
        </a>
    </div>
</div>
<div class="l_content">
    <div class="container px-0">
        <div class="row items-center">
            <div class="col-lg-7 d-none d-lg-block">
                <img src="{{ asset('backend/images/reset-pass.webp') }}" alt="" class="img-fluid">
            </div>
            <div class="col-lg-5">
                <div class="log-register-block">
                    <h2 class="text-center">Reset Your Password</h2>
                    <p class="text-center">Reset your password to continue</p>
                    <form method="POST" action="{{route("admin.reset-new-password")}}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter password" tabindex="1" value="{{ old('password') }}" autofocus>
                                <div class="input-group-text toggle-password" data-password="false">
                                    <span class="password-eye"></span>
                                </div>
                            </div>

                            @error('password')
                            <span class="invalid-feedback d-block">
                                {{ $message }}
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password-confirm" class="form-label">Confirm Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password-confirm" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="Enter confirm password" tabindex="2" value="{{ old('password_confirmation') }}">
                                <div class="input-group-text toggle-password" data-password="false">
                                    <span class="password-eye"></span>
                                </div>
                            </div>

                            @error('password_confirmation')
                            <span class="invalid-feedback d-block">
                                {{ $message }}
                            </span>
                            @enderror
                        </div>
                        <div class="btn-block">
                            <button class="btn btn-soft-primary w-100" type="submit">@lang('global.submit')</button>
                        </div>
                    </form>
                    <p class="bottom-para">Already have an Account? <a href="{{ route('admin.login') }}" class="text-decoration-underline">@lang('global.login')</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('custom_js')

<script>

    // Password field hide/show functiolity
    $(document).on('click', '.toggle-password', function () {        
          var passwordInput = $(this).prev('input');  
          console.log(passwordInput);      
          if (passwordInput.attr('type') === 'password') {
              passwordInput.attr('type', 'text');
              $(this).addClass('show-password');
          } else {
              passwordInput.attr('type', 'password');
              $(this).removeClass('show-password');
          }
    });

</script>
@endsection