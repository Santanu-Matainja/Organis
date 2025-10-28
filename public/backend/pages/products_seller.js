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
	resetForm("BulkDataEntry_formId");
	
	$("#submit-form").on("click", function () {
        $("#DataEntry_formId").submit();
    });

	$("#bulksubmit-form").on("click", function () {
        $("#BulkDataEntry_formId").submit();
    });

	$(document).on('click', '.pagination a', function(event){
		event.preventDefault(); 
		var page = $(this).attr('href').split('page=')[1];
		onPaginationDataLoad(page);
	});
	
	$('input:checkbox').prop('checked',false);
	
    $(".checkAll").on("click", function () {
        $("input:checkbox").not(this).prop("checked", this.checked);
    });

	$("#title").on("blur", function () {
		if(RecordId ==''){
			onProductSlug();
		}
	});
	
	$("#language_code").val(0).trigger("chosen:updated");
	$("#language_code").on("change", function () {
		onCategoryList();
		onBrandList();
		onRefreshData();
	});
	
	$("#category_id").val(0).trigger("chosen:updated");
	$("#category_id").on("change", function () {
		onRefreshData();
	});
	
	$("#brand_id").val('all').trigger("chosen:updated");
	$("#brand_id").on("change", function () {
		onRefreshData();
	});
	
	$("#lan").chosen();
	$("#lan").trigger("chosen:updated");
	$("#lan").on("change", function () {
		onCategoryListForform();
		onBrandListForform();
	});
	
});

function onCheckAll() {
    $(".checkAll").on("click", function () {
        $("input:checkbox").not(this).prop("checked", this.checked);
    });
}

function onPaginationDataLoad(page) {

	$.ajax({
		url:base_url + "/seller/getProductsTableData?page="+page
		+"&search="+$("#search").val()
		+"&language_code="+$('#language_code').val()
		+"&category_id="+$('#category_id').val()
		+"&brand_id="+$('#brand_id').val(),
		success:function(data){
			$('#tp_datalist').html(data);
			onCheckAll();
		}
	});
}

function onRefreshData() {

	$.ajax({
		url:base_url + "/seller/getProductsTableData?search="+$("#search").val()
		+"&language_code="+$('#language_code').val()
		+"&category_id="+$('#category_id').val()
		+"&brand_id="+$('#brand_id').val(),
		success:function(data){
			$('#tp_datalist').html(data);
			onCheckAll();
		}
	});
}

function onSearch() {

	$.ajax({
		url: base_url + "/seller/getProductsTableData?search="+$("#search").val()
		+"&language_code="+$('#language_code').val()
		+"&category_id="+$('#category_id').val()
		+"&brand_id="+$('#brand_id').val(),
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
	
	$("#lan").trigger("chosen:updated");
}

function onListPanel() {
	$('.parsley-error-list').hide();
    $('#list-panel, .btn-form, .btn-bulkform').show();
    $('#form-panel, .btn-list').hide();
	$('#bulk-form-panel').hide();
}

function onFormPanel() {
    resetForm("DataEntry_formId");
	RecordId = '';

	$("#lan").trigger("chosen:updated");
	
    $('#list-panel, .btn-form').hide();
    $('#form-panel, .btn-list').show();
	$('#bulk-form-panel, .btn-bulkform').hide();
	
	onCategoryListForform();
	onBrandListForform();
}

function onBulkFormPanel() {
    resetForm("BulkDataEntry_formId");
	RecordId = '';

	$("#lan").trigger("chosen:updated");
	
    $('#list-panel, .btn-form').hide();
    $('#form-panel, .btn-bulkform').hide();
    $('#bulk-form-panel, .btn-list').show();
	
	// onCategoryListForform();
	// onBrandListForformbulk();
}

function onEditPanel() {
    $('#list-panel, .btn-form').hide();
    $('#form-panel, .btn-list').show();	
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
		url: base_url + '/seller/saveProductsData',
		data: $('#DataEntry_formId').serialize(),
		success: function (response) {			
			var msgType = response.msgType;
			var msg = response.msg;

			if (msgType == "success") {
				resetForm("DataEntry_formId");
				onRefreshData();
				onSuccessMsg(msg);
				var id = response.id;
				window.location.href= base_url + '/seller/product/'+id;
			} else {
				onErrorMsg(msg);
			}
			
			onCheckAll();
		}
	});
}

jQuery('#BulkDataEntry_formId').parsley({
    listeners: {
        onFieldValidate: function (elem) {
            if (!$(elem).is(':visible')) {
                return true;
            } else {
                showPerslyError();
                return false;
            }
        },
        onFormSubmit: function (isFormValid, event) {
            if (isFormValid) {
                bulkupload();
                return false;
            }
        }
    }
});

// Excel upload + preview logic
let excelData = [];

