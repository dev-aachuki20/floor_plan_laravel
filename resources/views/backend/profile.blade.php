@extends('layouts.admin')
@section('title', trans('global.profile'))

@section('custom_css')
@endsection

@section('main-content')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Profile</h4>
            </div>
        </div>
    </div>

    <!-- start page title -->
    <div class="row">
        <div class="col-sm-4">
            <div class="card">
                <div class="card-body padding-30">
                    <div class="profile-image">
                        @if($user->profile_image_url)
                            <img src="{{ $user->profile_image_url }}" alt="user-image"  class="avatar-lg rounded-circle">
                        @else
                            <img src="{{ asset(config('constant.default.user_icon')) }}" alt="user-image"  class="avatar-lg rounded-circle">
                        @endif
                    </div>
                    <div class="profile_details">
                        <h4 class="ellipsis">{{ ucwords($user->name) }}</h4>
                        <ul>
                            <li>
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="13" viewBox="0 0 18 13" fill="none">
                                      <path d="M16.418 0H1.58203C0.711492 0 0 0.708363 0 1.58203V11.0742C0 11.9482 0.711949 12.6562 1.58203 12.6562H16.418C17.2885 12.6562 18 11.9479 18 11.0742V1.58203C18 0.708152 17.2882 0 16.418 0ZM16.175 1.05469L9.40866 7.84315C9.2025 8.04994 8.79761 8.05008 8.59134 7.84315L1.82496 1.05469H16.175ZM1.05469 10.8803V1.77592L5.59213 6.32812L1.05469 10.8803ZM1.82496 11.6016L6.3367 7.07512L7.84438 8.58772C8.46221 9.20756 9.53803 9.20732 10.1557 8.58772L11.6633 7.07516L16.175 11.6016H1.82496ZM16.9453 10.8803L12.4079 6.32812L16.9453 1.77592V10.8803Z" fill="#4f4f4f"></path>
                                    </svg>
                                </span> {{ $user->email }}
                            </li>
                            @if($user->phone)
                            <li>
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                      <path d="M17.5173 13.2285L14.6785 10.3998C14.0343 9.75398 12.9884 9.75274 12.3426 10.397L12.3398 10.3998L10.7133 12.0263C10.6537 12.086 10.575 12.1228 10.4909 12.1302C10.4068 12.1376 10.3228 12.1151 10.2537 12.0667C9.36347 11.4446 8.53013 10.7447 7.76344 9.97548C7.07678 9.29038 6.4451 8.55229 5.87426 7.76807C5.82419 7.69989 5.80023 7.61603 5.80671 7.53169C5.8132 7.44736 5.8497 7.36814 5.9096 7.30842L7.57651 5.64151C8.22007 4.9967 8.22007 3.95263 7.57651 3.30783L4.73772 0.469042C4.08357 -0.156347 3.05317 -0.156347 2.39901 0.469042L1.49991 1.36815C0.142048 2.71073 -0.342695 4.70428 0.247178 6.52043C0.68736 7.84916 1.30953 9.11049 2.09594 10.2684C2.8039 11.33 3.61613 12.3183 4.52054 13.2184C5.50381 14.2087 6.593 15.0878 7.76848 15.84C9.06067 16.6832 10.4795 17.3142 11.9711 17.7089C12.3545 17.8035 12.7481 17.851 13.143 17.8504C14.4968 17.8421 15.7921 17.2971 16.7445 16.335L17.5174 15.5621C18.1609 14.9174 18.1609 13.8733 17.5173 13.2285ZM16.802 14.8732L16.8 14.8752L16.805 14.8601L16.0322 15.6329C15.5439 16.1273 14.9365 16.4875 14.2684 16.6786C13.6004 16.8697 12.8943 16.8854 12.2185 16.724C10.8337 16.3533 9.51714 15.7633 8.31893 14.9763C7.20569 14.2648 6.17408 13.4331 5.24271 12.4961C4.38578 11.6454 3.61586 10.7114 2.94437 9.70781C2.2099 8.628 1.62853 7.45166 1.21684 6.21232C0.985441 5.49847 0.957387 4.73429 1.13582 4.0054C1.31426 3.27651 1.69213 2.6117 2.22711 2.08546L3.12621 1.18636C3.37619 0.935257 3.78239 0.934369 4.03343 1.18435L4.03544 1.18636L6.87423 4.02515C7.12533 4.27512 7.12622 4.68132 6.87624 4.93236L6.87423 4.93437L5.20731 6.60129C4.72902 7.07437 4.66888 7.82614 5.0659 8.36925C5.66881 9.19665 6.33596 9.97527 7.06116 10.6979C7.86971 11.5099 8.7487 12.2486 9.68779 12.9053C10.2304 13.2838 10.9661 13.22 11.4355 12.7538L13.0468 11.1172C13.2968 10.8661 13.703 10.8652 13.9541 11.1152L13.9561 11.1172L16.7999 13.9661C17.0511 14.216 17.052 14.6221 16.802 14.8732Z" fill="#4f4f4f"></path>
                                    </svg>
                                </span> {{ $user->phone }}
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-sm-8">
            <div class="card p-0">
                <div class="card-body p-0">
                    <div class="profile-content">
                        <ul class="nav nav-underline nav-justified gap-0">
        
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab"
                                    data-bs-target="#edit-profile" type="button" role="tab"
                                    aria-controls="home" aria-selected="true"
                                    href="#edit-profile">Edit Profile</a>
                            </li>

                            
                            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab"
                                    data-bs-target="#change-password" type="button" role="tab"
                                    aria-controls="home" aria-selected="true"
                                    href="#edit-profile">Change Password</a>
                            </li>
                        </ul>

                        <div class="tab-content m-0 p-4">
                            
                            <!-- profile -->
                            <div id="edit-profile" class="tab-pane active">
                                <div class="user-profile-content">
                                    <form id="profile-form" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row row-cols-sm-2 row-cols-1">
                                            <div class="mb-2">
                                                <label class="form-label" for="name">Name<span class="required"> *</span></label>
                                                <input type="text" name="name" value="{{ $user->name }}" id="name"
                                                    class="form-control" placeholder="Enter your name">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="phone">Phone Number</label>
                                                <input type="text" value="{{ $user->phone }}"
                                                    id="phone" class="form-control" placeholder="Enter your phone number">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label" for="phone">Profile Image<span class="required"> *</span></label>
                                                <input type="file" id="image-input" name="profile_image" class="form-control fileInputBoth" accept="image/*">
                                                
                                                <div class="img-prevarea m-1">
                                                    <img src="{{ $user->profile_image_url ? $user->profile_image_url : asset(config('constant.default.user_icon')) }}" width="100px" height="100px" >
                                                </div>
                                            </div>
                                            

                                        </div>
                                        <button class="btn btn-primary submitBtn" type="submit"><i
                                                class="ri-save-line me-1 fs-16 lh-1"></i> Update</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Change password -->
                            <div id="change-password" class="tab-pane">
                                <div class="user-profile-content">
                                    <form id="change-password-form">
                                        @csrf
                                        <div class="row row-cols-sm-2 row-cols-1">
                                            <div class="mb-2">
                                                <label class="form-label" for="current_password">Current Password<span class="required"> *</span></label>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password" value="{{ old('current_password') }}" tabindex="1" autofocus>
                                                    <div class="input-group-text" data-password="false">
                                                        <span class="password-eye"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label" for="new_password">New Password<span class="required"> *</span></label>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" id="new_password" name="password" class="form-control" placeholder="Enter new password" value="{{ old('password') }}" tabindex="2">
                                                    <div class="input-group-text" data-password="false">
                                                        <span class="password-eye"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label" for="password_confirmation">New Password Confirmation<span class="required"> *</span></label>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Enter new password confirmation" value="{{ old('password_confirmation') }}" tabindex="3">
                                                    <div class="input-group-text" data-password="false">
                                                        <span class="password-eye"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        <button class="btn btn-primary submitBtn" type="submit"><i
                                                class="ri-save-line me-1 fs-16 lh-1"></i> Update</button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
    <!-- end row -->

