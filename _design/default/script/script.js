var timerid2;
function fLog (txt,flag) {
	if(GetId('ajaxError'))
		GetId('ajaxError').innerHTML=txt;
	else
		$(".maintext .block").prepend("<div id='ajaxError' style='border:1px solid blue	;'>"+txt+"</div>");
}

function showLoginForm(id) {
	$('#'+id).show();
	$('#'+id+' .layerblock').show();
	showBG(0,1);
	fMessPos(0,' #'+id);
	return false;
}


function commanswer(id) {
	$('div.commformanswer').css('display','none');
	if(id==0) {
		$('#tr_sbmt input').val($('span.button_comm').text());
		$('div.form_comments').css('position','');
		$('span.button_comm').remove();
		$('div.form_comments #parent_id').val(0);
	} 
	else {
		if($('div.form_comments').css('position')!='absolute') {
			$('div.form_comments').after('<span onclick="return commanswer(0);" class="jshref button_comm">'+$('#tr_sbmt input').val()+'</span>');
			$('#tr_sbmt input').val('Написать ответ');
		}
		$('div.commformanswer').css('display','none');
		var paramW = $('div.form_comments').attr('clientWidth');
		var paramH = $('div.form_comments').attr('clientHeight');
		var pos = $('#commitem'+id+' > .commformanswer').css({'display':'block', 'width':paramW+'px', 'height':paramH+'px'}).position();
		$('div.form_comments').css({'position':'absolute','top':pos['top']+'px','left':pos['left']+'px'});
		$('div.form_comments #parent_id').val(id);
	}
	return false;
}


function JSWin(param) {
	if(typeof param['type']=='object') {
		param['href'] = $(param['type']).attr('action');
		param['data'] = $(param['type']).serialize();
		param['type'] = 'POST';
	}
	else if(!param['type']) param['type'] = 'GET';
	if(!param['href'])		param['href'] = '';
	if(!param['data']) 		param['data'] = '';
	if(!param['dataType'])	param['dataType'] = 'json';
	if(!param['insertObj'])	param['insertObj'] = 0;
	if(!param['insertType'])	param['insertType'] = 0;
	if(!param['body'])		param['body'] = 'body';
	clearTimeout(timerid2);timerid2 = 0;
	timerid = setTimeout(function(){fShowload(1,'',param['body']);},100);
	$.ajax({
		type: param['type'],
		url: param['href'],
		data: param['data'],
		dataType: param['dataType'],
		beforeSend: function(XMLHttpRequest) {
			return true;
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert(textStatus);
		},
		dataFilter: function(data, type) {
			return data;
		},
		success: function(result, textStatus, XMLHttpRequest) {
			clearTimeout(timerid);

			if(param['insertObj']!=0 && result.html != '') {
				if(typeof param['insertObj']!='object')
					param['insertObj'] = '#'+param['insertObj'];
				if(param['insertType']=='after')
					$(param['insertObj']).after(result.html);
				else if(param['insertType']=='before')
					$(param['insertObj']).before(result.html);
				else
					$(param['insertObj']).html(result.html);
				timerid2 = setTimeout(function(){fShowload(0,'',param['body']);},200);
			}
			else if(result.html!='') fShowload(1,result.html,param['body']);
			else timerid2 = setTimeout(function(){fShowload(0,'',param['body']);},200);

			if(result.text != undefined && result.text!='') fLog(fSpoiler(result.text,'AJAX text result'),1);
			if(result.eval != undefined && result.eval!='') eval(result.eval);
		}
	});
	return false;
}