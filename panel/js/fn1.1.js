   
  $(document).ready(function() {			
		
		$(function() {
			$(".draggable" ).draggable({ 
				containment: "#containment-wrapper", scroll: false 
			});	
	//	$( ".draggable" ).draggable();
			$( ".sortable" ).sortable({
				revert:true
			});
		//$("ul,li").disableselection();	
	});


var old_key = 0;
var tr_num;	
var start_limit,end_limit;

$('.pagination a').click(function(){
	
	var value='';
	value=$(this).text();
	var num=$('.pagination .number').size();
	
	//.......................set............current.............class
	
	$('.pagination a').removeClass('current');
	$(this).addClass('current');
	
	//.......................edit......value...........pageing.......................	
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

		start_limit = (value - 1) * 15; //end_limit - 12;
		old_key     = start_limit;
	}

	//.....................set........value...........pageing.......................	
	
	var sURL = window.document.URL.toString();  
	page_name=decodeURI((RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1] );	
	
	$('#tab1 table tbody').html('');
	$('#overflow').show();
  //  $.getJSON("?page="+page_name+"&action=pagging&start_limit="+start_limit+"&end_limit="+end_limit, function(data) {	

	$.get("?page="+page_name+"&action=pagging&start_limit="+start_limit, function(data) {
		/*if(!$.isArray(data)){
			$("<div>موردی یافت نشد!</div>").dialog({title:'خطا رخ داده !'});
			return;
		}*/
		var items="";
		var input_id;
		
		/*$.each(data, function(key,val) {
			var sub_item='';
			var id ='';	
			input_id=val.id;	
		//	console.log(val)
			items+='<tr>';			
			$.each(val,function(subkey,subval){
			//	alert(subkey)	;	
			////alert(subkey)						
				if(subkey.match(/id/gi)){
					id=subval;
					return;
					
				}else{
				
					if(subval!="Null" && subval!="" &&  subval!=null && subval!=0 && subval!='undifind'){
						 if(subval.match(/resize/gi))									
							sub_item+='<td><img src="'+subval+'"/></td>' ;	
						else {
							sub_item+='<td>'+subval+'</td>' ;					
						}				
					}else {
						sub_item+='<td>'+subval+'</td>' ;					
					}
				}
			});	
		
			items+='<td><input type="checkbox" checkbox-id="'+input_id+'"/></td>'+sub_item;
			items+='<td><a page="'+page_name+'" row-id="'+input_id+'" name="delete" href="?page='+page_name+'&id='+input_id+'&action=show_delete" title="Delete"><img src="images/icons/cross.png" alt="Delete" /></a><a name="update" href="?page='+page_name+'&id='+input_id+'&action=show_update" title="Edit Meta"><img src="images/icons/hammer_screwdriver.png" alt="Edit" /></a></td>';
			items+='</tr>';
																
		});*/
		if (data!='' && data !=null){			
			items+=data;
			
		}
		$('#tab1 table tbody').html(items);
		//items+='</tbody>';	
		$('#overflow').hide();
			
		
		
	})
   
	// return false;
})

//.............................................................delete ......record.....with....click..............................................

		/*$("a[name=delete]").live('click',function(){
			var temp = this;
			if(confirm('آیا مطمئن هستید می خواهید رکورد شماره '+$(this).attr('row-id')+' حذف کنید. ')){
				$.get("?page="+$(this).attr('page')+'&action=delete',{"id":$(this).attr('row-id'),"method":"ajax"},function(data){
          			if(data == 'این رکورد با موفقیت حذف شد'){					
						$(temp).parent().parent().fadeOut('slow',function (){					
							$(this).remove();
						});					
					}else
						$('.notification attention png_bg').html(data);						
          		})
			}
			return false;
		})
		$("td.checkbox").live('click',function(){
			var temp = this,
				$checkbox = $('input:checked'),
				checkbox_arry = $("input:checked").map(function(){return $(this).attr('checkbox-id');}).toArray();
			if($(this).hasClass('ui-state-error') && confirm('آیا مطمئن هستید می خواهید موارد انتخاب شده را  حذف کنید. ')){
		   		loading.show();
				$.get("?page="+$(this).attr('page')+'&action=delete',{"id":checkbox_arry.join(","),"method":"ajax-checkbox"},function(data){
					loading.hide();
					if(data == 'رکورد های انتخابی حذف گردید'){
						$($checkbox).parent().parent().fadeOut('slow');
					}else
						alert(data);
				})
			
			<!--else{break;}-->		
			}else{
		   		loading.show();
				$.get("?",{
							"id":checkbox_arry.join(","),
							"method": "ajax-checkbox",
							"action": "update",
							"page" 	: $(this).attr('page'),
							"key"  	: $(this).attr('key'),
							"value"	: $(this).attr('value')
						  },
						  function(data){
						  	loading.hide();
							if(data == 'رکورد های انتخابی ویرایش گردید'){
								$($checkbox).attr('checked', false).parent().parent().find('td:eq('+(parseInt($(temp).attr('key'))+1)+')').html($(temp).find('span').html());
							}else
								alert(data);
						  })
		   	}
		   	return false;
		});*/
		
	//.................................................complete .............tag....................................
	
		//$("input[tag=autocomplete]").live('click',function(){		
			
			var sURL = window.document.URL.toString();  	
			
			page_name=decodeURI((RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1] );	
			$("#tags_complete").autocomplete({
				source: "?page="+page_name+"&action=autocomplete"
			});			
			
		
	//..................................................add..tag to array tags...................................................	
	
	$("#add_tag").live('click',function(){
		
		var tag=$("#tags_complete").val();	
		
		var fin=$('#all_tags').find('li[value="'+tag+'"').size();
		
		if(tag !=''){					
			$("#all_tags").append('<li class="ui-state-default ui-corner-all">'+tag+'<span class="ui-icon ui-icon-close"></span><input type="hidden" value="'+tag+'" name="tag[]" /></li>'); 	
		}	
		$("#tags_complete").val('');		
	});
	
	//.......................................................................................................
	$(".ui-icon-close").live('hover',function(){		
		$('.ui-icon-close').css( 'cursor', 'pointer' );	
	});
	$(".ui-icon-close").live('click',function(){			
		$(this).parent().remove();	
	});
	
//.......................................................................................................
 });	
