var $ = jQuery.noConflict();
var payment_method = 1;

$(function () {
	"use strict";

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	
    $("#new_account").on("click", function () {
		if($(this).is(":checked")){
			$("#new_account_pass").removeClass("hideclass");
			$("#password").attr("required", "");
			$("#password_confirmation").attr("required", "");
		}else if($(this).is(":not(:checked)")){
			$("#new_account_pass").addClass("hideclass");
			$("#password").removeAttr("required");
			$("#password_confirmation").removeAttr("required");
		}
    });
	
    $("#payment_method_stripe").on("click", function () {
		$("#pay_paypal").addClass("hideclass");
		$("#pay_razorpay").addClass("hideclass");
		$("#pay_mollie").addClass("hideclass");
		$("#pay_cod").addClass("hideclass");
		$("#pay_bank").addClass("hideclass");
		$("#pay_stripe").removeClass("hideclass");
    });
	
    $("#payment_method_paypal").on("click", function () {
		$("#pay_stripe").addClass("hideclass");
		$("#pay_razorpay").addClass("hideclass");
		$("#pay_mollie").addClass("hideclass");
		$("#pay_cod").addClass("hideclass");
		$("#pay_bank").addClass("hideclass");
		$("#pay_paypal").removeClass("hideclass");
    });
	
    $("#payment_method_razorpay").on("click", function () {
		$("#pay_stripe").addClass("hideclass");
		$("#pay_paypal").addClass("hideclass");
		$("#pay_mollie").addClass("hideclass");
		$("#pay_cod").addClass("hideclass");
		$("#pay_bank").addClass("hideclass");
		$("#pay_razorpay").removeClass("hideclass");
    });
	
    $("#payment_method_mollie").on("click", function () {
		$("#pay_stripe").addClass("hideclass");
		$("#pay_paypal").addClass("hideclass");
		$("#pay_razorpay").addClass("hideclass");
		$("#pay_cod").addClass("hideclass");
		$("#pay_bank").addClass("hideclass");
		$("#pay_mollie").removeClass("hideclass");
    });
	
    $("#payment_method_cod").on("click", function () {
		$("#pay_stripe").addClass("hideclass");
		$("#pay_paypal").addClass("hideclass");
		$("#pay_razorpay").addClass("hideclass");
		$("#pay_mollie").addClass("hideclass");
		$("#pay_bank").addClass("hideclass");
		$("#pay_cod").removeClass("hideclass");
    });
	
    $("#payment_method_bank").on("click", function () {
		$("#pay_stripe").addClass("hideclass");
		$("#pay_paypal").addClass("hideclass");
		$("#pay_razorpay").addClass("hideclass");
		$("#pay_mollie").addClass("hideclass");
		$("#pay_cod").addClass("hideclass");
		$("#pay_bank").removeClass("hideclass");
    });
	
    // $(".shipping_method").on("click", function () {
	// 	var totalWithComma = $(this).data('total');
	// 	var shipping_fee = $(this).data('shippingfee');
	// 	var seller_count = $(this).data('seller_count');
	// 	var shippingfee = shipping_fee*seller_count;
		
	// 	var total = removeCommas(totalWithComma);
		
	// 	var TotalShippingfee = addCommas(parseFloat(total) + parseFloat(shippingfee));
		
	// 	$(".shipping_fee").text(shippingfee);
	// 	$(".total_amount").text(TotalShippingfee);
    // });
	// Trigger shipping fee calculation on page load
    // $(".shipping_method:checked").trigger("click");

	// $(".shipping_method").on("click", function () {
	// 	var shipping_fee = parseFloat($(this).data('shippingfee')) || 0;
	// 	var totalWithoutShipping = parseFloat(removeCommas($(this).data('total'))) || 0;

	// 	var productRow = $(this).closest("tr").prevAll("tr").has(".total-price").first();

	// 	productRow.find(".shipping_fee").text("€" + addCommas(shipping_fee.toFixed(2)));

	// 	var newTotal = totalWithoutShipping + shipping_fee;
	// 	productRow.find(".total-price").text("€" + addCommas(newTotal.toFixed(2)));

	// 	var grandTotal = 0;
	// 	$(".total-price").each(function () {
	// 		var val = parseFloat(removeCommas($(this).text().replace('€',''))) || 0;
	// 		grandTotal += val;
	// 	});
	// 	$(".grand_total_value").text(addCommas(grandTotal.toFixed(2)));
	// });
	$('.shipping_method:checked').each(function () {
        let sellerId = $(this).data('sellerid');
        let shippingId = $(this).data('shipid');

        $('input[name="shipping_id['+sellerId+']"]').val(shippingId);
    });
	$(".shipping_method").on("change", function () {
		const shipping_fee = parseFloat($(this).data("shippingfee")) || 0;
		const totalWithoutShipping = parseFloat($(this).data("total")) || 0;

		const shippingSection = $(this).closest("tr");
		const totalRow = shippingSection.prevAll("tr").has(".total-price").first();
		const shippingRow = shippingSection.prevAll("tr").has(".shipping_fee").first();

		const sellerId = $(this).data("sellerid");
    	const shippingId = $(this).data("shipid");

		// UPDATE THE ONE HIDDEN FIELD
		$("#shipping_id_" + sellerId).val(shippingId);
		// Update shipping and total
		shippingRow.find(".shipping_fee").text("€" + formatCurrency(shipping_fee));
		const newTotal = totalWithoutShipping + shipping_fee;
		totalRow.find(".total-price").text("€" + formatCurrency(newTotal));

		// Recalculate grand total
		let grandTotal = 0;
		$(".total-price").each(function () {
			const val = parseCurrency($(this).text());
			grandTotal += val;
		});
		let commission = parseFloat($("#commission").val()) || 0;
		$(".grand_total_value").text(formatCurrency(grandTotal + commission));
	});

	function parseCurrency(value) {
		value = value.replace(/[^\d,.-]/g, '')
					.replace(/\./g, '')
					.replace(',', '.');
		return parseFloat(value) || 0;
	}

	function formatCurrency(value) {
		return new Intl.NumberFormat('de-DE', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		}).format(value);
	}








	$("#checkout_submit_form").on("click", function () {
		payment_method = $('input[name="payment_method"]:checked').val();
        $("#checkout_formid").submit();
    });
});

