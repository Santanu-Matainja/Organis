@extends('layouts.app')

@section('title', __('Verify Email'))

@php $gtext = gtext(); @endphp

@section('content')
<!-- main Section -->
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div class="loginsignup-area">
				<div class="loginsignup text-center">
					<div class="logo">
						<a href="{{ url('/user/login') }}">
							<img src="{{ $gtext['back_logo'] ? asset_path('media/'.$gtext['back_logo']) : asset_path('backend/images/backend-logo.png') }}" alt="logo">
						</a>
					</div>
					<p>{{ __('Enter 6-digit code sent to your email') }}</p>
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
					<form class="text-left" id="login_form" method="POST" action="{{ route('frontend.verifyemail') }}">
						@csrf
						
                        <div class="form-group">
                            <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('Email Address') }}" value="{{ session('email') }}" required readonly>
                            @if ($errors->has('email'))
                            <span class="text-danger">{{ $errors->first('email') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <input name="otp" type="text" class="form-control @error('otp') is-invalid @enderror" placeholder="{{ __('Enter 6 Digit Verification Code') }}" value="{{ old('otp') }}" required maxlength="6">
                            @if ($errors->has('otp'))
                            <span class="text-danger">{{ $errors->first('otp') }}</span>
                            @endif
                        </div>

						<input type="submit" class="btn login-btn" value="{{ __('Verify Email') }}">
					</form>
					<h3><a class="btn text-decoration-none" href="{{ url('/user/login') }}">{{ __('Back to login') }}</a></h3>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /main Section -->
@endsection

@push('scripts')

@endpush