function change_list(){
	var val=document.getElementById('user_type').value;	
	$('#archive').html('');
	
	if (val !=''){	
		$.getJSON('?page=profile_access&type=change_list&val='+val,function(data){
				if(jQuery.isEmptyObject(data)){
					$('#tabs-5').html('<ul><p style="width:185px; text-align: center; height:50px; padding-top:20px">موردی یافت نشد</p></ul>'+
					'<div class="clearfix"></div>');						
					return;
				}
			var items = [];	   
				items.push('<option></option>');
			$.each(data, function(key, val) {	
				items.push('<option value=' + key+'>' + val+ '</option>');
			});	
					
			$('#archive').html(items.join(''));		
		});
	}
	
};

//.......................................................................................................

function bank_list(){
	vals=document.getElementById('main_bank').value;	
	if (vals !=''){		
		
		$('#sub_bank').html('');		
		$.getJSON('?page=change_place&type=sub_bank&val='+vals,function(data){
				if(jQuery.isEmptyObject(data)){
					$('#tabs-5').html('<ul><p style="width:185px; text-align: center; height:50px; padding-top:20px">موردی یافت نشد</p></ul>'+
					'<div class="clearfix"></div>');						
					return;
				}
			var items = [];	   
			
			$.each(data, function(key, val) {					
			  items.push('<option value="'+key+'">'+val+'</option>');
			});	
						
			$('#sub_bank').html(items.join(''));	
			
			
		});
		
				
			
		
	}
	
};
