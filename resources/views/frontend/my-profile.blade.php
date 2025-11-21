@extends('layouts.frontend')

@section('title', __('Profile'))
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    #map { height: 400px; }
	.close-btn{
	font-size: 13px;
    border: 2px solid gray;
    padding: 1px 5px;
    cursor: pointer;
	}
</style>
@php $gtext = gtext(); @endphp

@section('meta-content')
	<meta name="keywords" content="{{ $gtext['og_keywords'] }}" />
	<meta name="description" content="{{ $gtext['og_description'] }}" />
	<meta property="og:title" content="{{ $gtext['og_title'] }}" />
	<meta property="og:site_name" content="{{ $gtext['site_name'] }}" />
	<meta property="og:description" content="{{ $gtext['og_description'] }}" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="{{ url()->current() }}" />
	<meta property="og:image" content="{{ asset_path('media/'.$gtext['og_image']) }}" />
	<meta property="og:image:width" content="600" />
	<meta property="og:image:height" content="315" />
	@if($gtext['fb_publish'] == 1)
	<meta name="fb:app_id" property="fb:app_id" content="{{ $gtext['fb_app_id'] }}" />
	@endif
	<meta name="twitter:card" content="summary_large_image">
	@if($gtext['twitter_publish'] == 1)
	<meta name="twitter:site" content="{{ $gtext['twitter_id'] }}">
	<meta name="twitter:creator" content="{{ $gtext['twitter_id'] }}">
	@endif
	<meta name="twitter:url" content="{{ url()->current() }}">
	<meta name="twitter:title" content="{{ $gtext['og_title'] }}">
	<meta name="twitter:description" content="{{ $gtext['og_description'] }}">
	<meta name="twitter:image" content="{{ asset_path('media/'.$gtext['og_image']) }}">
@endsection

@section('header')
@include('frontend.partials.header')
@endsection

@section('content')

<main class="main">
	<!-- Page Breadcrumb -->
	<div class="breadcrumb-section">
		<div class="container">
			<div class="row align-items-center">
				<div class="col-lg-6">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
							<li class="breadcrumb-item active" aria-current="page">{{ __('Profile') }}</li>
						</ol>
					</nav>
				</div>
				<div class="col-lg-6">
					<div class="page-title">
						<h1>{{ __('Profile') }}</h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Page Breadcrumb/ -->
	
	<!-- Inner Section -->
	<section class="inner-section inner-section-bg">
		<div class="container">
			<div class="row my-dashbord">
				<div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
					@include('frontend.partials.my-dashbord-sidebar')
				</div>
				<div class="col-sm-12 col-md-8 col-lg-9 col-xl-9">
					<div class="my_card">
						<div class="row">
							<div class="col-lg-12">
								@if(Session::has('success'))
								<div class="alert alert-success">
									{{Session::get('success')}}
								</div>
								@endif
								@if(Session::has('fail'))
								<div class="alert alert-danger">
									{{Session::get('fail')}}
								</div>
								@endif
								<form method="POST" action="{{ route('frontend.UpdateProfile') }}">
									@csrf
									<div class="row">
										<div class="col-md-6">
											<div class="mb-3">
												<label for="name">{{ __('Name') }}<span class="red">*</span></label>
												<input id="name" name="name" type="text" class="form-control" placeholder="{{ __('Name') }}" value="@if(isset(Auth::user()->name)) {{ Auth::user()->name }} @endif" required />
												@if ($errors->has('name'))
												<span class="text-danger">{{ $errors->first('name') }}</span>
												@endif
											</div>
										</div>
				
										<div class="col-md-6">
											<div class="mb-3">
												<label for="email">{{ __('Email Address') }}<span class="red">*</span></label>
												<input id="email" name="email" type="email" class="form-control" placeholder="{{ __('Email Address') }}" value="@if(isset(Auth::user()->email)) {{ Auth::user()->email }} @endif" required readonly />
												@if ($errors->has('email'))
												<span class="text-danger">{{ $errors->first('email') }}</span>
												@endif
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="mb-3">
												<label for="phone">{{ __('Phone') }}</label>
												<input id="phone" name="phone" type="number" class="form-control" placeholder="{{ __('Phone') }}" value="{{ old('phone', Auth::user()->phone ?? '') }}" />
											</div>
										</div>
								
										<div class="col-md-6">
											<div class="mb-3">
												<label for="address">{{ __('Address') }}</label>
												<textarea id="address" name="address" class="form-control" placeholder="{{ __('Address') }}" rows="4">@if(isset(Auth::user()->address)) {{ Auth::user()->address }} @endif</textarea>
											</div>
										</div>
									</div>
									<div class="row">

										<div class="col-md-6">
											<div class="mb-3">
												<label for="state">{{ __('State') }}</label>
												<input type="text" id="state" name="state" class="form-control" placeholder="{{ __('State') }}"
													value="@if(isset(Auth::user()->state)) {{ Auth::user()->state }} @endif">
											</div>
										</div>		
										<div class="col-md-6">
											<div class="mb-3">
												<label for="city">{{ __('City') }}</label>
												<input type="text" id="city" name="city" class="form-control" placeholder="{{ __('City') }}"
													value="@if(isset(Auth::user()->city)) {{ Auth::user()->city }} @endif">
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<div class="mb-3">
												<label for="zip_code">{{ __('Zip Code') }}</label>
												<input type="number" id="zip_code" name="zip_code" class="form-control" placeholder="{{ __('Zip Code') }}"
													value="{{ old('phone', Auth::user()->zip_code ?? '') }}">
											</div>
										</div>
										<div class="col-md-6">
											<div class="mb-3">
												<label for="country_id">{{ __('Country') }}</label>
												<select id="country_id" name="country_id" class="form-control" data-required="true">
													<option value="">{{ __('-- Select Country --') }}</option>
													@foreach($countries as $country)
														<option value="{{ $country->id }}"
															@if(isset(Auth::user()->country_id) && Auth::user()->country_id == $country->id) selected @endif>
															{{ $country->country_name }}
														</option>
													@endforeach
												</select>
											</div>
										</div>
									</div>
									@if(Auth::user()->role_id == 2)
										<div class="row">
											<div class="col-md-4">
												<div class="form-group">
													<label for="lat">{{ __('Latitude') }}<span class="red">*</span></label>
													<input type="text" name="lat" id="lat" value="@if(isset(Auth::user()->lat)) {{ Auth::user()->lat }} @endif" class="form-control parsley-validated" data-required="true" readonly>
												</div>
											</div>
											<div class="col-md-4">
												<div class="form-group">
													<label for="lng">{{ __('Longitude') }}<span class="red">*</span></label>
													<input type="text" name="lng" id="lng" value="@if(isset(Auth::user()->lng)) {{ Auth::user()->lng }} @endif" class="form-control parsley-validated" data-required="true" readonly>
												</div>
											</div>
											<div class="col-md-4 mt-3 d-flex align-items-center justify-content-center">
												<button type="button" class="btn" style="background: var(--theme-color); color: #ffffff;" data-bs-toggle="modal" data-bs-target="#mapModal">Pick Location</button>
											</div>
											
										</div>
									@endif	
									<div class="row">
										<div class="col-md-6">
											@if($gtext['is_recaptcha'] == 1)
											<div class="mb-3">
												<div class="g-recaptcha" data-sitekey="{{ $gtext['sitekey'] }}"></div>
												@if ($errors->has('g-recaptcha-response'))
												<span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
												@endif
											</div>
											@endif
										</div>
									</div>
									<input name="user_id" type="hidden" value="@if(isset(Auth::user()->id)) {{ Auth::user()->id }} @endif" />
									<input type="submit" class="btn theme-btn mt-4" value="{{ __('Update') }}" />
								</form>
								{{-- MAP MODEL  --}}
								<div class="modal fade" id="mapModal" tabindex="-1">
									<div class="modal-dialog modal-lg modal-dialog-centered">
										<div class="modal-content">

										<div class="modal-header">
											<h5 class="modal-title">Select Location</h5>
											<a class="close-btn" data-bs-dismiss="modal">X</a>
										</div>

										<div class="modal-body">
											<div id="map"></div>
										</div>

										<div class="modal-footer" style="border: none !important;">
											<button class="btn btn-success" onclick="selectLocation()">Select</button>
											<button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
										</div>

										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- /Inner Section/ -->
