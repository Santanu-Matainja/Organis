@extends('layouts.frontend')

@section('title', __('Checkout'))
@php 
$gtext = gtext(); 
$gtax = getTax();
// $tax_rate = $gtax['percentage'];
@endphp

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
							<li class="breadcrumb-item active" aria-current="page">{{ __('Checkout') }}</li>
						</ol>
					</nav>
				</div>
				<div class="col-lg-6">
					<div class="page-title">
						<h1>{{ __('Checkout') }}</h1>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /Page Breadcrumb/ -->
	
	<!-- Inner Section -->
	<section class="inner-section inner-section-bg my_card">
		<div class="container">
			<form novalidate="" data-validate="parsley" id="checkout_formid">
				@csrf
				<div class="row">
					<div class="col-lg-7">
						<h5>{{ __('Delivary Information') }}</h5>
						@auth
						@else
						<p>{{ __('Already have an account?') }} <strong><a href="{{ route('frontend.login') }}">{{ __('login') }}</a></strong></p>
						@endauth
						<div class="row">
							<div class="col-md-12">
								<div class="mb-3">
									<input id="name" name="name" type="text" placeholder="{{ __('Name') }}" value="{{ old('name', $shippinginfo->name ?? '') }}" class="form-control parsley-validated" data-required="true">
									<span class="text-danger error-text name_error"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<input id="email" name="email" type="email" placeholder="{{ __('Email Address') }}" value="{{ old('email', $shippinginfo->email ?? '') }}" class="form-control parsley-validated" data-required="true">
									<span class="text-danger error-text email_error"></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<input id="phone" name="phone" type="text" placeholder="{{ __('Phone') }}" value="{{ old('phone', $shippinginfo->phone ?? '') }}" class="form-control parsley-validated" data-required="true">
									<span class="text-danger error-text phone_error"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<select id="country" name="country" class="form-control parsley-validated" data-required="true">
										<option value="">{{ __('Country') }}</option>
										@foreach($country_list as $row)
											<option value="{{ $row->id }}"
												{{ old('country', $shippinginfo->country ?? '') == $row->id ? 'selected' : '' }}>
												{{ $row->country_name }}
											</option>
										@endforeach
									</select>

									<span class="text-danger error-text country_error"></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<input id="state" name="state" type="text" placeholder="{{ __('State') }}" class="form-control parsley-validated" data-required="true" value="{{ old('state', $shippinginfo->state ?? '') }}">
									<span class="text-danger error-text state_error"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<input id="zip_code" name="zip_code" type="number" placeholder="{{ __('Zip Code') }}" class="form-control parsley-validated" data-required="true" value="{{ old('zip_code', $shippinginfo->zip_code ?? '') }}">
									<span class="text-danger error-text zip_code_error"></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<input id="city" name="city" type="text" placeholder="{{ __('City') }}" class="form-control parsley-validated" data-required="true" value="{{ old('city', $shippinginfo->city ?? '') }}">
									<span class="text-danger error-text city_error"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="mb-3">
									<textarea id="address" name="address" placeholder="{{ __('Address') }}" rows="2" class="form-control parsley-validated" data-required="true">{{ old('address', $shippinginfo->address ?? '') }}</textarea>
									<span class="text-danger error-text address_error"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="checkboxlist">
									<label class="checkbox-title">
										<input id="new_account" name="new_account" type="checkbox" value="1">{{ __('Use Registered Address As Delivary Address') }} 
									</label>
								</div>
								@if ($errors->has('password'))
								<span class="text-danger">{{ $errors->first('password') }}</span>
								@endif
							</div>
						</div>
						
						{{-- <div class="row hideclass" id="new_account_pass">
							<div class="col-md-6">
								<div class="mb-3">
									<input type="password" name="password" id="password" class="form-control" placeholder="{{ __('Password') }}">
									<span class="text-danger error-text password_error"></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="{{ __('Confirm password') }}">
								</div>
							</div>
						</div> --}}
						
						<h5 class="mt10">{{ __('Payment Method') }}</h5>
						<div class="row">
							<div class="col-md-12">
								<span class="text-danger error-text payment_method_error"></span>
								{{-- @if($gtext['stripe_isenable'] == 1)
								<div class="payment_card">
									<div class="checkboxlist">
										<label class="checkbox-title">
											<input id="payment_method_stripe" name="payment_method" type="radio" value="3"><img src="{{ asset_path('frontend/images/stripe.png') }}" alt="Stripe" />
										</label>
									</div>
									<div id="pay_stripe" class="row hideclass">
										<div class="col-md-12">
											<div class="row">
												<div class="col-md-12">
													<div class="mb-3">
														<div class="form-control" id="card-element"></div>
														<span class="card-errors" id="card-errors"></span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								@endif --}}
								
								 @if($gtext['isenable_paypal'] == 1)
								<div class="payment_card">
									<div class="checkboxlist">
										<label class="checkbox-title">
											<input id="payment_method_paypal" name="payment_method" type="radio" value="4"><img src="{{ asset_path('frontend/images/paypal.png') }}" alt="Paypal" />
										</label>
									</div>
									<p id="pay_paypal" class="hideclass">{{ __('Pay online via Paypal') }}</p>
								</div>
								@endif
								
								{{-- @if($gtext['isenable_razorpay'] == 1)
								<div class="payment_card">
									<div class="checkboxlist">
										<label class="checkbox-title">
											<input id="payment_method_razorpay" name="payment_method" type="radio" value="5"><img src="{{ asset_path('frontend/images/razorpay.png') }}" alt="Razorpay" />
										</label>
									</div>
									<p id="pay_razorpay" class="hideclass">{{ __('Pay online via Razorpay') }}</p>
								</div>
								@endif
								
								@if($gtext['isenable_mollie'] == 1)
								<div class="payment_card">
									<div class="checkboxlist">
										<label class="checkbox-title">
											<input id="payment_method_mollie" name="payment_method" type="radio" value="6"><img src="{{ asset_path('frontend/images/mollie.png') }}" alt="Mollie" />
										</label>
									</div>
									<p id="pay_mollie" class="hideclass">{{ __('Pay online via Mollie') }}</p>
								</div>
								@endif --}}
								
								@if($gtext['cod_isenable'] == 1)
								<div class="payment_card">
									<div class="checkboxlist">
										<label class="checkbox-title">
											<input id="payment_method_cod" name="payment_method" type="radio" value="1"><img src="{{ asset_path('frontend/images/cash_on_delivery.png') }}" alt="Cash on Delivery" />
										</label>
									</div>
									<p id="pay_cod" class="hideclass">{{ $gtext['cod_description'] }}</p>
								</div>
								@endif
								
								{{-- @if($gtext['bank_isenable'] == 1)
								<div class="payment_card">
									<div class="checkboxlist">
										<label class="checkbox-title">
											<input id="payment_method_bank" name="payment_method" type="radio" value="2"><img src="{{ asset_path('frontend/images/bank_transfer.png') }}" alt="Bank Transfer" />
										</label>
									</div>
									<p id="pay_bank" class="hideclass">{{ $gtext['bank_description'] }}</p>
								</div>
								@endif --}}
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="mb-3 mt10">
									<textarea name="comments" class="form-control" placeholder="{{ __('Write comment') }}" rows="2"></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-5">
						<div class="carttotals-card">
							<div class="carttotals-head">{{ __('Order Summary') }}</div>
							<div class="carttotals-body">
								@if($ShoppingCartData)
									<table class="table">
										<tbody>
											@php
												$CartDataArr = [];
												$Total_Price = 0;
												$GrandTotal = 0;

												foreach ($ShoppingCartData as $row) {
													$Total_Price += $row['price'] * $row['qty'];
													$pid = (int) ($row['perisible'] ?? 0);
													$sellerId = $row['seller_id'];
													$CartDataArr[$sellerId][$pid][] = $row;
												}

												 $categoryIds = array_column($ShoppingCartData, 'category_id');
												  $parents = \DB::table('pro_categories')
															->whereIn('id', $categoryIds)
															->pluck('parent_id', 'id')
															->toArray();
											@endphp

											@foreach($CartDataArr as $sellerId => $groups)
												
												@php
													
													$sample = null;
													foreach($groups as $g) { if(count($g) > 0){ $sample = $g[0]; break; } }
												@endphp

												@if($sample)
													<tr>
														<td colspan="2" class="tp_group">
															<div class="store_logo">
																<a href="{{ route('frontend.stores', [$sample['seller_id'], str_slug($sample['store_name'])]) }}">
																	<img src="{{ asset_path('media/'.$sample['store_logo']) }}" alt="{{ $sample['store_name'] }}" />
																</a>
															</div>
															<div class="store_name">
																<p><strong>{{ __('Sold By') }}</strong></p>
																<p><a href="{{ route('frontend.stores', [$sample['seller_id'], str_slug($sample['store_url'])]) }}">{{ $sample['store_name'] }}</a></p>
															</div>
														</td>
													</tr>
												@endif

												@foreach([0,1] as $isPerishable)
													@if(isset($groups[$isPerishable]) && count($groups[$isPerishable])>0)
														@php
															$products = $groups[$isPerishable];
															$sellerTotalPrice = 0;
															$taxTotal = 0;
															$sellerShippingSlab = 0;
														@endphp

														<tr>
															<td colspan="2" style="background:#f9f9f9;padding:8px 12px;">
																<strong>
																	@if($isPerishable == 1)
																		{{ __('Perishable Items') }}
																	@else
																		{{ __('Non-Perishable Items') }}
																	@endif
																</strong>
															</td>
														</tr>

														@foreach($products as $row)
															@php
																$unit = $row['unit'] == '0' ? '' : '<strong>'.$row['qty'].' '.$row['unit'].'</strong>';
																// new tax rate
																$priceTotal = $row['price'] * $row['qty'];
																$taxRate = 0;
																$matched = false;

																foreach($gtax as $taxRow){
																	if(!empty($taxRow['category'])){
																		if(in_array($row['category_id'], $taxRow['category'])){
																			$taxRate = $taxRow['percentage'];
																			$matched = true;
																			break;
																		} else {
																			$parentId = $parents[$row['category_id']] ?? null;
																			if($parentId && in_array($parentId, $taxRow['category'])){
																				$taxRate = $taxRow['percentage'];
																				$matched = true;
																				break;
																			}
																		}
																	}
																}

																if(!$matched){
																	$defaultTax = collect($gtax)->firstWhere('category', null);
																	$taxRate = $defaultTax ? $defaultTax['percentage'] : 0;
																}

																$tax = ($priceTotal * $taxRate) / 100;
																$TotalPrice = $priceTotal + $tax;

																$sellerTotalPrice += $TotalPrice;
																$taxTotal += $tax;

																// product-specific slab shipping
																$productShipping = \DB::table('product_shippings')->where('product_id', $row['id'])->first();
																$productShippingFee = 0;
																if ($productShipping && !empty($productShipping->slab)) {
																	$slabs = json_decode($productShipping->slab, true);
																	if (is_string($slabs)) $slabs = json_decode($slabs, true);
																	if (is_array($slabs) && count($slabs) > 0) {
																		foreach ($slabs as $slab) {
																			$min = (int)$slab['min_qty'];
																			$max = (int)$slab['max_qty'];
																			$price = (float)$slab['price'];
																			if($row['qty'] >= $min && $row['qty'] <= $max){
																				$productShippingFee = $price;
																				break;
																			}
																		}
																	}
																}
																$sellerShippingSlab += $productShippingFee;
															@endphp

															<tr>
																<td>
																	<p class="title">
																		<a href="{{ route('frontend.product', [$row['id'], str_slug($row['name'])]) }}">{{ $row['name'] }}</a>
																	</p>
																	<p class="sub-title">{!! $unit !!}</p>
																</td>
																<td>
																	@if($gtext['currency_position'] == 'left')
																		<p class="price">{{ $gtext['currency_icon'] }}{{ NumberFormat($priceTotal) }}</p>
																		<p class="sub-price">{{ $gtext['currency_icon'] }}{{ $row['price'] }} x {{ $row['qty'] }}</p>
																	@else
																		<p class="price">{{ NumberFormat($priceTotal) }}{{ $gtext['currency_icon'] }}</p>
																		<p class="sub-price">{{ $row['price'] }}{{ $gtext['currency_icon'] }} x {{ $row['qty'] }}</p>
																	@endif
																</td>
															</tr>
														@endforeach

														{{-- Tax row (group) --}}
														<tr>
															<td colspan="2">
																<span class="title">{{ __('Tax') }}</span>
																<span class="price">{{ $gtext['currency_icon'] . NumberFormat($taxTotal) }}</span>
															</td>
														</tr>

														<tr>
															<td colspan="2">
																<span class="title">{{ __('Shipping Fee') }}</span>
																@php $initialShippingFee = $sellerShippingSlab; @endphp
																<span class="price shipping_fee">{{ $gtext['currency_icon'] . NumberFormat($initialShippingFee) }}</span>
															</td>
														</tr>

														{{-- Total (group) --}}
														<tr>
															<td colspan="2">
																<span class="total">{{ __('Total') }}</span>
																<span class="total-price">{{ $gtext['currency_icon'] . NumberFormat($sellerTotalPrice + $initialShippingFee) }}</span>
															</td>
														</tr>

														{{-- Shipping Method specific to this group --}}
														<tr>
															<td colspan="2">
																<h6>{{ __('Shipping Method') }}</h6>

																@foreach($shipping_list as $ship)
																	@if(in_array($ship->id, explode(',', $products[0]['delivarytypeid'])))
																		@php
																			$shipping_fee = 0;
																			if($ship->id == 2) $shipping_fee = $sellerShippingSlab;
																			elseif($ship->id == 3) $shipping_fee = 0;
																			elseif($ship->id == 4) $shipping_fee = ($ship->shipping_fee ?? 0) + $sellerShippingSlab;
																			else $shipping_fee = $ship->shipping_fee ?? 0;

																			$displayFee = $gtext['currency_position'] == 'left'
																				? $gtext['currency_icon'] . NumberFormat($shipping_fee)
																				: NumberFormat($shipping_fee) . $gtext['currency_icon'];
																		@endphp

																		<div class="checkboxlist">
																			<label class="checkbox-title">
																				<input type="radio"
																					class="shipping_method"
																					name="shipping_method[{{ $sellerId }}][{{ $isPerishable }}]"
																					data-sellerid="{{ $sellerId }}"
																					data-perishable="{{ $isPerishable }}"
																					data-shipid="{{ $ship->id }}"
																					data-shippingfee="{{ $shipping_fee }}"
																					data-total="{{ $sellerTotalPrice }}"
																					value="{{ $shipping_fee }}"
																					{{ $ship->id == 2 ? 'checked' : '' }}
																				>
																				{{ $ship->lable }} : {{ $displayFee }}
																			</label>
																		</div>
																	@endif
																@endforeach

																<input type="hidden"
																	id="shipping_id_{{ $sellerId }}_{{ $isPerishable }}"
																	name="shipping_id[{{ $sellerId }}][{{ $isPerishable }}]"
																	value=""
																>
															</td>
														</tr>

														@php
															// add group totals into grand total
															$GrandTotal += ($sellerTotalPrice + $sellerShippingSlab);
														@endphp

													@endif
												@endforeach
											@endforeach

											{{-- Commission --}}
											<tr>
												<td colspan="2" style="border-top:2px solid #ccc;">
													<strong>{{ __('Commission') }}</strong>
													<span class="price">
														{{ $gtext['currency_icon'] }}<span class="commission">{{ NumberFormat($commision->commission) }}</span>
													</span>
												</td>
											</tr>

											{{-- Hidden raw commission value (numeric) for JS --}}
											<input type="hidden" id="commission" value="{{ $commision->commission }}">

											{{-- Grand total --}}
											<tr>
												<td colspan="2">
													<strong>{{ __('Grand Total') }}</strong>
													<span class="price">
														{{ $gtext['currency_icon'] }}<span class="grand_total_value">{{ NumberFormat($GrandTotal + 0) }}</span>
													</span>
												</td>
											</tr>

										</tbody>
									</table>

									{{-- Checkout Section --}}
									<input name="customer_id" type="hidden" value="@if(isset(Auth::user()->id)) {{ Auth::user()->id }} @endif" />
									<input name="razorpay_payment_id" id="razorpay_payment_id" type="hidden" />
									<a id="checkout_submit_form" href="javascript:void(0);" class="btn theme-btn mt10 checkout_btn">{{ __('Checkout') }}</a>

									@if(Session::has('pt_payment_error'))
										<div class="alert alert-danger">
											{{ Session::get('pt_payment_error') }}
										</div>
									@endif
								@endif
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</section>
	<!-- /Inner Section/ -->
