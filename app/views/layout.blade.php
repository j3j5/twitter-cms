<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- 		<link rel="icon" type="image/gif" href="/favicon.ico" /> -->

		{{ Asset::container('header')->styles(); }}
		{{ Asset::container('header')->scripts(); }}

		<title>{{{ Config::get('app.project_name') }}}</title>

	</head>
	<body>
		@include('navbar')

		<div id="mainContent" class="container main">
				<!-- check for flash notification message -->
				@if(Session::has('flash_notice'))
					<div id="flash_notice" class="alert alert-success alert-dismissible my-alert">
						<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						{{ Session::get('flash_notice') }}
					</div>
				@endif

				<!-- check for login error flash var -->
				@if (Session::has('flash_error'))
					<div id="flash_error" class="alert alert-danger alert-dismissible my-alert" >
						<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						{{ Session::get('flash_error') }}
					</div>
				@endif

				@yield('content')
		</div>

		{{ Asset::container('footer')->scripts(); }}
	</body>
</html>
