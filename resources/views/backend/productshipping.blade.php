@extends('layouts.backend')

@section('title', __('Shipping'))

@section('content')

<style>
.form-group {
margin-bottom: 0rem !important;
}
.delivery-type-row {
    border-radius: 8px;
    padding: 0px 0px 0px 15px;
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.delivery-type-row .form-group label {
    font-weight: 600;
    color: #444;
}

.delivery-type-row .form-control {
    border-radius: 6px;
}

.removeDeliveryTypeBtn {
    background-color: #e74c3c !important;
    border-color: #e74c3c !important;
    color: #fff !important;
    padding: 6px 14px;
    margin-top: 25px;
}

#addDeliveryTypeBtn {
    margin-top: 35px;
}

#deliveryTypeContainer {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 15px;
}

.delivery-item {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    flex: 0 0 auto;
}

.delivery-item .form-group {
    width: 250px;
    min-width: 250px;
    max-width: 250px;
}

.delivery-item .form-group .chosen-container {
    width: 250px !important;
    min-width: 250px !important;
    max-width: 250px !important;
}

.delivery-item .form-group .form-control {
    width: 250px !important;
}

#buttonWrapper {
    display: flex;
    align-items: center;
    flex: 0 0 auto;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .delivery-item .form-group {
        width: 220px;
        min-width: 220px;
        max-width: 220px;
    }
    
    .delivery-item .form-group .chosen-container {
        width: 220px !important;
        min-width: 220px !important;
        max-width: 220px !important;
    }
    
    .delivery-item .form-group .form-control {
        width: 220px !important;
    }
}

@media (max-width: 992px) {
    .delivery-item .form-group {
        width: 200px;
        min-width: 200px;
        max-width: 200px;
    }
    
    .delivery-item .form-group .chosen-container {
        width: 200px !important;
        min-width: 200px !important;
        max-width: 200px !important;
    }
    
    .delivery-item .form-group .form-control {
        width: 200px !important;
    }
}

@media (max-width: 768px) {
    #deliveryTypeContainer {
        flex-direction: column;
    }
    
    .delivery-item {
        width: 100%;
        flex-wrap: wrap;
    }
    
    .delivery-item .form-group {
        width: 100%;
        min-width: 100%;
        max-width: 100%;
    }
    
    .delivery-item .form-group .chosen-container {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
    }
    
    .delivery-item .form-group .form-control {
        width: 100% !important;
    }
    
    .removeDeliveryTypeBtn {
        margin-top: 10px;
    }
    
    #addDeliveryTypeBtn {
        margin-top: 10px;
        width: 100%;
    }
}

@media (max-width: 576px) {
    .removeDeliveryTypeBtn {
        width: 100%;
    }
}
</style>

