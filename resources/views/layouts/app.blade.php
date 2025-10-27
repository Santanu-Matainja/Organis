<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    @php $gtext = gtext(); @endphp
	<!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title') | {{ $gtext['site_title'] }}</title>
	<!-- favicon -->
	<link rel="shortcut icon" href="{{ $gtext['favicon'] ? asset_path('media/'.$gtext['favicon']) : asset_path('backend/images/favicon.ico') }}" type="image/x-icon">
	<link rel="icon" href="{{ $gtext['favicon'] ? asset_path('media/'.$gtext['favicon']) : asset_path('backend/images/favicon.ico') }}" type="image/x-icon">
    <!-- CSS -->
	<style type="text/css">
	:root {
	  --backend-theme-color: {{ $gtext['theme_color'] }};
	}
	</style>
    <link rel="stylesheet" href="{{asset_path('backend/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset_path('backend/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset_path('backend/css/style.css')}}">
    <link rel="stylesheet" href="{{asset_path('backend/css/responsive.css')}}">
	@stack('style')
  </head>
  <body>
	@yield('content')
    <!-- JS -->
	<script src="{{asset_path('backend/js/jquery-3.6.0.min.js')}}"></script>
	<script src="{{asset_path('backend/js/popper.min.js')}}"></script>
	<script src="{{asset_path('backend/js/bootstrap.min.js')}}"></script>
	@stack('scripts')
  </body>
</html>