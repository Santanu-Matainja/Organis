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

	$(document).on('click', '.tp_pagination nav ul.pagination a', function(event){
		event.preventDefault(); 
		var page = $(this).attr('href').split('page=')[1];
		onPaginationDataLoad(page);
	});
	
	$('input:checkbox').prop('checked',false);
	
    $(".checkAll").on("click", function () {
        $("input:checkbox").not(this).prop("checked", this.checked);
    });

	$("#is_featured").chosen();
	$("#is_featured").trigger("chosen:updated");
	
	$("#lan").chosen();
	$("#lan").trigger("chosen:updated");
	
	$("#is_publish").chosen();
	$("#is_publish").trigger("chosen:updated");
	
	$("#media_select_file").on("click", function () {
		
		var thumbnail = $("#thumbnail").val();
		if(thumbnail !=''){
			$("#brand_thumbnail").val(thumbnail);
			$("#view_thumbnail_image").html('<img src="'+public_path+'/media/'+thumbnail+'">');
		}
		
		$("#remove_thumbnail").show();
		$('#global_media_modal_view').modal('hide');
    });
	
	$("#language_code").val(0).trigger("chosen:updated");
	
	$("#language_code").on("change", function () {
		onRefreshData();
	});
});

function onCheckAll() {
    $(".checkAll").on("click", function () {
        $("input:checkbox").not(this).prop("checked", this.checked);
    });
}

function onPaginationDataLoad(page) {
	$.ajax({
		url:base_url + "/seller/getSellerBrandsTableData?page="+page
		+"&search="+$("#search").val()
		+"&language_code="+$('#language_code').val(),
		success:function(data){
			$('#tp_datalist').html(data);
			onCheckAll();
		}
	});
}

function onRefreshData() {
	$.ajax({
		url:base_url + "/seller/getSellerBrandsTableData?search="+$("#search").val()
		+"&language_code="+$('#language_code').val(),
		success:function(data){
			$('#tp_datalist').html(data);
			onCheckAll();
		}
	});
}

function onSearch() {
	$.ajax({
		url: base_url + "/seller/getSellerBrandsTableData?search="+$("#search").val()
		+"&language_code="+$('#language_code').val(),
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
	
	$("#is_featured").trigger("chosen:updated");
	$("#lan").trigger("chosen:updated");
	$("#is_publish").trigger("chosen:updated");
}

function onListPanel() {
	$('.parsley-error-list').hide();
    $('#list-panel, .btn-form').show();
    $('#form-panel, .btn-list').hide();
}

function onFormPanel() {
    resetForm("DataEntry_formId");
	RecordId = '';
	
	$("#remove_thumbnail").hide();
	$("#brand_thumbnail").html('');
	
	$("#is_featured").trigger("chosen:updated");
	$("#lan").trigger("chosen:updated");
	$("#is_publish").trigger("chosen:updated");
	
    $('#list-panel, .btn-form').hide();
    $('#form-panel, .btn-list').show();
}

function onEditPanel() {
    $('#list-panel, .btn-form').hide();
    $('#form-panel, .btn-list').show();	
}

// function onMediaImageRemove(type) {
//     $('#brand_thumbnail').val('');
// 	$("#remove_thumbnail").hide();
// }
function onMediaImageRemove(type) {
    const fileName = $('#' + type).val();
    if (!fileName) return;

    $.ajax({
        url: base_url + '/seller/delete-media-file',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            file_name: fileName
        },
        success: function (res) {
            $('#' + type).val('');
            $("#view_thumbnail_image").html('');
            $("#remove_thumbnail").hide();
        },
        error: function () {
            onErrorMsg('Failed to delete image.');
        }
    });
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

	var formData = new FormData($('#DataEntry_formId')[0]);
	formData.append('media_type', media_type);
    $.ajax({
		type : 'POST',
		url: base_url + '/seller/saveSellerBrandsData',
		data: formData,
		processData: false,  // important
    	contentType: false,  // important
		success: function (response) {			
			var msgType = response.msgType;
			var msg = response.msg;

			if (msgType == "success") {
				resetForm("DataEntry_formId");
				onRefreshData();
				onSuccessMsg(msg);
				onListPanel();
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
		url: base_url + '/seller/getSellerBrandsById',
		data: 'id='+RecordId,
		success: function (response) {
			var data = response;
			$("#RecordId").val(data.id);
			$("#name").val(data.name);
			$("#is_featured").val(data.is_featured).trigger("chosen:updated");
			$("#lan").val(data.lan).trigger("chosen:updated");
			$("#is_publish").val(data.is_publish).trigger("chosen:updated");
			
 			if(data.thumbnail != null){
				$("#brand_thumbnail").val(data.thumbnail);
				$("#view_thumbnail_image").html('<img src="'+public_path+'/media/'+data.thumbnail+'">');
				$("#remove_thumbnail").show();
			}else{
				$("#brand_thumbnail").val('');
				$("#view_thumbnail_image").html('');
				$("#remove_thumbnail").hide();
			}
			
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
		url: base_url + '/seller/deleteSellerBrands',
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
	
	if(BulkAction == 'publish'){
		var msg = TEXT["Do you really want to publish this records"];
	}else if(BulkAction == 'draft'){
		var msg = TEXT["Do you really want to draft this records"];
	}else if(BulkAction == 'delete'){
		var msg = TEXT["Do you really want to delete this records"];
	}
	
	onCustomModal(msg, "onConfirmBulkAction");	
}

function onConfirmBulkAction() {

    $.ajax({
		type : 'POST',
		url: base_url + '/seller/bulkActionSellerBrands',
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

function openinput() {
    document.getElementById('thumbnail_file').click(); // open file picker
}

$('#thumbnail_file').on('change', function () {
    const file = this.files[0];
    if (!file) return;

    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
    if (!validTypes.includes(file.type)) {
        onErrorMsg('Only image files (jpg, png, gif, svg) are allowed.');
        $(this).val('');
        return;
    }

    $('#brand_thumbnail').val(file.name);

    const reader = new FileReader();
    reader.onload = function (e) {
        $('#view_thumbnail_image').html('<img src="' + e.target.result + '" alt="Preview">');
        $('#remove_thumbnail').show();
    };
    reader.readAsDataURL(file);
});