function addCommas(nStr){
    nStr += '';
    var x = nStr.split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

function removeCommas(nStr){

	if(typeof nStr === 'string'){
		var num = nStr.replace(/,/g, "");
	}else{
		var num = nStr;
	}
	
    return num;
}

function showPerslyError() {
    $('.parsley-error-list').show();
}

jQuery('#checkout_formid').parsley({
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
				if(payment_method == 5){
					onPaymentByRazorpay();
				}else{
					onConfirmMakeOrder();
				}
				
                return false;
            }
        }
    }
});

function onPaymentByRazorpay() {
	var sVal = $(".total_amount").text();
	var totalVal = removeCommas(sVal);
	var amount = parseFloat(totalVal);
	var razorpayAmount = amount.toFixed(2) * 100;

	var	options = {
			"key": razorpay_key_id,
			"amount": razorpayAmount,
			"currency": razorpay_currency,
			"name": site_name, //merchant user name
			"handler": function(response) {
				var razorpay_payment_id = response.razorpay_payment_id;
				$("#razorpay_payment_id").val(razorpay_payment_id);
				if(razorpay_payment_id != ''){
					onConfirmMakeOrder();
				}
			},
			"prefill": {
				"name": $("#name").val(), //user name
				"email": $("#email").val(), //user email
			},
			"theme": {
				"color": theme_color,
			},
			"modal": {
				"ondismiss": function(e) {}
			}
		};
	var rzp1 = new Razorpay(options);
	rzp1.open();		
}

function onConfirmMakeOrder() {

	var payment_method = $('input[name="payment_method"]:checked').val();

 	if(payment_method == 3){
		if(isenable_stripe == 1){
			if(validCardNumer == 0){
				$("span.payment_method_error").text(TEXT['Please type valid card number']);
				return;
			}
		}
	}else{
		$("span.payment_method_error").text('');
	}
	
	var checkout_btn = $('.checkout_btn').html();
	
    $.ajax({
		type : 'POST',
		url: base_url + '/frontend/make_order',
		data: $('#checkout_formid').serialize(),
		beforeSend: function() {
			$('.checkout_btn').html('<span class="spinner-border spinner-border-sm"></span> Please Wait...');
		},
		success: function (response) {		
			var msgType = response.msgType;
			var msg = response.msg;

			if (msgType == "success") {
				$("#checkout_formid").find('span.error-text').text('');
				
				//Stripe
				if(payment_method == 3){
					if(isenable_stripe == 1){
						if(response.intent != ''){
							onConfirmPayment(response.intent, msg);
						}
					}
				
				//Paypal
				}else if(payment_method == 4){
					if(response.intent != ''){
						window.location.href = response.intent;
					}
				
				//Mollie
				}else if(payment_method == 6){
					if(response.intent != ''){
						window.location.href = response.intent;
					}
				}else{
					//onSuccessMsg(msg);
					window.location.href = base_url + '/thank';
				}

			} else {
				$.each(msg, function(prefix, val){
					if(prefix == 'oneError'){
						onErrorMsg(val[0]);
					}else{
						$('span.'+prefix+'_error').text(val[0]);
					}
				});
			}
			
			$('.checkout_btn').html(checkout_btn);
		}
	});
}