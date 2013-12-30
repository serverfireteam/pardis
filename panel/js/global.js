$(document).ready(function() {
	  $('#circle').click(function(){
		$('this').attr('name',"22").hide();
		$('#flags').show();
	})
	
	$('#flags').click(function(){
		$('#flags').hide();
		$('#circle').show();
	})
//.... upload btn.........
function upload_btn() {
            $("#img1").click();
        }
		/*$("a[name=delete]").live('click',function(){
			var temp = this;
			if(confirm('آیا مطمئن هستید می خواهید رکورد شماره '+$(this).attr('row-id')+' حذف کنید. ')){
				$.get("?page="+$(this).attr('page')+'&action=delete',{"id":$(this).attr('row-id'),"method":"ajax"},function(data){
          			if(data == 'این رکورد با موفقیت حذف شد'){
						$(temp).parent().parent().fadeOut('slow');
					}else
						alert(data);
          		})
			}
			return false;
		})*/
		/*$("a.checkbox").live('click',function(){
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
		$("#uncheck-all").live('click',function(){
		   $('input:checked').attr('checked', false);
		})
		$("#check-all").live('click',function(){
		   $('input[type=checkbox]').attr('checked', true);
		})
		$("#option-div p").click(function(){
										 
			$("#option-div p").fadeOut(500,function(){$("#option-div div").fadeIn(1000);	 })
										  
		})
		$("#do-option").click(function(){
			setCookie('order',$("select[name=order]").val());
			setCookie('num',$("input[name=num]").val());
			location.href = '?page=select_images&action=show';
		});
		//$("#head h1").pngfix();
		var cache = {},
			lastXhr;
		$(".google-ss").autocomplete({
			position :{ using: function( to ) {
					$( this ).css({
						top: to.top + 10,
						left: to.left
					})
			}},
			source: function( request, response ) {
				var term = request.term;
				if ( term in cache ) {
					response( cache[ term ] );
					return;
				}

				lastXhr = $.getJSON( "../?page=complete/json", request, function( data, status, xhr ) {
					cache[ term ] = data;
					if ( xhr === lastXhr ) {
						response( data );
					}
				});
			}
		});
 });
var loading = {
		show : function(){
			$('.ui-widget-overlay').css('width',$('html').css('width')).css('height',$('html').css('height'));
			$(".loading").show();
		},
		hide : function(){
			$(".loading").hide();
		}

}

		
function setCookie(c_name,value,expiredays)
{
var exdate=new Date();
exdate.setDate(exdate.getDate()+expiredays);
document.cookie=c_name+ "=" +escape(value)+
((expiredays==null) ? "" : ";expires="+exdate.toUTCString());
}

  function printpage() {
  window.print();
  }

