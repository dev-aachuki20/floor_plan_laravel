@extends('layouts.admin_auth')
@section('title', trans('global.login'))
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
                <img src="{{ asset('backend/images/signin-up-img.webp') }}" alt="" class="img-fluid">
            </div>
            <div class="col-lg-5">
                <div class="log-register-block">
                    <h2 class="text-center">@lang('global.login')</h2>
                    <p class="text-center">Welcome to HIPL Product!</p>
                    <form method="POST" action="{{route("authenticate")}}">
                        @csrf
                        <div class="form-group">
                            <label for="emailaddress" class="form-label">@lang('global.login_email')</label>
                            <input class="form-control" type="email" name="email" id="email" placeholder="Enter your email" value="{{ old('email') }}" tabindex="1"   autofocus>
                            @error('email')
                            <span class="invalid-feedback d-block">
                                {{ $message }}
                            </span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">@lang('global.login_password')</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" tabindex="2">
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
                        <div class="form-group d-flex justify-content-between flex-wrap">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkbox-signin">
                                <label class="form-check-label" for="checkbox-signin">@lang('global.remember_me')</label>
                            </div>
                            <a href="{{ route('admin.forgot.password') }}" class="forgot-text">@lang('global.forgot_password')</a>
                        </div>
                        <div class="text-start btn-block">
                            <button class="btn btn-soft-primary w-100" type="submit">
                                @lang('global.login')
                            </button>
                        </div>
                    </form>
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