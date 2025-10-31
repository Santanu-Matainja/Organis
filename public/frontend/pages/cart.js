var $ = jQuery.noConflict();

$(function () {
	"use strict";

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
	
	onViewCart();
	onWishlist();
	
	$(document).on("click", ".product_addtocart", function(event) {
		event.preventDefault();

		var id = $(this).data('id');
		var qty = $("#quantity").val();

		if((qty == undefined) || (qty == '') || (qty <= 0)){
			onErrorMsg(TEXT['Please enter quantity.']);
			return;
		}
		var stockqty = $(this).data('stockqty');
		if((qty > stockqty)){
			onErrorMsg(TEXT['Out Of Stock.']);
			return;
		}
		if (
            (
                (cost_price === undefined || cost_price === '' || cost_price === null || Number(cost_price) <= 0) ||
                (sale_price === undefined || sale_price === '' || sale_price === null || Number(sale_price) <= 0) ||
                (old_price === undefined || old_price === '' || old_price === null || Number(old_price) <= 0)
            )
        ) {
            onErrorMsg(TEXT['This Product Dont Have Price.']);
            return;
        }

		if(is_stock == 1){
			var stockqty = $(this).data('stockqty');
			if(is_stock_status == 1 && is_stock_status != null){
				if(qty > stockqty){
					onErrorMsg(TEXT['The value must be less than or equal to']);
					return;
				}
			}else{
				onErrorMsg(TEXT['This product out of stock.']);
				return;
			}
		}
		
		$.ajax({
			type : 'GET',
			url: base_url + '/frontend/add_to_cart/'+id+'/'+qty,
			dataType:"json",
			success: function (response) {
				var msgType = response.msgType;
				var msg = response.msg;

				if (msgType == "success") {
					onSuccessMsg(msg);
				} else {
					onErrorMsg(msg);
				}
				onViewCart();
			}
		});
    });
	
	$(document).on("click", ".product_buy_now", function(event) {
		event.preventDefault();

		var id = $(this).data('id');
		var qty = $("#quantity").val();
		
		if((qty == undefined) || (qty == '') || (qty <= 0)){
			onErrorMsg(TEXT['Please enter quantity.']);
			return;
		}
		var stockqty = $(this).data('stockqty');
		if((qty > stockqty)){
			onErrorMsg(TEXT['Out Of Stock']);
			return;
		}
		if (
            (
                (cost_price === undefined || cost_price === '' || cost_price === null || Number(cost_price) <= 0) ||
                (sale_price === undefined || sale_price === '' || sale_price === null || Number(sale_price) <= 0) ||
                (old_price === undefined || old_price === '' || old_price === null || Number(old_price) <= 0)
            )
        ) {
            onErrorMsg(TEXT['This Product Dont Have Price.']);
            return;
        }
		if(is_stock == 1){
			var stockqty = $(this).data('stockqty');
			if(is_stock_status == 1){
				if(qty > stockqty){
					onErrorMsg(TEXT['The value must be less than or equal to']);
					return;
				}
			}else{
				onErrorMsg(TEXT['This product out of stock.']);
				return;
			}
		}
		
		$.ajax({
			type : 'GET',
			url: base_url + '/frontend/add_to_cart/'+id+'/'+qty,
			dataType:"json",
			success: function (response) {
				var msgType = response.msgType;
				var msg = response.msg;

				if (msgType == "success") {
					// onSuccessMsg(msg);
					window.location.href = base_url + '/checkout';
				} else {
					onErrorMsg(msg);
				}
				onViewCart();
			}
		});
    });
	
	$(document).on("click", ".addtocart", function(event) {
		event.preventDefault();
		
		var id = $(this).data('id');
		var qty = 1;
		var cost_price = $(this).data('costprice');
		var sale_price = $(this).data('saleprice');
		var old_price = $(this).data('oldprice');
		var stockqty = $(this).data('stockqty');
		var is_stock = $(this).data('isstock');
		var is_stock_status = $(this).data('isstockstatus');
		
		if((qty > stockqty)){
			onErrorMsg(TEXT['This product out of stock.']);
			return;
		}
		if (
            (
                (cost_price === undefined || cost_price === '' || cost_price === null || Number(cost_price) <= 0) ||
                (sale_price === undefined || sale_price === '' || sale_price === null || Number(sale_price) <= 0) ||
                (old_price === undefined || old_price === '' || old_price === null || Number(old_price) <= 0)
            )
        ) {
            onErrorMsg(TEXT['This Product Dont Have Price.']);
            return;
        }
		if(is_stock == 1){
			var stockqty = $(this).data('stockqty');
			if(is_stock_status == 1){
				if(qty > stockqty){
					onErrorMsg(TEXT['The value must be less than or equal to']);
					return;
				}
			}else{
				onErrorMsg(TEXT['This product out of stock.']);
				return;
			}
		}
		$.ajax({
			type : 'GET',
			url: base_url + '/frontend/add_to_cart/'+id+'/'+qty,
			dataType:"json",
			success: function (response) {
				var msgType = response.msgType;
				var msg = response.msg;

				if (msgType == "success") {
					onSuccessMsg(msg);
				} else {
					onErrorMsg(msg);
				}
				onViewCart();
			}
		});
    });	
	
	$(document).on("click", ".addtowishlist", function(event) {
		event.preventDefault();
		
		var id = $(this).data('id');

		$.ajax({
			type : 'GET',
			url: base_url + '/frontend/add_to_wishlist/'+id,
			dataType:"json",
			success: function (response) {
				var msgType = response.msgType;
				var msg = response.msg;

				if (msgType == "success") {
					onSuccessMsg(msg);
				} else {
					onErrorMsg(msg);
				}
				onWishlist();
			}
		});
    });	
});

function onViewCart() {

    $.ajax({
		type : 'GET',
		url: base_url + '/frontend/view_cart',
		dataType:"json",
		success: function (data) {
			if(data.items == ''){
				$(".has_item_empty").show();
				$(".has_cart_item").hide();
				$(".total_qty").text(0);
			}else{
				$(".has_item_empty").hide();
				$(".has_cart_item").show();
				
				$('#tp_cart_data').html(data.items);
				$('#tp_cart_data_for_mobile').html(data.items);
				
				$(".total_qty").text(data.total_qty);
				$(".sub_total").text(data.sub_total);
				$(".tax").text(data.tax);
				$(".tp_total").text(data.total);
			}
		}
	});
}

function onRemoveToCart(id) {
	var rowid = $("#removetocart_"+id).data('id');

	$.ajax({
		type : 'GET',
		url: base_url + '/frontend/remove_to_cart/'+rowid,
		dataType:"json",
		success: function (response) {
			
			var msgType = response.msgType;
			var msg = response.msg;

			if (msgType == "success") {
				onSuccessMsg(msg);
			} else {
				onErrorMsg(msg);
			}
			
			onViewCart();
		}
	});
}

function onWishlist() {

    $.ajax({
		type : 'GET',
		url: base_url + '/frontend/count_wishlist',
		dataType:"json",
		success: function (data) {
			$(".count_wishlist").text(data);
		}
	});
}
