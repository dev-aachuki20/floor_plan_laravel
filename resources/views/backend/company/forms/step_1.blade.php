<div id="step1Section" class="tab-pane fade show active">

    <form id="stepForm1" method="post"  class="step-form" data-step-form-no="1" enctype="multipart/form-data">
        @csrf

        <div class="row">
           
            <div class="mb-3 col-md-6 col-lg-4">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter first name" value="{{ isset($user) ? $user->first_name : ''}}">
            </div> 
            
            <div class="mb-3 col-md-6 col-lg-4">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Enter last name" value="{{ isset($user) ? $user->last_name : ''}}">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="email" class="form-label">Email <span class="required"> *</span></label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" value="{{ isset($user) ? $user->email : ''}}">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter phone number" value="{{ isset($user) ? $user->phone : ''}}">
            </div>

            {{-- <div class="mb-3 col-md-6 col-lg-4">
                <label for="password" class="form-label">Password <span class="required"> *</span></label>
                <div class="input-group input-group-merge">
                    <input type="password" id="password"  name="password" class="form-control" placeholder="Enter password" autocomplete="off">
                    <div class="input-group-text toggle-password" data-password="false">
                        <span class="password-eye"></span>
                    </div>
                </div>
            </div> --}}

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="company_name" class="form-label">Company Name <span class="required"> *</span></label>
                <input type="text" id="company_name" name="company_name" class="form-control" placeholder="Enter company name" value="{{ (isset($user->company) && !empty($user->company->company_name)) ? $user->company->company_name : ''}}">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="company_website" class="form-label">Company Website</label>
                <input type="text" id="company_website" name="company_website" class="form-control" placeholder="Enter company website" value="{{ (isset($user->company) && !empty($user->company->company_website)) ? $user->company->company_website : ''}}">
            </div>

            
            <div class="mb-3 col-md-12">
                <label for="tagline" class="form-label">Tagline</label>
                <input type="text" id="tagline" name="tagline" class="form-control" placeholder="Enter tagline" value="{{ (isset($user->company) && !empty($user->company->tagline)) ? $user->company->tagline : ''}}">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="total_employees" class="form-label">Total Employees</label>
                <select class="form-control select2" data-toggle="select2" id="total_employees" name="total_employees">
                    <option value="">Select total employees</option>
                    @if(config('constant.total_employees'))
                     @foreach(config('constant.total_employees') as $val)
                        <option value="{{ $val }}" {{ (isset($user->company) && ($user->company->total_employees == $val)) ? 'selected' : ''}}> {{ $val }}</option>
                     @endforeach
                    @endif
                </select>
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="founding_year" class="form-label">Founding Year</label>
                <input type="text" id="founding_year" name="founding_year" class="form-control" placeholder="Select Year" value="{{ (isset($user->company) && !empty($user->company->founding_year)) ? $user->company->founding_year : ''}}">
            </div>

            <div class="mb-3 col-md-6 col-lg-4">
                <label for="admin_contact_phone" class="form-label">Admin Contact Phone</label>
                <input type="text" id="admin_contact_phone" name="admin_contact_phone" class="form-control" placeholder="Enter admin contact phone" value="{{ (isset($user->company) && !empty($user->company->admin_contact_phone)) ? $user->company->admin_contact_phone : ''}}">
            </div>

            <div class="mb-3  col-md-6 col-lg-4">
                <label for="sales_email" class="form-label">Sales Email</label>
                <input type="email" id="sales_email" name="sales_email" class="form-control" placeholder="Enter sales email" value="{{ (isset($user->company) && !empty($user->company->sales_email)) ? $user->company->sales_email : ''}}">
            </div>


            <div class="mb-3  col-md-6 col-lg-4">
                <label class="form-label" for="phone">Company Logo</label>
                {{-- <input type="file" id="image-input" name="company_logo" class="form-control fileInputBoth" accept="image/*">
                
                <div class="img-prevarea m-1">
                    @php
                     $logoUrl = asset(config('constant.default.no_image'));    

                     if(isset($user->company)){

                        if($user->company->company_logo_url){
                            $logoUrl = $user->company->company_logo_url;
                        }
                     }
                    @endphp

                    <img src="{{ $logoUrl  }}" width="100px" height="100px" >
                </div> 
                --}}

                @php
                $logoUrl ='';    

                if(isset($user->company)){

                   if($user->company->company_logo_url){
                       $logoUrl = $user->company->company_logo_url;
                   }
                }
               @endphp
                <input name="company_logo" type="file" class="dropify" id="company-logo" data-height="100"  data-default-file="{{ $logoUrl }}"  data-show-loader="true" data-errors-position="outside" data-allowed-file-extensions="jpeg png jpg" data-min-file-size-preview="1M" data-max-file-size-preview="3M"  accept="image/jpeg, image/png, image/jpg" />
            </div>
                    
        </div>
        
        <ul class="list-inline wizard mb-0">
            <li class="next list-inline-item float-end">
                <button type="submit"  class="btn btn-success submitBtn">@lang('global.save')<i class="ri-arrow-right-line ms-1"></i></button>
            </li>
        </ul>

    </form>
</div>