<!-- main Section -->
<div class="main-body">
	<div class="container-fluid">
		@php $vipc = vipc(); @endphp
		@if($vipc['bkey'] == 0) 
		@include('seller.partials.vipc')
		@else
		<div class="row mt-25">
			<div class="col-lg-12">
				<div class="card">
					<div class="card-header">
						<div class="row">
							<div class="col-lg-6">
								{{ __('Shipping') }}
							</div>
							<div class="col-lg-6">
								<div class="float-right">
									<a href="{{ route('seller.products') }}" class="btn warning-btn"><i class="fa fa-reply"></i> {{ __('Back to List') }}</a>
								</div>
							</div>
						</div>
					</div>
					<div class="card-body tabs-area p-0">
						@include('seller.partials.product_tabs_nav')
						<div class="tabs-body">
							<!--Data Entry Form-->
							<form novalidate="" data-validate="parsley" id="DataEntry_formId">
                                
                                <div class="row">
                                    <div class="col-lg-4">
										<div class="form-check mt-4">
											<input value="1"  name="perisible" id="perisible" type="checkbox" class="form-check-input parsley-validated" {{ old('perisible', $datalist['perisible']) ? 'checked' : '' }}  style="height: 50px;width: 18px;">
											<label for="perisible"  style="margin: 18px 7px;">{{ __('Perisible') }}</label>
										</div>
									</div>
                                </div>
								<div id="deliveryTypeContainer"> 
                                    <div class="delivery-item">
                                        <div class="form-group"> 
                                            <label for="delivarytypeid">Delivery Type<span class="red">*</span></label> 
                                            <select name="delivarytypeid[]" id="delivarytypeid" class="chosen-select form-control delivarytypeid"> 
                                                <option value="">-- Select --</option> 
                                                @foreach($delivarytypes as $row) 
                                                <option value="{{ $row->id }}">{{ $row->lable }}</option>
                                                @endforeach 
                                            </select> 
                                        </div>
                                    </div>
                                    <div id="buttonWrapper"> 
                                        <button type="button" id="addDeliveryTypeBtn" class="btn btn-primary" style="display: none;">+ Add Delivery Type</button> 
                                    </div> 
                                </div>

                                <hr>
                                <p class="text-info"><i class="fa fa-exclamation-circle mr-1" aria-hidden="true"></i>Your Maximum Order Quantity is {{$datalist['maxorderqty']}}, Set The Highest Quantity To {{$datalist['maxorderqty']}}</p>
                                    @php
                                        $slabs = json_decode($shippingMethod->slab ?? '[]', true);
                                    @endphp

                                    <div id="slabContainer">
                                        @foreach($slabs as $key => $slab)
                                            <div class="row slab-row mb-2">
                                                <div class="col-md-3">
                                                    <label>Lowest Quantity<span class="red">*</span></label> 
                                                    <input type="number" name="slabs[{{ $key }}][min_qty]" class="form-control min-qty" value="{{ $slab['min_qty'] }}" readonly>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Highest Quantity<span class="red">*</span></label> 
                                                    <input type="number" name="slabs[{{ $key }}][max_qty]" class="form-control max-qty" value="{{ $slab['max_qty'] }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Price<span class="red">*</span></label> 
                                                    <input type="number" name="slabs[{{ $key }}][price]" class="form-control price" value="{{ $slab['price'] }}">
                                                </div>
                                                <div class="col-md-3 mt-4">
                                                    @if($loop->last)
                                                        <button type="button" id="addSlab" class="btn btn-success">Add Slab</button>
                                                    @else
                                                        <button type="button" class="btn btn-danger removeSlab">Remove</button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                        @if(empty($slabs))
                                            <div class="row slab-row mb-2">
                                                <div class="col-md-3">
                                                    <label>Lowest Quantity<span class="red">*</span></label> 
                                                    <input type="number" name="slabs[0][min_qty]" class="form-control min-qty" value="1" readonly>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Highest Quantity<span class="red">*</span></label> 
                                                    <input type="number" name="slabs[0][max_qty]" class="form-control max-qty" placeholder="Highest Quantity">
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Price<span class="red">*</span></label> 
                                                    <input type="number" name="slabs[0][price]" class="form-control price" placeholder="Price">
                                                </div>
                                                <div class="col-md-3 mt-4">
                                                    <button type="button" id="addSlab" class="btn btn-success">Add Slab</button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>


								<input value="{{ $datalist['id'] }}" type="text" name="RecordId" id="RecordId" class="dnone">
								<div class="row tabs-footer mt-15">
									<div class="col-lg-12">
										<a id="submit-form" href="javascript:void(0);" class="btn blue-btn">{{ __('Save') }}</a>
									</div>
								</div>
							</form>
							<!--/Data Entry Form/-->
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
<script src="{{asset_path('backend/pages/backend_shipping.js')}}"></script>
<script>
$(document).ready(function () {
    const $container = $('#deliveryTypeContainer');
    const $buttonWrapper = $('#buttonWrapper');
    const allOptions = @json($delivarytypes);
    const selectedData = @json($selectedDeliveryTypes ?? []);
    const $perishable = $('#perisible');

    // Helper: get used values
    const usedValues = () =>
        $container.find('.delivarytypeid').map(function () {
            return $(this).val();
        }).get().filter(Boolean);

    const limit = () => ($perishable.is(':checked') ? 2 : 3);

    function toggleOpt4($select) {
        const $opt4 = $select.find('option[value="4"]');
        if ($perishable.is(':checked')) {
            $opt4.hide();
            if ($select.val() === '4') $select.val('');
        } else {
            $opt4.show();
        }
        $select.trigger('chosen:updated');
    }

    function refreshDropdowns() {
        const used = usedValues();
        $container.find('.delivarytypeid').each(function () {
            const current = $(this).val();
            const html = allOptions
                .map(opt =>
                    used.includes(opt.id.toString()) && opt.id.toString() !== current
                        ? ''
                        : `<option value="${opt.id}" ${opt.id == current ? 'selected' : ''}>${opt.lable}</option>`
                )
                .join('');
            $(this).html(`<option value="">-- Select --</option>${html}`);
            toggleOpt4($(this));
        });
    }

    function updateButton() {
        const used = usedValues();
        const totalDropdowns = $container.find('.delivery-item').length;
        const selectedCount = used.length;
        const maxLimit = limit();
        const canAdd = selectedCount > 0 && selectedCount === totalDropdowns && totalDropdowns < maxLimit;
        $('#addDeliveryTypeBtn').toggle(canAdd);
    }

    function addRow(selectedId = '', hideRemove = false) {
        const used = usedValues();
        const opts = allOptions
            .map(opt =>
                used.includes(opt.id.toString()) && opt.id.toString() !== selectedId
                    ? ''
                    : `<option value="${opt.id}" ${opt.id == selectedId ? 'selected' : ''}>${opt.lable}</option>`
            )
            .join('');

        const removeBtn = hideRemove
            ? ''
            : `<button type="button" class="btn btn-danger removeDeliveryTypeBtn" style="margin-top:25px;">Remove</button>`;

        const $item = $(`
            <div class="delivery-item d-flex align-items-end gap-2" style="margin-bottom:10px;">
                <div class="form-group flex-grow-1">
                    <label>Delivery Type<span class="red">*</span></label>
                    <select name="delivarytypeid[]" class="chosen-select form-control delivarytypeid">
                        <option value="">-- Select --</option>${opts}
                    </select>
                </div>
                ${removeBtn}
            </div>
        `);

        $buttonWrapper.before($item);
        $item.find('.chosen-select').chosen({ width: '100%' });
        toggleOpt4($item.find('.delivarytypeid'));
    }

    // === INITIALIZE ===
    $container.find('.delivery-item').remove(); // remove default
    if (Array.isArray(selectedData) && selectedData.length > 0) {
        let first = true;
        selectedData.forEach(selectedId => {
            addRow(selectedId, first);
            first = false;
        });
    } else {
        // default empty one if nothing selected
        addRow('', true);
    }

    refreshDropdowns();
    updateButton();

    // === EVENTS ===
    $container.on('change', '.delivarytypeid', function () {
        refreshDropdowns();
        updateButton();
    });

    $container.on('click', '.removeDeliveryTypeBtn', function () {
        $(this).closest('.delivery-item').remove();
        refreshDropdowns();
        updateButton();
    });

    $('#addDeliveryTypeBtn').on('click', function (e) {
        e.preventDefault();
        addRow();
        refreshDropdowns();
        updateButton();
    });

    $perishable.on('change', function () {
        refreshDropdowns();
        updateButton();
    });

    updateButton();
});


