/****************/

function ShowTools(id,hrf) {
	/*Панель инструментов модуля(фильтр, статистика, обновление таблицы итп)*/
	$('#'+id).hide();

	if(typeof hrf=='object')
		_last_load = $(hrf).attr('href');
	JSHR(id,hrf);
	$('#'+id).fadeIn();

	return false;
}

function JSHR(id,_href,param,body,insertType) {
	clearTimeout(timerid2);timerid2 = 0;
	timerid = setTimeout(function(){fShowload(1,'',body);},400);
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
		success: function(data, textStatus, XMLHttpRequest) {
			clearTimeout(timerid);
			timerid2 = setTimeout(function(){fShowload(0);},200);

			if(id!=0 && data.html != '') {
				if(typeof id!='object')
					id = '#'+id;
				if(insertType=='after')
					$(id).after(data.html);
				else if(insertType=='before')
					$(id).before(data.html);
				else
					$(id).html(data.html);
			}
			if(data.text != undefined && data.text!='') fLog(fSpoiler(data.text,'AJAX text result'),1);
			if(data.eval != undefined && data.eval!='') eval(data.eval);
		}
	});
	return false;
}

function JSFRWin(obj,htmlobj) {
	clearTimeout(timerid);
	timerid = setTimeout(function(){fShowload(1);},200);
	preSubmitAJAX(obj);
	$.ajax({
		type: "POST",
		url: $(obj).attr('action'),
		data: $(obj).serialize()+'&sbmt=1',
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
			clearTimeout(timerid);
			if(result.html!= undefined && result.html!='') {
				if(htmlobj) {
					timerid = setTimeout(function(){fShowload(0);},200);
					$(htmlobj).html(result.html);
				}
				else
					fShowload(1,result.html);
			}
			if(result.eval!= undefined && result.eval!='') eval(result.eval);
			if(result.text!= undefined && result.text!='') fLog(fSpoiler(result.text,'AJAX text result'),1);
		}
	});
	return false;
}