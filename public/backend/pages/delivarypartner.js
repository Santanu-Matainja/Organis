var $ = jQuery.noConflict();
var RecordId = '';
var BulkAction = '';
var ids = [];

$(function () {
	"use strict";

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	resetForm("DataEntry_formId");
	
	
	$("#submit-form").on("click", function () {
        $("#DataEntry_formId").submit();
    });
	
	
	$(document).on('click', '.users_pagination nav ul.pagination a', function(event){
		event.preventDefault(); 
		var page = $(this).attr('href').split('page=')[1];
		onPaginationDataLoad(page);
	});
		
	$('input:checkbox').prop('checked',false);
	
    $(".checkAll").on("click", function () {
        $("input:checkbox").not(this).prop("checked", this.checked);
    });

	$("#status_id").chosen();
	$("#status_id").trigger("chosen:updated");
	
	$('.toggle-password').on('click', function() {
		$(this).toggleClass('fa-eye-slash');
			let input = $($(this).attr('toggle'));
		if (input.attr('type') == 'password') {
			input.attr('type', 'text');
		}else {
			input.attr('type', 'password');
		}
	});
	
	$("#on_thumbnail").on("click", function () {
		onGlobalMediaModalView();
    });
	
	$("#media_select_file").on("click", function () {	
		var thumbnail = $("#thumbnail").val();

		if(thumbnail !=''){
			$("#photo_thumbnail").val(thumbnail);
			$("#view_photo_thumbnail").html('<img src="'+'/media/'+thumbnail+'">');
		}

		$("#remove_photo_thumbnail").show();
		$('#global_media_modal_view').modal('hide');
    });
	
	$("#view_by_status").val(0);
	
});

function onCheckAll() {
    $(".checkAll").on("click", function () {
        $("input:checkbox").not(this).prop("checked", this.checked);
    });
}

function onPaginationDataLoad(page) {
	$.ajax({
		url:base_url + "/backend/getDelivaryPartnersTableData?page="+page+"&search="+$("#search").val()+"&status="+$("#view_by_status").val(),
		success:function(data){
			$('#tp_datalist').html(data);
			onCheckAll();
		}
	});
}

function onRefreshData() {
	$.ajax({
		url:base_url + "/backend/getDelivaryPartnersTableData?search="+$("#search").val()+"&status="+$("#view_by_status").val(),
		success:function(data){
			$('#tp_datalist').html(data);
			onCheckAll();
		}
	});
}

function onSearch() {

	$.ajax({
		url: base_url + "/backend/getDelivaryPartnersTableData?search="+$("#search").val()+"&status="+$("#view_by_status").val(),
		success:function(data){
			$('#tp_datalist').html(data);
			onCheckAll();
		}
	});
}

function onDataViewByStatus(status) {

	$("#view_by_status").val(status);
	
	$(".orderstatus").removeClass('active')
	$("#orderstatus_"+status).addClass('active');
	
	$.ajax({
		url: base_url + "/backend/getDelivaryPartnersTableData?status="+$("#view_by_status").val()+"&search="+$("#search").val(),
		success:function(data){
			$('#tp_datalist').html(data);
			onCheckAll();
		}
	});
}

function resetForm(id) {
    $('#' + id).each(function () {
        this.reset();
    });
	
	$("#status_id").trigger("chosen:updated");
}

function onListPanel() {
	$('.parsley-error-list').hide();
    $('#list-panel, .btn-form').show();
    $('#form-panel, .btn-list').hide();
}

function onFormPanel() {

    resetForm("DataEntry_formId");
    
	RecordId = '';
	
	$("#status_id").trigger("chosen:updated");
	
	$("#remove_photo_thumbnail").hide();
	$("#photo_thumbnail").html('');
	
    $('#list-panel, .btn-form').hide();
    $('#form-panel, .btn-list').show();

}

function onEditPanel() {
    $('#list-panel, .btn-form').hide();
    $('#form-panel, .btn-list').show();
	
	$("#details_bank_info_2").show();
	$(".error_available").html('');
}

function onMediaImageRemove(type) {
	$('#photo_thumbnail').val('');
	$("#remove_photo_thumbnail").hide();
}



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
		url: base_url + '/backend/saveDelivaryPartnersData',
		data: $('#DataEntry_formId').serialize(),
		success: function (response) {			
			var msgType = response.msgType;
			var msg = response.msg;
			var id = response.id;
			$("#RecordId").val(id);
			if (msgType == "success") {
				
				if(RecordId == ''){
					RecordId = id;
		
				}
				onRefreshData();
				onSuccessMsg(msg);

			} else {
				onErrorMsg(msg);
			}
			
			onCheckAll();
		}
	});
}


