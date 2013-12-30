// JavaScript Document
$(document).ready(function() {
	$a=0;		 	
	
	resizable();
	$(window).resize(resizable);	
	
	$( "#slider" ).slider({
		value:0,
		min: 0,
		max: 15,
		step: 0.1,
		slide: function( event, ui ) {
			$( "#amount" ).val( ui.value );
		}
	});
	$( "#amount" ).val( $( "#slider" ).slider( "value" ) );

	$("#music").change(function(){
		msg('در حال بارگذاری متن موزیک...');
		$(".note").remove();
		$.ajax({
			type: "GET",
			timeout: 20000,
			dataType : "json",
			cache : false,
			url: "?page=music_text/get&callback=?&id="+$(this).val(),
			error: function(XMLHttpRequest, textStatus, errorThrown) {
					msg("خطای ارتباط با سرور : " + errorThrown);
			},
			success: function( data ) {
				  msg(false);
				  $.each(data, function(key, val) {
					add_note(val.text,val.s,val.top)
					//console.log(val.text,val.s,val.top)
					
				  });
				 
				  
			}
	    });
	})
	setInterval(function(){
		$.ajax({
			type: "GET",
			timeout: 20000,
			cache : false,
			url: "?page=music_text/update_login",
			error: function(XMLHttpRequest, textStatus, errorThrown) {
					msg("خطای ارتباط با سرور : " + errorThrown);
			}
	    });
	},1*60000)
	$("#export").button().click(export_music);
	$("#back").button().click(function(){
		window.location = '?page=controlpanel';
	})
	
	$('#send').mousedown(function(){
		$('#send').addClass("change");	
	})
	$('#send').mouseup(function(){
		$('#send').removeClass("change");	
	})
	var num_str = '',m=0,s=0,m_show=0,s_show=0;
	for(var i = 0;i<360;i++){
		
		if(s==60){
			s = 0;
			m++;
		}
		m_show = m;
		s_show = s;
		if(m<10)
			m_show = "0"+m;
		if(s<10)
			s_show = "0"+s;
		num_str += '<li>'+m_show+":"+s_show+'</li>';
		s++;
	}
	$('#music-design ul').append(num_str);
})

$(window).resize(resizable);


var resizable = function(){
	$a=($(window).width()-($(window).width()%60));
	//$('#music-design').width($a);
	//alert($('#music-design').width());	
},
add_note = function(text,size,top){
	
	var span = '' ;
	size = (!size) ?  $( "#amount" ).val() : size;
	text = (!text) ?  $( "#text" ).val() : text;
	console.log(text,size,top)
	if(size>0){
		if(text!=''){
			span='<span '+(top!='' ? 'style="top:'+top+'"' : '')+'><p class="del"></p><p class="after"></p>'+text+'</span>';
		}else{
			span='<p class="remove"></p><p class="next"></p>';
		}
	};
	$('#music-design').append('<div class="note" style=" width:'+((size*10)*6)+'px;">'+span+'</div>');	
	$('#text').val('');
	$( "#music-design div span" ).draggable({ axis: "y" , containment: ".note" });
	$( "#slider" ).slider( "value" , 0 )
	$( "#amount" ).val(0);
},
export_json={} ,
export_music = function(){
	if($("#music-design .note").size()==0){
		msg("نوت وجود ندارد ،لطفا ایجاد نمایید")
		return;
	}
	msg('در حال ارسال ...')
	$("#music-design .note").each(function(i){
		export_json[i] = {"text":$(this).find('span').text(),"s":$(this).width()/60,"top":$(this).find('span').css("top")}
	})
	$.ajax({
			type: "POST",
			timeout: 20000,
			cache : false,
			url: "?page=get_note&callback=?",
			data  : {'json':export_json,'id':$("#music").val()},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
					loading(false);
					msg("خطای ارتباط با سرور: ");//+ errorThrown
			},
			success: function( data ) {
				if(data==0) 
					msg("نوت شما قرار گرفت");
				else
					msg("اشکالی در قرار گرفتن نوت در سرور ایجاد شده است .");
			}
	  });
},
msg_timer,
msg = function(text,timeout){
	clearTimeout(msg_timer);
	if(!text){
		$("#system-msg").fadeOut(1000);
		return false;
	}
	if(timeout>0)
		msg_timer = setTimeout(function(){msg();},timeout);
	if(msg.length > 65)
		text = text.slice(0,65)+ '...';
	$("#system-msg").html(text);
	$("#system-msg").fadeIn(1000);
}
/*$('.note span').live('click',function(){
	//alert($(this).hasClass('select'));
	if (($(this).hasClass('select'))==true)
		$(this).removeClass('select');
	else{
		$('.note span').removeClass('select');
		$(this).addClass('select');	
	}
	})*/
$('.after').live('click',function(){
	//add_note_next();
	if(($( "#amount" ).val()>0)){
		if($('#text').val()!=''){
			$(this).parent().parent().after('<div class="note" style=" width:'+(($( "#amount" ).val()*10)*6)+'px;"><span><p class="del"></p><p class="after"></p>'+$('#text').val()+'</span></div>');
		}else{
			$(this).parent().parent().after('<div class="note" style=" width:'+(($( "#amount" ).val()*10)*6)+'px;"><p class="remove"></p><p class="next"></p>'+$('#text').val()+'</div>');
		}
	};	
	$('#text').val('');
	$( "#music-design div span" ).draggable({ axis: "y" });
	$( "#slider" ).slider( "value" , 0 )
	$( "#amount" ).val(0);	
});

$('.del').live('click',function(){
	$(this).parent().parent().html('<p class="remove"></p><p class="next"></p>');
	$(this).parent().remove();	
});
$('.next').live('click',function(){
	if(($( "#amount" ).val()>0)){
		if($('#text').val()!=''){
			$(this).parent().after('<div class="note" style=" width:'+(($( "#amount" ).val()*10)*6)+'px;"><span><p class="del"></p><p class="after"></p>'+$('#text').val()+'</span></div>');
		}else{
			$(this).parent().after('<div class="note" style=" width:'+(($( "#amount" ).val()*10)*6)+'px;"><p class="remove"></p><p class="next"></p>'+$('#text').val()+'</div>');
		}
	};	
	$('#text').val('');
	$( "#music-design div span" ).draggable({ axis: "y" });
	$( "#slider" ).slider( "value" , 0 )
	$( "#amount" ).val(0);	
});

$('.remove').live('click',function(){
	$(this).parent().remove();
});
$('#send').live('click',function(){
	add_note();	
});