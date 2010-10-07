var timerid2;
function fLog (txt,flag) {
	if(GetId('ajaxError'))
		GetId('ajaxError').innerHTML=txt;
	else
		$(".maintext .block").prepend("<div id='ajaxError' style='border:1px solid blue	;'>"+txt+"</div>");
}

function JSHRWin(_href,param,body,objid) {
	clearTimeout(timerid2);
	fShowload(1,body,objid);
	$.ajax({
		type: "GET",
		url: _href,
		data: param,
		dataType: "json",
		beforeSend: function(XMLHttpRequest) {
			return true;
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert(textStatus);
		},
		dataFilter: function(data, type) {
			return data;
		},
		success: function(result) {
			if(result.html!='') fShowload(1,result.html,body,objid);
			if(result.eval!='') eval(result.eval);
		}
	});
	return false;
}


function JSFRWin(obj) {
	clearTimeout(timerid2);
	fShowload(1);
	$.ajax({
		type: "POST",
		url: $(obj).attr('action'),
		data: $(obj).serialize(),
		dataType: "json",
		beforeSend: function(XMLHttpRequest) {
			return true;
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert(textStatus);
		},
		dataFilter: function(data, type) {
			return data;
		},
		success: function(result) {
			if(result.html!='') fShowload(1,result.html);
			if(result.eval!='') eval(result.eval);
		}
	});
	return false;
}

function liveInet(id) {
	GetId(id).innerHTML = "<!-- тут код счетчика -->";
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