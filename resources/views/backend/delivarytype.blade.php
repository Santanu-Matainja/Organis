@extends('layouts.backend')

@section('title', __('Delivary Types'))

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
				<div class="card" id="list-panel">
					<div class="card-header">
						<div class="row">
							<div class="col-lg-6">
								<span>{{ __('Delivary Types') }}</span>
							</div>
							<div class="col-lg-6">
								<div class="float-right">
									<a onClick="onFormPanel()" href="javascript:void(0);" class="btn blue-btn btn-form float-right"><i class="fa fa-plus"></i> {{ __('Add New') }}</a>
								</div>
							</div>
						</div>
					</div>
					
					<!--Data grid-->
					<div class="card-body">
						<div class="row mb-10">
							<div class="col-lg-12">
								<div class="group-button">
									<button id="orderstatus_0" type="button" onclick="onDataViewByStatus(0)" class="btn btn-theme orderstatus active">All ({{ $AllCount }})</button>
									<button id="orderstatus_1" type="button" onclick="onDataViewByStatus(1)" class="btn btn-theme orderstatus">{{ __('Active') }} ({{ $ActiveCount }})</button>
									<button id="orderstatus_2" type="button" onclick="onDataViewByStatus(2)" class="btn btn-theme orderstatus">{{ __('Inactive') }} ({{ $InactiveCount }})</button>
								</div>
								<input type="hidden" id="view_by_status" value="0">
							</div>
						</div>
					
						<div class="row">
							<div class="col-lg-4">
								<div class="form-group bulk-box">
									<select id="bulk-action" class="form-control">
										<option value="">{{ __('Select Action') }}</option>
										<option value="active">{{ __('Active') }}</option>
										<option value="inactive">{{ __('Inactive') }}</option>
										<option value="delete">{{ __('Delete Permanently') }}</option>
									</select>
									<button type="submit" onClick="onBulkAction()" class="btn bulk-btn">{{ __('Apply') }}</button>
								</div>
							</div>
							<div class="col-lg-3"></div>
							<div class="col-lg-5">
								<div class="form-group search-box">
									<input id="search" name="search" type="text" class="form-control" placeholder="{{ __('Search') }}...">
									<button type="submit" onClick="onSearch()" class="btn search-btn">{{ __('Search') }}</button>
								</div>
							</div>
						</div>
						<div id="tp_datalist">
							@include('backend.partials.delivarytype_table')
						</div>
					</div>
					<!--/Data grid/-->
				</div>
				
				<div class="dnone" id="form-panel">
					<div class="row">
						<div class="col-md-9">
							<div class="card">
								<div class="card-header">
									<div class="row">
										<div class="col-lg-6">
											<span>{{ __('Delivary Partners') }}</span>
										</div>
										<div class="col-lg-6">
											<div class="float-right">
												<a onClick="onListPanel()" href="javascript:void(0);" class="btn warning-btn btn-list float-right dnone"><i class="fa fa-reply"></i> {{ __('Back to List') }}</a>
											</div>
										</div>
									</div>
								</div>
								<!--/Data Entry Form-->
								<div class="card-body">
									{{-- <a onClick="onDetailsBankInfo(1)" href="javascript:void(0);" id="details_bank_info_1" class="btn custom-btn font-bold mr-10 details_bank_info active">{{ __('Details') }}</a> --}}
						
									
									<!--Details-->
									<div class="mt-15" id="details">
										<form novalidate="" data-validate="parsley" id="DataEntry_formId">
											<div class="row">
												<!-- Label -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="label">{{ __('Lable') }} <span class="red">*</span></label>
                                                        <input type="text" name="lable" id="lable" class="form-control parsley-validated" data-required="true" placeholder="Enter label">
                                                    </div>
                                                </div>

                                                <!-- Slug -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="slug">{{ __('Slug') }} <span class="red">*</span></label> 
                                                        <input type="text" name="slug" id="slug" class="form-control parsley-validated" data-required="true" placeholder="Auto generated slug">
                                                    </div>
                                                </div>
											</div>
				
											<div class="row">
												<!-- Perishable -->
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="perisible">{{ __('Perishable') }} <span class="red">*</span></label>
                                                        <select name="perisible" id="perisible" class="form-control parsley-validated" data-required="true">
                                                            <option value="">-- Select --</option>
                                                            <option value="Yes">Yes</option>
                                                            <option value="No">No</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="shipping_fee">{{ __('Shipping Fee') }} <span class="red">*</span></label>
                                                        <input type="number" name="shipping_fee" id="shipping_fee" class="form-control parsley-validated" data-required="true" placeholder="Enter Shipping Fee">
                                                    </div>
                                                </div>

												<div class="col-md-6">
													<div class="form-group">
														<label for="status_id">{{ __('Active/Inactive') }}<span class="red">*</span></label>
														<select name="status_id" id="status_id" class="chosen-select form-control">
														@foreach($statuslist as $row)
															<option value="{{ $row->id }}">
																{{ $row->status }}
															</option>
														@endforeach
														</select>
													</div>
												</div>
												
											</div>
											
											<input type="text" id="RecordId" name="RecordId" class="dnone"/>
											
											<div class="row tabs-footer mt-15">
												<div class="col-lg-12">
													<a id="submit-form" href="javascript:void(0);" class="btn blue-btn mr-10">{{ __('Save') }}</a>
												</div>
											</div>
										</form>
									</div>
									<!--/Details/-->
									
						
								</div>
								<!--/Data Entry Form-->
							</div>
						</div>
						<div class="col-md-3">
							<div class="card mb-15">
								<div class="card-body">
									<div class="seller_card">
										<h5><strong>{{ __('Joined At') }}</strong> <span class="float-right" id="created_at"></span></h5>
										<h6><strong>{{ __('Status') }}</strong> <span id="seller_status" class="float-right"></span></h6>
									</div>
								</div>
							</div>
							
						</div>
					</div>
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
<script type="text/javascript">
var media_type = 'Thumbnail';
var TEXT = [];
	TEXT['Do you really want to edit this record'] = "{{ __('Do you really want to edit this record') }}";
	TEXT['Do you really want to delete this record'] = "{{ __('Do you really want to delete this record') }}";
	TEXT['Do you really want to active this records'] = "{{ __('Do you really want to active this records') }}";
	TEXT['Do you really want to inactive this records'] = "{{ __('Do you really want to inactive this records') }}";
	TEXT['Do you really want to delete this records'] = "{{ __('Do you really want to delete this records') }}";
	TEXT['Please select action'] = "{{ __('Please select action') }}";
	TEXT['Please select record'] = "{{ __('Please select record') }}";
	TEXT['Active'] = "{{ __('Active') }}";
	TEXT['Inactive'] = "{{ __('Inactive') }}";
</script>
<script src="{{asset('backend/pages/delivarytype.js')}}"></script>

@endpush