
<div class="sidebar">

	{{-- <div class="widget-card">
		<div class="widget-title">{{ __('Categories') }}</div>
		<div class="widget-body">
			<ul class="widget-list">
				@php $CategoryListForFilter = CategoryListForFilter(); @endphp
				@foreach ($CategoryListForFilter as $row)
				<li>
					<div class="icon">
						<a href="{{ route('frontend.product-category', [$row->id, $row->slug]) }}">
							<img src="{{ asset_path('media/'.$row->thumbnail) }}" alt="{{ $row->name }}" />
						</a>
					</div>
					<div class="desc">
						<a href="{{ route('frontend.product-category', [$row->id, $row->slug]) }}">{{ $row->name }}</a>
					</div>
					<div class="count">{{ $row->TotalProduct }}</div>
				</li>
				@endforeach
			</ul>
		</div>
	</div> --}}
	<div class="widget-card">

		@if(isset($metadata) && $metadata->id)
			<div class="widget-title">
				<a href="{{ route('frontend.product-category', [$metadata->id, $metadata->name]) }}">
					{{ $metadata->name }}
				</a>
			</div>
		@else
			<div class="widget-title">{{ __('Categories') }}</div>
		@endif

		<div class="widget-body">
			<ul class="widget-list">

				@if(isset($metadata) && $metadata->id)
					@php
						$CategoryListForFilter = SubCategoryListForFilter($metadata->id);
					@endphp
				@else
					@php
						$CategoryListForFilter = CategoryListForFilter();
					@endphp
				@endif

				@forelse ($CategoryListForFilter as $row)
					<li>
						<div class="icon">
							<a href="{{ route('frontend.product-category', [$row->id, $row->slug]) }}">
								<img src="{{ asset_path('media/'.$row->thumbnail) }}" alt="{{ $row->name }}">
							</a>
						</div>
						<div class="desc">
							<a href="{{ route('frontend.product-category', [$row->id, $row->slug]) }}">
								{{ $row->name }}
							</a>
						</div>
						<div class="count">{{ $row->TotalProduct }}</div>
					</li>
				@empty
					<li class="text-muted">{{ __('No Sub Categories Found') }}</li>
				@endforelse

			</ul>
		</div>
	</div>

	<div class="widget-card">
		<div class="widget-title">{{ __('Filter by Price') }}</div>
		<div class="widget-body">
			<div class="slider-range">
				<div id="slider-range"></div>
				<div class="price-range">
					<div class="price-label">{{ __('Price Range') }}:</div>
					<div class="price" id="amount"></div>
				</div>
				<input id="filter_min_price" type="hidden" value="0" />
				<input id="filter_max_price" type="hidden" />
				<a id="FilterByPrice" href="javascript:void(0);" class="btn theme-btn filter-btn"><i class="bi bi-funnel"></i> {{ __('Filter') }}</a>
			</div>
		</div>
	</div>
	<div class="widget-card">
		<div class="widget-title">{{ __('Brands') }}</div>
		<div class="widget-body">
			<ul class="widget-list">
				@php $BrandListForFilter = BrandListForFilter(); @endphp
				@foreach ($BrandListForFilter as $row)
				<li>
					<div class="icon">
						<a href="{{ route('frontend.brand', [$row->id, str_slug($row->name)]) }}">
							<img src="{{ asset_path('media/'.$row->thumbnail) }}" alt="{{ $row->name }}" />
						</a>
					</div>
					<div class="desc">
						<a href="{{ route('frontend.brand', [$row->id, str_slug($row->name)]) }}">{{ $row->name }}</a>
					</div>
					<div class="count">{{ $row->TotalProduct }}</div>
				</li>
				@endforeach
			</ul>
		</div>
	</div>
</div>
