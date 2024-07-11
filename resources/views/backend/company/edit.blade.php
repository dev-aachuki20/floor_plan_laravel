@extends('layouts.admin')
@section('title', 'Edit Company')

@section('custom_css')

 <!-- Select2 css -->
 <link href="{{ asset('backend/vendor/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />

<!-- Bootstrap Datepicker css -->
<link href="{{ asset('backend/vendor/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css" />

<!-- Quill css -->
<link href="{{ asset('backend/vendor/quill/quill.snow.css')}}" rel="stylesheet" type="text/css" />

<!-- dropify css -->
<link href="{{ asset('backend/vendor/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />

@endsection

@section('main-content')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Edit Company</h4>
            </div>
           
        </div>
    </div>

    <div class="row">
     
        <div class="col-12">

            <div class="card">
                <div class="card-body pt-0">
                    <div class="rootwizard">
                        <ul class="nav nav-pills nav-justified form-wizard-header mb-3">
                            <li class="nav-item">
                                <a class="nav-link rounded-0 py-2 navTab active" href="{{ route('admin.companies.stepForm',1) }}" data-step="1" data-tab-type="step1Section">
                                    <i class="bi bi-building fw-normal fs-20 align-middle me-1"></i>
                                    <span class="d-none d-sm-inline">About</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link rounded-0 py-2 navTab" href="{{ route('admin.companies.stepForm',2) }}" data-step="2" data-tab-type="step2Section">
                                    <i class="ri-profile-line fw-normal fs-20 align-middle me-1"></i>
                                    <span class="d-none d-sm-inline">Overview</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link rounded-0 py-2 navTab" href="{{ route('admin.companies.stepForm',3) }}" data-step="3" data-tab-type="step3Section">
                                    <i class="ri-map-pin-fill fw-normal fs-20 align-middle me-1"></i>
                                    <span class="d-none d-sm-inline">Location</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link rounded-0 py-2 navTab" href="{{ route('admin.companies.stepForm',4) }}" data-step="4" data-tab-type="step4Section">
                                    <i class="ri-profile-line fw-normal fs-20 align-middle me-1"></i>
                                    <span class="d-none d-sm-inline">Project Information</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link rounded-0 py-2 navTab" href="{{ route('admin.companies.stepForm',5) }}" data-step="5" data-tab-type="step5Section">
                                    <i class="ri-profile-line fw-normal fs-20 align-middle me-1"></i>
                                    <span class="d-none d-sm-inline">Serivces & Industries</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link rounded-0 py-2 navTab" href="{{ route('admin.companies.stepForm',6) }}" data-step="6" data-tab-type="step6Section">
                                    <i class="ri-profile-line fw-normal fs-20 align-middle me-1"></i>
                                    <span class="d-none d-sm-inline">Portfolio</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link rounded-0 py-2 navTab" href="{{ route('admin.companies.stepForm',7) }}" data-step="7" data-tab-type="step7Section">
                                    <i class="ri-profile-line fw-normal fs-20 align-middle me-1"></i>
                                    <span class="d-none d-sm-inline">Clients</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link rounded-0 py-2 navTab" href="{{ route('admin.companies.stepForm',8) }}" data-step="8" data-tab-type="step8Section">
                                    <i class="ri-profile-line fw-normal fs-20 align-middle me-1"></i>
                                    <span class="d-none d-sm-inline">Reviews</span>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content mb-0 b-0">
                           
                           
                        </div>
                    </div>
                </div>

                <div class="card-disabled">
                    <div class="card-portlets-loader"></div>
                </div>
            </div>
            
        </div> <!-- end col -->
    </div> 
    <!-- end row -->
   


@endsection

@section('custom_js')

<!-- Bootstrap Datepicker Plugin js -->
<script src="{{ asset('backend/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

<!--  Select2 Plugin Js -->
<script src="{{ asset('backend/vendor/select2/js/select2.min.js') }}"></script>

<!-- Quill Editor js -->
<script src="{{ asset('backend/vendor/quill/quill.min.js') }}"></script>

<!-- dropify Js -->
<script src="{{ asset('backend/vendor/dropify/dropify.min.js') }}"></script>

<script>
    
    var user_uuid = "{{$user_uuid}}";
    let userObj = {
        id:user_uuid
    };

</script>

@include('backend.company.partials.main_js')

@endsection