</main>

@endsection

@push('scripts')
<script src="{{asset_path('frontend/js/parsley.min.js')}}"></script>
<script type="text/javascript">
var theme_color = "{{ $gtext['theme_color'] }}";
var site_name = "{{ $gtext['site_name'] }}";
var validCardNumer = 0;
var TEXT = [];
	TEXT['Please type valid card number'] = "{{ __('Please type valid card number') }}";
</script>
@if($gtext['stripe_isenable'] == 1)
<script src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">
	var isenable_stripe = "{{ $gtext['stripe_isenable'] }}";
	var stripe_key = "{{ $gtext['stripe_key'] }}";
</script>
<script src="{{asset_path('frontend/pages/payment_method.js')}}"></script>
@endif

@if($gtext['isenable_razorpay'] == 1)
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script type="text/javascript">
	var isenable_razorpay = "{{ $gtext['isenable_razorpay'] }}";
	var razorpay_key_id = "{{ $gtext['razorpay_key_id'] }}";
	var razorpay_currency = "{{ $gtext['razorpay_currency'] }}";
</script>
@endif
<script src="{{asset_path('frontend/pages/checkout.js')}}"></script>
<script>
const shipinfo = {
    name: "{{ $shippinginfo->name ?? '' }}",
    email: "{{ $shippinginfo->email ?? '' }}",
    phone: "{{ $shippinginfo->phone ?? '' }}",
    country_id: "{{ $shippinginfo->country ?? '' }}",
    state: "{{ $shippinginfo->state ?? '' }}",
    zip_code: "{{ $shippinginfo->zip_code ?? '' }}",
    city: "{{ $shippinginfo->city ?? '' }}",
    address: "{{ $shippinginfo->address ?? '' }}"
};