@endsection

@section('custom_js')

<script>
    $(document).on('submit', '#profile-form', function(e){
        e.preventDefault();
        $(".submitBtn").attr('disabled', true);

        $('.validation-error-block').remove();

        var formData = new FormData(this);

        $.ajax({
            type: 'post',
            url: "{{ route('admin.update.profile') }}",
            dataType: 'json',
            contentType: false,
            processData: false,
            data: formData,
            success: function (response) {
                $(".submitBtn").attr('disabled', false);
                if(response.success) {
                    toasterAlert('success',response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 20000000);
                }
            },
            error: function (response) {
                // console.log(response);
                $(".submitBtn").attr('disabled', false);
                if(response.responseJSON.error_type == 'something_error'){
                    toasterAlert('error',response.responseJSON.error);
                } else {
                    var errorLabelTitle = '';
                    $.each(response.responseJSON.errors, function (key, item) {
                        errorLabelTitle = '<span class="validation-error-block">'+item[0]+'</sapn>';

                        $(errorLabelTitle).insertAfter("input[name='"+key+"']");

                    });
                }
            },
            complete: function(res){
                $(".submitBtn").attr('disabled', false);
            }
        });
    });

    // Image show in profile page
    $(document).on('change', ".fileInputBoth",function(e){
        var files = e.target.files;
        for (var i = 0; i < files.length; i++) {
            var reader2 = new FileReader();
            reader2.onload = function(e) {
                $('.img-prevarea img').attr('src', e.target.result);
            };
            reader2.readAsDataURL(files[i]);
        }
    });

    $(document).on('submit', '#change-password-form', function(e){
        e.preventDefault();
        $(".submitBtn").attr('disabled', true);

        $('.validation-error-block').remove();

        var formData = new FormData(this);

        $.ajax({
            type: 'post',
            url: "{{ route('admin.update.change.password') }}",
            dataType: 'json',
            contentType: false,
            processData: false,
            data: formData,
            success: function (response) {
                $(".submitBtn").attr('disabled', false);
                if(response.success) {
                    $('#change-password-form')[0].reset();
                    toasterAlert('success',response.message);
                }
            },
            error: function (response) {
                $(".submitBtn").attr('disabled', false);
                // console.log(response);
                if(response.responseJSON.error_type == 'something_error'){
                    toasterAlert('error',response.responseJSON.error);
                } else {                    
                    var errorLabelTitle = '';
                    $.each(response.responseJSON.errors, function (key, item) {
                        errorLabelTitle = '<span class="validation-error-block">'+item[0]+'</sapn>';
                        
                        var elementItem = $("input[name='"+key+"']").parent();    
                        $(errorLabelTitle).insertAfter(elementItem);
                    });
                }
            },
            complete: function(res){
                $(".submitBtn").attr('disabled', false);
            }
        });
    });
</script>

@endsection