@extends('layouts.admin')
@section('title', 'Show Company')

@section('custom_css')

@endsection

@section('main-content')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Show Company</h4>
            </div>
           
        </div>
    </div>

    
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-pills bg-nav-pills nav-justified mb-3">
                        <li class="nav-item">
                            <a href="#about" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0 active">
                                About
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#overview" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                Overview
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#location" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                Location
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#project_information" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                Project Information
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#services_industries" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                Services & Industries
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#portfolio" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                Portfolio
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="#clients" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                Clients
                            </a>
                        </li>

                        
                        <li class="nav-item">
                            <a href="#review" data-bs-toggle="tab" aria-expanded="false" class="nav-link rounded-0">
                                Review
                            </a>
                        </li>

                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane show active" id="about">
                            
                            <div class="row">
                            
                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="first_name" class="form-label">First Name :</label>
                                    <span>{{ isset($user) ? $user->first_name : ''}}</span>
                                </div> 
                                
                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="last_name" class="form-label">Last Name :</label>
                                    <span>{{ isset($user) ? $user->last_name : ''}}</span>

                                </div>

                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="email" class="form-label">Email :</label>
                                    <span>{{ isset($user) ? $user->email : ''}}</span>
                                </div>

                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="phone" class="form-label">Phone Number :</label>
                                    <span>{{ isset($user) ? $user->phone : ''}}</span>
                                </div>


                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="company_name" class="form-label">Company Name :</label>
                                    <span>{{ (isset($user->company) && !empty($user->company->company_name)) ? $user->company->company_name : ''}}</span>
                                </div>

                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="company_website" class="form-label">Company Website :</label>
                                    <span>{{ (isset($user->company) && !empty($user->company->company_website)) ? $user->company->company_website : ''}}</span>

                                </div>

                                
                                <div class="mb-3 col-md-12">
                                    <label for="tagline" class="form-label">Tagline :</label>
                                    <span>{{ (isset($user->company) && !empty($user->company->tagline)) ? $user->company->tagline : ''}}</span>
                                </div>

                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="total_employees" class="form-label">Total Employees :</label>
                                    <span>{{ (isset($user->company) && !empty($user->company->total_employees)) ? $user->company->total_employees : ''}}</span>
                                </div>

                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="founding_year" class="form-label">Founding Year :</label>
                                    <span>{{ (isset($user->company) && !empty($user->company->founding_year)) ? $user->company->founding_year : ''}}</span>
                                </div>

                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="admin_contact_phone" class="form-label">Admin Contact Phone :</label>
                                    <span>{{ (isset($user->company) && !empty($user->company->admin_contact_phone)) ? $user->company->admin_contact_phone : ''}}</span>
                                </div>

                                <div class="mb-3  col-md-6 col-lg-4">
                                    <label for="sales_email" class="form-label">Sales Email : </label>
                                    <span>{{ (isset($user->company) && !empty($user->company->sales_email)) ? $user->company->sales_email : ''}}</span>
                                </div>

                                <div class="mb-3  col-md-6 col-lg-4">
                                    <label for="sales_email" class="form-label">Created At : </label>
                                    <span>{{ (isset($user)) ? dateFormat($user->created_at,'d-m-Y H:i') : ''}}</span>
                                </div>

                                <div class="mb-3  col-md-6 col-lg-4">
                                    <label for="sales_email" class="form-label">Updated At : </label>
                                    <span>{{ (isset($user)) ? dateFormat($user->updated_at,'d-m-Y H:i') : ''}}</span>
                                </div>


                                <div class="mb-3  col-md-6 col-lg-4">
                                    <label class="form-label" for="phone">Company Logo</label>
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
                                </div>
                                        
                            </div>

                        </div>
                        <div class="tab-pane" id="overview">

                            {!! (isset($user->company) && !empty($user->company->description)) ? $user->company->description : '' !!}

                        </div>
                        <div class="tab-pane" id="location">
                                                    
                            @php
                                $locations = isset($user->company) ? $user->company->locations()->get() : null;
                            @endphp

                            @if($locations->count() > 0)
                                @foreach($locations as $key=>$location)
                                

                                @php
                                    $keyId = $key+1;
                                @endphp
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-widgets">

                                            <a data-bs-toggle="collapse" href="#card-collapse{{$keyId}}" role="button" aria-expanded="true" aria-controls="card-collapse{{$keyId}}" class=""><i class="ri-subtract-line"></i></a>
                                            
                                        </div>
                                        <h5 class="card-title mb-0">Location {{$keyId}}</h5>
                                    </div>
                                    <div id="card-collapse{{$keyId}}" class="collapse show" style="">
                                        <div class="card-body">
                                            
                                            <div class="row">
                                            
                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="country" class="form-label">Country : </label>
                                                    <span>{{ $location->country ?? '' }}</span>
                                                </div> 
                                                
                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="city" class="form-label">City :</label>
                                                    <span>{{ $location->city ?? '' }}</span>
                                                </div>

                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="street" class="form-label">Street :</label>
                                                    <span>{{ $location->street ?? '' }}</span>
                                                </div>

                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="total_employees" class="form-label">Total employees :</label>
                                                    <span>{{ $location->total_employees ?? '' }}</span>
                                                </div>

                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="contact_number" class="form-label">Contact Number :</label>
                                                    <span>{{ $location->contact_number ?? '' }}</span>
                                                </div>


                                            </div>

                                        </div>
                                    </div>
                                </div>

                                @endforeach
                            @endif
                        </div>
                        <div class="tab-pane" id="project_information">
                            
                            @php
                                $project =  isset($user->company) ? $user->company->project : null;   
                            @endphp

                            
                            <div class="row">
                            
                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="minimum_project_size" class="form-label">Minimum Project Size : </label>
                                    <span>{{ $project ? $project->minimum_project_size : ''}}</span>
                                </div>

                                <div class="mb-3 col-md-6 col-lg-4">
                                    <label for="average_hourly_rate" class="form-label">Average Hourly Rate : </label>
                                    <span>{{ $project ? $project->average_hourly_rate : ''}}</span>
                                </div>


                            </div>
                        </div>

                        <div class="tab-pane" id="services_industries">
                            
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="fs-15">Services</h6>

                                            @if(isset($services))
                                            @foreach($services as $key=>$service)

                                                @php
                                                $checkedTag = '';
                                                
                                                if(isset($user->company)){
                                                    
                                                    $serviceArr = $user->company->services()->pluck('id')->toArray();
                                                    if(in_array($service->id,$serviceArr)){
                                                        $checkedTag = 'checked';
                                                    }

                                                }
                                                @endphp
                                                
                                                <div class="form-check form-checkbox-success mb-2">
                                                    <input type="checkbox" class="form-check-input readonly-checkbox" name="services[]" id="customCheckcolor-service{{$key}}" value="{{ $service->id }}" {{ $checkedTag }}>
                                                    <label class="form-check-label" for="customCheckcolor-service{{$key}}">{{ ucwords($service->name) }}</label>
                                                </div>

                                            @endforeach

                                            @endif

                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="fs-15">Industries</h6>

                                            @if(isset($industries))
                                            @foreach($industries as $key=>$industry)

                                            @php
                                                $checkedTag = '';
                                                
                                                if(isset($user->company)){
                                                    
                                                    $industryArr = $user->company->industries()->pluck('id')->toArray();
                                                    if(in_array($industry->id,$industryArr)){
                                                        $checkedTag = 'checked';
                                                    }

                                                }
                                            @endphp

                                                <div class="form-check mb-2">
                                                    <input type="checkbox" class="form-check-input readonly-checkbox" name="industries[]" id="customCheckcolor-industry{{$key}}" value="{{ $industry->id }}" {{ $checkedTag }}>
                                                    <label class="form-check-label" for="customCheckcolor-industry{{$key}}">{{ ucwords($industry->name) }}</label>
                                                </div>

                                            @endforeach

                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="tab-pane" id="portfolio">
                            
                            @php
                                $portfolios = isset($user->company) ? $user->company->portfolios()->get() : null;
                            @endphp

                            @if($portfolios->count() > 0)
                                @foreach($portfolios as $key=>$portfolio)
                                @php
                                    $keyId = $key+1;
                                @endphp
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-widgets">

                                            <a data-bs-toggle="collapse" href="#card-collapse{{$keyId}}" role="button" aria-expanded="true" aria-controls="card-collapse{{$keyId}}" class=""><i class="ri-subtract-line"></i></a>
                                            
                                        </div>
                                        <h5 class="card-title mb-0">Portfolio {{$keyId}}</h5>
                                    </div>
                                    <div id="card-collapse{{$keyId}}" class="collapse show" style="">
                                        <div class="card-body">
                                            
                                            <div class="row">
                                            
                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="client_name" class="form-label">Client Name : </label>
                                                    <span>{{ $portfolio->client_name ?? '' }}</span>
                                                </div> 
                                                
                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="client_website" class="form-label">Client Website :</label>
                                                    <span>{{ $portfolio->client_website ?? '' }}</span>
                                                </div>

                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="project_title" class="form-label">Project Title :</label>
                                                    <span>{{ $portfolio->project_title ?? '' }}</span>
                                                </div>

                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="project_industry" class="form-label">Project Industry :</label>
                                                    <span>{{ $portfolio->project_industry ?? '' }}</span>
                                                </div>

                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="timeline" class="form-label">Timeline :</label>
                                                    <span>{{ $portfolio->timeline ?? '' }}</span>
                                                </div>

                                                <div class="mb-3 col-md-6 col-lg-4">
                                                    <label for="project_cost" class="form-label">Project Cost :</label>
                                                    <span>{{ $portfolio->project_cost ?? '' }}</span>
                                                </div>

                                                <div class="mb-3 col-md-12">
                                                    <label for="project_description" class="form-label">Project Description :</label>
                                                    <span>{{ $portfolio->project_description ?? '' }}</span>
                                                </div>

                                                <div class="mb-3  col-md-6 col-lg-4">
                                                    <label class="form-label" for="phone">Screenshot :</label>
                                                    <div class="img-prevarea m-1">
                                                        @php
                                                        $logoUrl = asset(config('constant.default.no_image'));    
                                    
                                                        if(isset($portfolio)){
                                    
                                                            if($portfolio->screenshot_url){
                                                                $logoUrl = $portfolio->screenshot_url;
                                                            }
                                                        }
                                                        @endphp
                                    
                                                        <img src="{{ $logoUrl  }}" width="100px" height="100px" >
                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                </div>
                                
                                @endforeach
                            @endif
                        </div>

                        <div class="tab-pane" id="clients">
                            
                            @php
                                $clients = isset($user->company) ? $user->company->clients()->get() : null;
                            @endphp

                            @if($clients->count() > 0)
                            <div class="row">

                                @foreach($clients as $key=>$client)

                                    <div class="col-md-2">
                                        @php
                                            $logoUrl = asset(config('constant.default.no_image'));    

                                            if(isset($client)){

                                                if($client->client_profile_image_url){
                                                    $logoUrl = $client->client_profile_image_url;
                                                }
                                            }
                                        @endphp
                                        <img src="{{$logoUrl}}" alt="image" class="img-fluid avatar-xl rounded">
                                        <p class="mb-0">
                                            <span>{{ $client->name ?? '' }}</span>
                                        </p>
                                    </div>

                                @endforeach
                            </div>

                            @endif
                        </div>

                        <div class="tab-pane" id="review">
                            <p>Review</p>
                        </div>

                    </div>
                </div> <!-- end card-body -->
            </div> <!-- end card-->
        </div> <!-- end col -->
    
    </div>
    <!-- end row -->
   


@endsection

@section('custom_js')


@endsection