const authAddress = {
    name: "{{ Auth::user()->name ?? '' }}",
    email: "{{ Auth::user()->email ?? '' }}",
    phone: "{{ Auth::user()->phone ?? '' }}",
    country_id: "{{ Auth::user()->country_id ?? '' }}",
    state: "{{ Auth::user()->state ?? '' }}",
    zip_code: "{{ Auth::user()->zip_code ?? '' }}",
    city: "{{ Auth::user()->city ?? '' }}",
    address: "{{ Auth::user()->address ?? '' }}"
};

$('#new_account').on('change', function () {
    if ($(this).is(':checked')) {
		$('#name').val(authAddress.name);
        $('#email').val(authAddress.email);
        $('#phone').val(authAddress.phone);
        $('#country').val(authAddress.country_id).trigger("change");
        $('#state').val(authAddress.state);
        $('#zip_code').val(authAddress.zip_code);
        $('#city').val(authAddress.city);
        $('#address').val(authAddress.address);
    } else {
         $('#name').val(shipinfo.name);
        $('#email').val(shipinfo.email);
        $('#phone').val(shipinfo.phone);
        $('#country').val(shipinfo.country_id).trigger("change");
        $('#state').val(shipinfo.state);
        $('#zip_code').val(shipinfo.zip_code);
        $('#city').val(shipinfo.city);
        $('#address').val(shipinfo.address);
    }
});
</script>

@endpush	