let slabIndex = $('#slabContainer .slab-row').length - 1;

// Add slab
$(document).on('click', '#addSlab', function() {
    const lastRow = $('#slabContainer .slab-row').last();
    const lastMaxQty = parseInt(lastRow.find('.max-qty').val());
    const price = parseInt(lastRow.find('.price').val());

    if (!lastMaxQty || lastMaxQty <= 0) {
        onErrorMsg('Please enter highest quantity.');
        return;
    }
    if (!price || price <= 0) {
        onErrorMsg('Please enter price.');
        return;
    }

    slabIndex++;

    // Replace last row's Add button with Remove
    lastRow.find('#addSlab').remove();
    if (lastRow.index() === 0) {
        lastRow.find('.col-md-3:last').html('');
    } else {
        lastRow.find('.col-md-3:last').html('<button type="button" class="btn btn-danger removeSlab">Remove</button>');
    }

    // New row
    const newRow = `
        <div class="row slab-row mb-2">
            <div class="col-md-3">
                <label>Lowest Quantity<span class="red">*</span></label>
                <input type="number" name="slabs[${slabIndex}][min_qty]" class="form-control min-qty" value="${lastMaxQty + 1}" readonly>
            </div>
            <div class="col-md-3">
                <label>Highest Quantity<span class="red">*</span></label>
                <input type="number" name="slabs[${slabIndex}][max_qty]" class="form-control max-qty" placeholder="Highest Quantity">
            </div>
            <div class="col-md-3">
                <label>Price<span class="red">*</span></label>
                <input type="number" name="slabs[${slabIndex}][price]" class="form-control price" placeholder="Price">
            </div>
            <div class="col-md-3 mt-4">
                <button type="button" id="addSlab" class="btn btn-success">Add Slab</button>
            </div>
        </div>
    `;

    $('#slabContainer').append(newRow);
});

// Remove slab
$(document).on('click', '.removeSlab', function() {
    const row = $(this).closest('.slab-row');
    const wasLastRow = row.is(':last-child');

    row.remove();

    // Adjust lowest quantities after removal
    updateMinQuantities();

    // Move Add button to last row
    const newLastRow = $('#slabContainer .slab-row').last();
    if (!newLastRow.find('#addSlab').length) {
        newLastRow.find('.removeSlab').remove();
        newLastRow.find('.col-md-3:last').html('<button type="button" id="addSlab" class="btn btn-success">Add Slab</button>');
    }
});

// Validate: Highest >= Lowest
$(document).on('change', '.max-qty', function() {
    const row = $(this).closest('.slab-row');
    const min = parseInt(row.find('.min-qty').val());
    const max = parseInt($(this).val());

    if (max && min && max < min) {
        onErrorMsg('Highest quantity cannot be lower than lowest quantity.');
        $(this).val('');
    }
});

// Function to reflow lowest quantities after delete or manual change
function updateMinQuantities() {
    const rows = $('#slabContainer .slab-row');
    rows.each(function(index) {
        if (index === 0) {
            $(this).find('.min-qty').val(1);
        } else {
            const prevRow = rows.eq(index - 1);
            const prevMax = parseInt(prevRow.find('.max-qty').val());
            if (prevMax && prevMax > 0) {
                $(this).find('.min-qty').val(prevMax + 1);
            }
        }
    });
}

</script>

@endpush