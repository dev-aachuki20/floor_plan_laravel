<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>

	<!-- FONT FAMILY -->
	<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&family=Nunito+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @yield('styles')
</head>
<body style="font-family: Arial, Helvetica, sans-serif;font-size: 16px;">
	<div style="max-width: 620px;margin: 0 auto;">
		<h1 style="background-color: #ecf1f5;padding: 20px 10px;font-size: 24px;text-align: center;color: #295597;margin: 0;">FLOORPLAN</h1>
		<!-- <h1 style="background-color: #ecf1f5;padding: 20px 10px;font-size: 24px;text-align: center;color: #295597;margin: 0;">{{ config('app.name') }}</h1> -->

		@yield('email-content') 

		<div style="background-color: #295597;font-size: 16px;text-align: center;color: #fff;padding: 20px 10px;">© {{ date('Y') }} All Copyrights Reserved By {{config('app.name')}}</div>
	</div>
</body>
</html>