</main>

@endsection

@push('scripts')
@if($gtext['is_recaptcha'] == 1)
<script src='https://www.google.com/recaptcha/api.js' async defer></script>
@endif
<script type="text/javascript">
	var my_dashbord_href = location.href;
	var my_dashbord_elem = '.sidebar-nav li a[href="' + my_dashbord_href + '"]';
	$('ul.sidebar-nav li').parent().removeClass('active');
	$('ul.sidebar-nav li a').parent().removeClass('active');
	$(my_dashbord_elem).addClass('active');
</script>

<script>
let map;
let marker;
let selectedLat = null;
let selectedLng = null;

document.getElementById('mapModal').addEventListener('shown.bs.modal', function () {

    // If map not initialized → initialize
    if (!map) {

        // Default center (India as fallback)
        let defaultLat = 20.5937;
        let defaultLng = 78.9629;

        // Try browser location first
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                defaultLat = position.coords.latitude;
                defaultLng = position.coords.longitude;

                loadMap(defaultLat, defaultLng);

            }, () => {
                loadMap(defaultLat, defaultLng); // fallback
            });
        } else {
            loadMap(defaultLat, defaultLng);
        }
    }

    setTimeout(() => map.invalidateSize(), 300);
});

function loadMap(lat, lng) {
    map = L.map('map').setView([lat, lng], 13);

    L.tileLayer(
	'https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',
	{ maxZoom: 20 }
	).addTo(map);


    // Marker
    marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    selectedLat = lat;
    selectedLng = lng;

    // Update on drag
    marker.on('dragend', function (e) {
        let pos = marker.getLatLng();
        selectedLat = pos.lat;
        selectedLng = pos.lng;
    });

    // Click to move marker
    map.on("click", function (e) {
        selectedLat = e.latlng.lat;
        selectedLng = e.latlng.lng;
        marker.setLatLng([selectedLat, selectedLng]);
    });
}

function selectLocation() {
    document.getElementById("lat").value = selectedLat;
    document.getElementById("lng").value = selectedLng;

    var modal = bootstrap.Modal.getInstance(document.getElementById("mapModal"));
    modal.hide();
}
</script>
@endpush	