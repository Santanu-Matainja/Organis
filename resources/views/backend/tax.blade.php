@extends('layouts.backend')

@section('title', __('Tax'))

@section('content')
<!-- main Section -->
<div class="main-body">
	<div class="container-fluid">
		@php $vipc = vipc(); @endphp
		@if($vipc['bkey'] == 0) 
		@include('backend.partials.vipc')
		@else
		<div class="row mt-25">
			<div class="col-lg-12">
				<div class="card">
					<div class="card-header">
						<div class="row">
							<div class="col-lg-12">
								<span>{{ __('Tax') }}</span>
							</div>
						</div>
					</div>
					<!--Data Entry Form-->
					<div class="card-body">
						<form novalidate="" data-validate="parsley" id="DataEntry_formId">
							<div class="row">
								<div class="col-md-12">
									<div class="form-group">
										<label for="title">{{ __('Title') }}<span class="red">*</span></label>
										<input type="text" value="{{ $datalist['title'] }}" name="title" id="title" class="form-control parsley-validated" data-required="true">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-5">
									<div class="form-group">
										<label for="percentage">{{ __('Default Percentage') }} %<span class="red">*</span></label>
										<input type="number" value="{{ $datalist['percentage'] }}" name="percentage" id="percentage" class="form-control parsley-validated" data-required="true">
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-group">
										<label for="is_publish">{{ __('Status') }}<span class="red">*</span></label>
										<select name="is_publish" id="is_publish" class="chosen-select form-control">
										@foreach($statuslist as $row)
											<option {{ $row->id == $datalist['is_publish'] ? "selected=selected" : '' }} value="{{ $row->id }}">
												{{ $row->status }}
											</option>
										@endforeach
										</select>
									</div>
								</div>
								<div class="col-md-3 d-flex justify-content-end align-items-center mt-3">
									<div class="form-group">
										<button type="button" id="addTaxSlab" class="btn blue-btn">
											+ Add Category Tax
										</button>
									</div>
								</div>
							</div>
							<div id="taxSlabContainer"></div>
							<!-- Hidden Slab Template -->
							<div id="taxSlabTemplate" style="display:none;">
								<div class="row tax-slab mt-15">

									<div class="col-md-3">
										<div class="form-group">
											<label>{{ __('Percentage') }} %<span class="red">*</span></label>
											<input type="number"
												name="slabs[__INDEX__][percentage]"
												class="form-control"
												required>
										</div>
									</div>

									<div class="col-md-3">
										<div class="form-group">
											<label>{{ __('Status') }}<span class="red">*</span></label>
											<select name="slabs[__INDEX__][is_publish]"
													class="form-control slab-status">
												@foreach($statuslist as $row)
													<option value="{{ $row->id }}">{{ $row->status }}</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="col-md-4">
										<div class="form-group">
											<label>{{ __('Category') }}<span class="red">*</span></label>
											<select name="slabs[__INDEX__][category][]"
													class="form-control slab-category"
													multiple required>
												@foreach($categories as $cat)
													<option value="{{ $cat->id }}">{{ $cat->name }}</option>
												@endforeach
											</select>
										</div>
									</div>

									<div class="col-md-2 mt-4">
										<label>&nbsp;</label>
										<button type="button" class="btn btn-danger removeSlab">
											{{ __('Delete') }}
										</button>
									</div>

								</div>
							</div>
							<div class="row tabs-footer mt-15">
								<div class="col-lg-12">
									<a id="submit-form" href="javascript:void(0);" class="btn blue-btn mr-10">{{ __('Save') }}</a>
								</div>
							</div>
						</form>
					</div>
					<!--/Data Entry Form/-->
				</div>
			</div>
		</div>
		@endif
	</div>
</div>
<!-- /main Section -->
@endsection

@push('scripts')
<!-- css/js -->
<script src="{{asset_path('backend/pages/tax.js')}}"></script>
<script>
    var existingSlabs = @json($slabs);

    if (typeof existingSlabs !== 'undefined' && existingSlabs.length > 0) {

        existingSlabs.forEach(function (slab) {

            // create slab
            var template = $('#taxSlabTemplate').html();
            template = template.replace(/__INDEX__/g, slabIndex);

            var $slab = $(template);

            // add chosen only now
            $slab.find('.slab-status, .slab-category')
                 .addClass('chosen-select');

            // set values
            $slab.find('input[name="slabs['+slabIndex+'][percentage]"]')
                 .val(slab.percentage);

            $slab.find('select[name="slabs['+slabIndex+'][is_publish]"]')
                 .val(slab.is_publish);

            // hidden id field (IMPORTANT for edit)
            $('<input>')
                .attr({
                    type: 'hidden',
                    name: 'slabs['+slabIndex+'][id]',
                    value: slab.id
                })
                .appendTo($slab);

            // append first, then chosen
            $('#taxSlabContainer').append($slab);

            // init chosen
            $slab.find('.chosen-select').chosen();

            // set category (JSON → array)
            if (slab.category) {
                var cats = JSON.parse(slab.category);
                $slab.find('select[name="slabs['+slabIndex+'][category][]"]')
                     .val(cats)
                     .trigger('chosen:updated');
            }

            slabIndex++;
        });
    }

</script>

@endpush