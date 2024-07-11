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
<body style="margin: 0; color: #0A2540;">
	<div class="mail-template" style="max-width: 100%; margin: 0 auto;">
		<table cellpadding="0" cellspacing="0" width="100%" style="max-width:600px; margin:0 auto;">
			<thead>
				<tr style="height:180px; width:100%;">
					<th style="text-align: center;">
                        {{-- <img style="padding:30px;" src="{{ asset(config('constants.default.email_logo')) }}" alt="" title="" /> --}}
                        {{ config('app.name') }}
                    </th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="padding:15px 0;">
                        @yield('email-content') 
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td>
						<p class="copyright" style="box-sizing:border-box;line-height:1.5em;margin-top:0;margin:0;background-color: #dffeef;padding:22px 0;text-align:center;font-size: 14px;font-weight: 700;font-family:'Nunito Sans',sans-serif;color: #0a2540;">© {{ date('Y') }} All Copyrights Reserved By {{config('app.name')}}</p>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
	
</body>
</html>