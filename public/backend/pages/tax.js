var $ = jQuery.noConflict();

$(function () {
	"use strict";

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#submit-form").on("click", function () {
        $("#DataEntry_formId").submit();
    });

	$("#is_publish").chosen();
	$("#is_publish").trigger("chosen:updated");
});

function showPerslyError() {
    $('.parsley-error-list').show();
}

jQuery('#DataEntry_formId').parsley({
    listeners: {
        onFieldValidate: function (elem) {
            if (!$(elem).is(':visible')) {
                return true;
            }
            else {
                showPerslyError();
                return false;
            }
        },
        onFormSubmit: function (isFormValid, event) {
            if (isFormValid) {
                onConfirmWhenAddEdit();
                return false;
            }
        }
    }
});

function onConfirmWhenAddEdit() {

    $.ajax({
		type : 'POST',
		url: base_url + '/backend/saveTaxData',
		data: $('#DataEntry_formId').serialize(),
		success: function (response) {			
			var msgType = response.msgType;
			var msg = response.msg;

			if (msgType == "success") {
				onSuccessMsg(msg);
			} else {
				onErrorMsg(msg);
			}
		}
	});
}

var slabIndex = 0;

$('#addTaxSlab').on('click', function () {

    var template = $('#taxSlabTemplate').html();
    template = template.replace(/__INDEX__/g, slabIndex);

    var $slab = $(template);

    // add chosen-select ONLY now
    $slab.find('.slab-status, .slab-category')
         .addClass('chosen-select');

    $('#taxSlabContainer').append($slab);

    // initialize chosen ONLY for this slab
    $slab.find('.chosen-select').chosen();

    slabIndex++;
});

$(document).on('click', '.removeSlab', function () {
    $(this).closest('.tax-slab').remove();
});