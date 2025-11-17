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
					<div class="alert alert-success text-left" id="successMessage" style="display: none">
					</div>
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
					<div id="alert-box"></div>
					<form class="text-left" id="login_form" method="POST" action="{{ route('frontend.verifyemailseller') }}">
						@csrf
						
                        <div class="form-group">
                            <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('Email Address') }}" value="{{ $email }}" required readonly>
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

					<h6><a id="resendOtpBtn" href="javascript:void(0);"  class=" text-decoration-none">Resend OTP</a></h6>

					<h3><a class="btn text-decoration-none" href="{{ url('/user/login') }}">{{ __('Back to login') }}</a></h3>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /main Section -->
@endsection

@push('scripts')
<script>
  
let resendBtn = document.getElementById("resendOtpBtn");

resendBtn.addEventListener("click", function () {
    let email = "{{ $email }}";

    fetch("{{ route('frontend.resendOtpseller') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ email: email })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {

          	let msgDiv = document.getElementById("successMessage");
			msgDiv.style.display = "block";
			msgDiv.innerText = data.msg; 

        }
    });
});

</script>
@endpush
