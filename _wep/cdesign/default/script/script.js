var timerid2;

function fLog(txt,flag) {
	if(GetId('debug_view'))
	{
		GetId('debug_view').innerHTML=txt+GetId('debug_view').innerHTML;
		if(flag==1) fShowDebug('debug_view',1);
	}
}

function JSHRWin(_href,param) {
	clearTimeout(timerid2);
	fShowload(1);
	$.getJSON(_href,param,
		function(result) {
			if(result.html!='') {
				fShowload(1,result.html);
			}
			if(result.eval!='') eval(result.eval);
		},
		true  // do not disable caching
	);
	return false;
}

function fShowDebug (id,f) {
	if(GetId(id).style.display!='none' && !f)
		$('#'+id).animate({ opacity: "hide" }, "slow");
	else
		$('#'+id).animate({ opacity: "show" }, "slow");
}


function load_href(hrf) {
	if(typeof hrf=='object')
		window.location.href = $(hrf).attr('href');
	else
		window.location.href = hrf;
	return false;
}

function hrefConfirm(obj,mess)
{
	if(confirm(MESS[mess])) {
		return true;
	}
	return false;
}

function cityAdd(cityid,cityname) {
	$('#tr_city > td > a').text(cityname);
	$("input[name='city']").attr("value", cityid);
	fShowload(0);
	return false;
}

function fckOpen(nm) {
	var txth;
	if($('#tr_'+nm+' td').text()=='') {
		var htm=$('#tr_'+nm+' script').text(); 
		//htm = htm.replace('\'/g','"');
		eval(htm);
		eval("txth=FCKTEXT_"+nm+";");
		$('#tr_'+nm+' td').html(txth);
	}
	//setTimeout(function(){$('#tr_'+nm).slideToggle('fast')}, 400);
	$('#tr_'+nm).toggle();
}