function onEdit(id) {
	RecordId = id;
	var msg = TEXT["Do you really want to edit this record"];
	onCustomModal(msg, "onLoadEditData");	
}

function onLoadEditData() {

    $.ajax({
		type : 'POST',
		url: base_url + '/backend/getDelivaryPatnersById',
		data: 'id='+RecordId,
		success: function (response) {

			var delivarypartners_data = response.delivarypartners_data;

			$("#RecordId").val(delivarypartners_data.id);
			$("#name").val(delivarypartners_data.name);
			$("#email").val(delivarypartners_data.email);
			$("#phone").val(delivarypartners_data.phone);
			$("#address").val(delivarypartners_data.address);
			$("#city").val(delivarypartners_data.city);
			$("#state").val(delivarypartners_data.state);
			$("#zip_code").val(delivarypartners_data.zip_code);
			$("#country_id").val(delivarypartners_data.country_id).trigger("chosen:updated");
			$("#status_id").val(delivarypartners_data.status_id).trigger("chosen:updated");
			$("#delivery_range").val(delivarypartners_data.delivery_range);
			$("#vehicle_type").val(delivarypartners_data.vehicle_type);
			$("#license_number").val(delivarypartners_data.license_number);
 			
			if(delivarypartners_data.photo != null){
				$("#photo_thumbnail").val(delivarypartners_data.photo);
				$("#view_photo_thumbnail").html('<img src="'+'/media/'+delivarypartners_data.photo+'">');
				$("#remove_photo_thumbnail").show();
			}else{
				$("#photo_thumbnail").val('');
				$("#view_photo_thumbnail").html('');
				$("#remove_photo_thumbnail").hide();
			}
			
			if(delivarypartners_data.status_id == 1){
				$("#seller_status").removeClass("inactive").addClass("active");
				$("#seller_status").text(TEXT['Active']);
			}else{
				$("#seller_status").removeClass("active").addClass("inactive");
				$("#seller_status").text(TEXT['Inactive']);
			}

			
			$("#created_at").text(delivarypartners_data.created_at);
			
			
			onEditPanel();
		}
    });
}

function onDelete(id) {
	RecordId = id;
	var msg = TEXT["Do you really want to delete this record"];
	onCustomModal(msg, "onConfirmDelete");	
}

function onConfirmDelete() {

    $.ajax({
		type : 'POST',
		url: base_url + '/backend/deletedelivarypartners',
		data: 'id='+RecordId,
		success: function (response) {
			var msgType = response.msgType;
			var msg = response.msg;

			if(msgType == "success"){
				onSuccessMsg(msg);
				onRefreshData();
			}else{
				onErrorMsg(msg);
			}
			
			onCheckAll();
		}
    });
}

function onBulkAction() {
	ids = [];
	$('.selected_item:checked').each(function(){
		ids.push($(this).val());
	});

	if(ids.length == 0){
		var msg = TEXT["Please select record"];
		onErrorMsg(msg);
		return;
	}
	
	BulkAction = $("#bulk-action").val();
	if(BulkAction == ''){
		var msg = TEXT["Please select action"];
		onErrorMsg(msg);
		return;
	}
	
	if(BulkAction == 'active'){
		var msg = TEXT["Do you really want to active this records"];
	}else if(BulkAction == 'inactive'){
		var msg = TEXT["Do you really want to inactive this records"];
	}else if(BulkAction == 'delete'){
		var msg = TEXT["Do you really want to delete this records"];
	}
	
	onCustomModal(msg, "onConfirmBulkAction");	
}

function onConfirmBulkAction() {

    $.ajax({
		type : 'POST',
		url: base_url + '/backend/bulkActiondelivarypartners',
		data: 'ids='+ids+'&BulkAction='+BulkAction,
		success: function (response) {
			var msgType = response.msgType;
			var msg = response.msg;

			if(msgType == "success"){
				onSuccessMsg(msg);
				onRefreshData();
				ids = [];
			}else{
				onErrorMsg(msg);
			}
			
			onCheckAll();
		}
    });
}

