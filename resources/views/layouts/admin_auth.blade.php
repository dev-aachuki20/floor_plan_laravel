<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{{ config('app.name')}} | @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully responsive admin theme which can be used to build CRM, CMS,ERP etc." name="description" />
    <meta content="Techzaa" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset(config('constant.default.favicon')) }}">

    <!-- App css -->
    <link href="{{ asset('backend/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />

    <!-- Icons css -->
    <link href="{{ asset('backend/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Main css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/css/style.css') }}">

    @yield('custom_css')
</head>

<body class="authentication-bg position-relative">
    <div class="account-pages position-relative">
        <div class="container">
            @yield('main-content')
        </div>
        <!-- end container -->
    </div>
    <!-- end page -->


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @include('partials.alert')

    @yield('custom_js')
</body>
</html>