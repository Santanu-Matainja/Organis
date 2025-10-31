var $ = jQuery.noConflict();

$(function () {
	"use strict";

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	
	//Menu active
	$('#select_product').parent().removeClass('active');
	$('#select_product').addClass('active');
	
	$("#submit-form").on("click", function () {
        $("#DataEntry_formId").submit();
    });
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
		url: base_url + '/seller/saveInventoryData',
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
$('#sku').on('keyup', function() {
    let sku = $(this).val().trim();

    if (sku === '') {
        $('#sku-message').text('');
        return;
    }

    $.ajax({
        url: base_url + '/seller/check-sku',
        method: 'GET',
        data: { sku: sku },
        success: function(response) {
            if (response.exists) {
                $('#sku-message').text('This SKU is not available. Enter Another One.');
				$('#submit-form').addClass('disabled');
            } else {
                $('#sku-message').text('');
				$('#submit-form').removeClass('disabled');
            }
        }
    });
});
