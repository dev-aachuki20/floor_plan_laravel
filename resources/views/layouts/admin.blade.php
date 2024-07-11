
<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="utf-8" />
    <title>{{ config('app.name') }} | @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully responsive admin theme which can be used to build CRM, CMS,ERP etc." name="description" />
    <meta content="HIPL" name="author" />
    <meta content="{{ csrf_token() }}" name="csrf-token" />


    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset(config('constant.default.favicon')) }}">

    @include('backend.partials.hstyle')

    @yield('custom_css')
</head>
<body>
	<div class="loader-div" style="display: none;"><div><img src="{{asset(config('constant.default.page_loader'))}}" width="100"></div></div>

    <!-- Begin page -->
    <div class="wrapper">
    
        <!-- ========== Topbar Start ========== -->
        @include('backend.partials.header')
        <!-- ========== Topbar End ========== -->

        <!-- ========== Left Sidebar Start ========== -->
        @include('backend.partials.sidebar')
        <!-- ========== Left Sidebar End ========== -->


        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                <!-- Start Content-->
                <div class="container-fluid">
                    @yield('main-content')
                </div>
                <!-- container -->
            </div>
             <!-- content -->

            @include('backend.partials.footer')
        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

	<div class="popup_render_div"></div>
	
    @include('backend.partials.fscript')

	@yield('custom_js')
</body>
</html>
