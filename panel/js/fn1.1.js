$(document).ready(function() {

	var prevOrders = [];

	$(function() {

		getPreviousOrders();

		$(".draggable").draggable({
			containment: "#containment-wrapper", scroll: false
		});

		$(".sortable").mouseover(function() {
			$(this).css("cursor", "move");
		});

		$(".sortable").sortable({
			revert: true,
			start: function(event, ui) {
				pageNum     = $('.pagination a.number.current').html();
				sourceId	= ui.item.find('a[name="delete"]').attr("row-id");
				sourceIndex = ((parseInt(pageNum) - 1) * 15) + ui.item.context.rowIndex;
			},
			stop: function(event, ui) {

				var orders  = [];
				pageNum     = $('.pagination a.number.current').html();
				targetIndex = ((parseInt(pageNum) - 1) * 15) + ui.item.context.rowIndex;

				if (targetIndex > sourceIndex) {

					$("tbody.sortable tr").each(function() {
						rowIndex = ((parseInt(pageNum) - 1) * 15) + $(this).index() + 1;
						rowId	 = $(this).find('a[name="delete"]').attr("row-id");
						if (rowIndex >= sourceIndex && rowIndex < targetIndex) {
							newOrder = prevOrders[rowIndex];
							$(this).find("input[name='order']").attr("value", newOrder);
							orders.push({"id": rowId, "order": newOrder});
						}
					});

					newOrder = prevOrders[targetIndex];
					ui.item.find("input[name='order']").attr("value", newOrder);
					orders.push({"id": sourceId, "order": newOrder});

				} else if (targetIndex < sourceIndex) {

					$("tbody.sortable tr").each(function() {
						rowIndex = ((parseInt(pageNum) - 1) * 15) + $(this).index() + 1;
						rowId	 = $(this).find('a[name="delete"]').attr("row-id");
						if (rowIndex > targetIndex && rowIndex <= sourceIndex) {
							newOrder = prevOrders[rowIndex];
							$(this).find("input[name='order']").attr("value", newOrder);
							orders.push({"id": rowId, "order": newOrder});
						}
					});

					newOrder = prevOrders[targetIndex];
					ui.item.find("input[name='order']").attr("value", newOrder);
					orders.push({"id": sourceId, "order": newOrder});
				}

				var sURL  = window.document.URL.toString();
				page_name = decodeURI((RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]);

				$.ajax({
					type: "POST",
					data: {orders: orders},
					url: "?page="+page_name+"&action=updateRowOrder",
					dataType: 'json'
				});
			}
		});

	});

	var old_key = 0;
	var tr_num;
	var start_limit, end_limit;

	$('.pagination a').click(function() {

		var value = '';
		value = $(this).text();
		var num=$('.pagination .number').size();

		$('.pagination a').removeClass('current');
		$(this).addClass('current');

		tr_num = $('#tab1 table tbody tr').size();

		if (value == '1' || value == '« first' || value == '') {
			start_limit = 0;
			end_limit   = 14;
			old_key     = start_limit;
		} else if (value == 'last »') {
			end_limit   = 'last';
			start_limit = 'last';
			old_key		= start_limit;
		} else if (value == '« pre') {
			if (old_key == 'last')
				start_limit='pre_last';
			else if (old_key == 0)
				return false;
			else {
				start_limit = parseInt(old_key) - 15;
				old_key     = start_limit;
			}
		} else if (value == 'next »') {
			if (old_key == 'last') {
				end_limit   = 'last';
				start_limit = 'last';
				old_key		= start_limit;
			} else if (old_key >= (parseInt(num) * 15) - 15)
				return false;
			else {
				start_limit = parseInt(old_key) + 15;
				old_key     = start_limit;
			}
		} else {		 
			if (tr_num == '0') {
				value = parseInt(value) - 1;
				$(this).remove();
			}
			end_limit = value * 14 - 1;

			start_limit = (value - 1) * 15;
			old_key     = start_limit;
		}

		var sURL  = window.document.URL.toString();
		page_name = decodeURI((RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]);

		$('#tab1 table tbody').html('');
		$('#overflow').show();

		$.get("?page="+page_name+"&action=pagging&start_limit="+start_limit, function(data) {

			var items = "";
			var input_id;

			if (data != '' && data != null) {
				items += data;
			}

			$('#tab1 table tbody').html(items);
			$('#overflow').hide();

			getPreviousOrders();
		});
	});

	var sURL = window.document.URL.toString();

	page_name = decodeURI((RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]);
	$("#tags_complete").autocomplete({
		source: "?page="+page_name+"&action=autocomplete"
	});

	$("#add_tag").live('click',function() {

		var tag = $("#tags_complete").val();
		var fin = $('#all_tags').find('li[value="'+tag+'"').size();

		if (tag != '') {
			$("#all_tags").append('<li class="ui-state-default ui-corner-all">' + tag + '<span class="ui-icon ui-icon-close"></span><input type="hidden" value="' +
								  tag + '" name="tag[]" /></li>');
		}
		$("#tags_complete").val('');
	});

	$(".ui-icon-close").live('hover',function() {
		$('.ui-icon-close').css('cursor', 'pointer');
	});

	$(".ui-icon-close").live('click',function() {
		$(this).parent().remove();
	});

	function getPreviousOrders() {
		prevOrders = [];
		// find previous orders
		$("tbody.sortable tr").each(function() {
			pageNum   = $('.pagination a.number.current').html();
			rowIndex  = ((parseInt(pageNum) - 1) * 15) + $(this).index() + 1;
			prevOrder = $(this).find("input[name='order']").attr("value");
			prevOrders[rowIndex] = prevOrder;
		});
	}
});

function change_list() {
	var val = document.getElementById('user_type').value;
	$('#archive').html('');

	if (val != '') {
		$.getJSON('?page=profile_access&type=change_list&val='+val,function(data) {
			if (jQuery.isEmptyObject(data)) {
				$('#tabs-5').html('<ul><p style="width:185px; text-align: center; height:50px; padding-top:20px">موردی یافت نشد</p></ul>' +
								  '<div class="clearfix"></div>');
				return;
			}
			var items = [];
			items.push('<option></option>');
			$.each(data, function(key, val) {
				items.push('<option value=' + key + '>' + val + '</option>');
			});
		
			$('#archive').html(items.join(''));
		});
	}
}

function bank_list() {
	vals = document.getElementById('main_bank').value;
	if (vals != '') {
		$('#sub_bank').html('');
		$.getJSON('?page=change_place&type=sub_bank&val=' + vals,function(data) {
			if (jQuery.isEmptyObject(data)) {
				$('#tabs-5').html('<ul><p style="width:185px; text-align: center; height:50px; padding-top:20px">موردی یافت نشد</p></ul>' +
								  '<div class="clearfix"></div>');
				return;
			}

			var items = [];

			$.each(data, function(key, val) {
				items.push('<option value="' + key + '">' + val + '</option>');
			});

			$('#sub_bank').html(items.join(''));
		});
	}
}