$('#excelFile').on('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (event) {
        const data = new Uint8Array(event.target.result);
        const workbook = XLSX.read(data, { type: 'array' });
        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        const rows = XLSX.utils.sheet_to_json(sheet, { defval: '' }); 

        if (rows.length === 0) {
            onErrorMsg("No data found in Excel file.");
            return;
        }

      
        if (rows.length > 100) {
			$('#maxlenMsg').remove();
			$('#excelPreviewTable').before(`
				<p id="maxlenMsg" class="text-danger">
					<strong>Note:</strong> Maximum Upload Limit Crossed. 
					You can upload a Maximum of 100 rows at a time.
				</p>
			`);

			setTimeout(() => {
				$('#maxlenMsg').fadeOut(500, function() { $(this).remove(); });
			}, 2000);

			$('#excelFile').val(''); // reset file input
			$('#excelPreviewTable thead, #excelPreviewTable tbody').empty();
			return;
		}


        // Build table headers dynamically
        const headers = Object.keys(rows[0]);
        const thead = $('#excelPreviewTable thead');
        const tbody = $('#excelPreviewTable tbody');
        thead.empty();
        tbody.empty();

        let headerHtml = '<tr>';
        headers.forEach(h => headerHtml += `<th>${h}</th>`);
        headerHtml += '</tr>';
        thead.html(headerHtml);

        // Fill preview + prepare JSON
        excelData = [];
        rows.forEach(row => {
            excelData.push({
                title: row['Title'] || '',
                short_desc: row['Short Description'] || '',
                description: row['Description'] || '',
                extra_desc: row['Extra Description'] || '',
                cost_price: row['Cost Price'] || '',
                sale_price: row['Sale Price'] || '',
                old_price: row['Old Price'] || '',
                sku: row['SKU'] || '',
                stock_qty: row['Stock Qty'] || '',
                image: row['Image'] || ''
            });

            let rowHtml = '<tr>';
            headers.forEach(h => {
                rowHtml += `<td>${row[h] ?? ''}</td>`;
            });
            rowHtml += '</tr>';
            tbody.append(rowHtml);
        });

        // Add a message about SKU rule
        $('#skuInfoMsg').remove();
        $('#excelPreviewTable').before(`
            <p id="skuInfoMsg" class="text-info">
                <strong>Note:</strong> SKU must be unique. 
                If the same SKU is found, product details will be updated instead of creating a new one.
            </p>
        `);
    };
    reader.readAsArrayBuffer(file);
});

function bulkupload() {

    if (excelData.length === 0) {
        onErrorMsg("Please upload a valid Excel file first.");
        return;
    }

    const formData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        categoryid: $('#categoryid2').val(),
        user_id: $('#user_id2').val(),
        brand_id2: $('#brandid2').val(),
        excelData: excelData
    };

    $.ajax({
        type: 'POST',
        url: base_url + '/seller/bulksaveProductsData',
        data: formData,
        success: function (response) {
            if (response.msgType === "success") {
                resetForm("BulkDataEntry_formId");
                $('#excelPreviewTable thead, #excelPreviewTable tbody').empty();
                onSuccessMsg(response.msg);
                window.location.href = base_url + '/seller/products';
            } else {
                onErrorMsg(response.msg);
            }
        },
        error: function () {
            onErrorMsg("Something went wrong while saving data.");
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
		url: base_url + '/seller/deleteProducts',
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
		url: base_url + '/seller/bulkActionProducts',
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

//Product Slug
function onProductSlug() {
	var StrName = $("#title").val();
	var str_name = StrName.trim();
	var strLength = str_name.length;
	if(strLength>0){
		$.ajax({
			type : 'POST',
			url: base_url + '/seller/hasProductSlug',
			data: 'slug='+StrName,
			success: function (response) {
				var slug = response.slug;
				$("#slug").val(slug);
			}
		});
	}
}

function onCategoryList() {
	
	$.ajax({
		type : 'POST',
		url: base_url + '/seller/getCategoryList',
		data: 'lan='+$('#language_code').val(),
		success: function (data) {
			var html = '<option value="0" selected="selected">'+TEXT['All Category']+'</option>';
			$.each(data, function (key, obj) {
				html += '<option value="' + obj.id + '">' + obj.name + '</option>';
			});
			
			$("#category_id").html(html);
			$("#category_id").chosen();
			$("#category_id").trigger("chosen:updated");
		}
	});
}

function onBrandList() {
	
	$.ajax({
		type : 'POST',
		url: base_url + '/seller/getBrandList',
		data: 'lan='+$('#language_code').val(),
		success: function (data) {
			var html = '<option value="all" selected="selected">'+TEXT['All Brand']+'</option>';
			$.each(data, function (key, obj) {
				html += '<option value="' + obj.id + '">' + obj.name + '</option>';
			});
			
			$("#brand_id").html(html);
			$("#brand_id").chosen();
			$("#brand_id").trigger("chosen:updated");
		}
	});
}

function onCategoryListForform() {
	
	$.ajax({
		type : 'POST',
		url: base_url + '/seller/getCategoryList',
		data: 'lan='+$('#lan').val(),
		success: function (data) {
			var html = '';
			$.each(data, function (key, obj) {
				html += '<option value="' + obj.id + '">' + obj.name + '</option>';
			});
			
			$("#categoryid").html(html);
			$("#categoryid").chosen();
			$("#categoryid").trigger("chosen:updated");
		}
	});
}

function onBrandListForform() {
	
	$.ajax({
		type : 'POST',
		url: base_url + '/seller/getBrandList',
		data: 'lan='+$('#lan').val(),
		success: function (data) {
			var html = '';
			$.each(data, function (key, obj) {
				html += '<option value="' + obj.id + '">' + obj.name + '</option>';
			});
			
			$("#brandid").html(html);
			$("#brandid").chosen();
			$("#brandid").trigger("chosen:updated");
		}
	});
}

$(document).ready(function() {
    let option4 = $('#delivarytypeid option[value="4"]');

    $('#perisible').on('change', function() {
        if ($(this).is(':checked')) {
            option4.hide(); // completely hide the option
        } else {
            option4.show(); // show it again if unchecked
        }
        $('#delivarytypeid').trigger("chosen:updated"); // refresh chosen dropdown
    });
});