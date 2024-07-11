@extends('layouts.admin')
@section('title', trans('global.dashboard'))

@section('custom_css')
@endsection

@section('main-content')

 
<div class="row mt-4">
    <div class="col-xxl-3 col-sm-6">
        <div class="card widget-flat text-bg-pink">
            <div class="card-body">
                <div class="float-end">
                    <i class="ri-eye-line widget-icon"></i>
                </div>
                <h6 class="text-uppercase mt-0" title="Customers">Daily Visits</h6>
                <h2 class="my-2">0</h2>
            </div>
        </div>
    </div> <!-- end col-->

    <div class="col-xxl-3 col-sm-6">
        <div class="card widget-flat text-bg-purple">
            <div class="card-body">
                <div class="float-end">
                    <i class="ri-wallet-2-line widget-icon"></i>
                </div>
                <h6 class="text-uppercase mt-0" title="Customers">Revenue</h6>
                <h2 class="my-2">0</h2>
            </div>
        </div>
    </div> <!-- end col-->

    <div class="col-xxl-3 col-sm-6">
        <div class="card widget-flat text-bg-info">
            <div class="card-body">
                <div class="float-end">
                    <i class="ri-group-2-line widget-icon"></i>
                </div>
                <h6 class="text-uppercase mt-0" title="Customers">Staffs</h6>
                <h2 class="my-2">{{ $staffCount }}</h2>
            </div>
        </div>
    </div> <!-- end col-->

    <div class="col-xxl-3 col-sm-6">
        <div class="card widget-flat text-bg-primary">
            <div class="card-body">
                <div class="float-end">
                    <i class="ri-group-2-line widget-icon"></i>
                </div>
                <h6 class="text-uppercase mt-0" title="Companies">Companies</h6>
                <h2 class="my-2">{{ $companyCount }}</h2>
            </div>
        </div>
    </div> <!-- end col-->
</div>

@endsection

@section('custom_js')

@endsection