var _last_load='';
var _win1 = 'modulstree';
var _win2 = 'modulsforms';
var _cash_win2;

function onLoadBodyAdmin() {
	_cash_win2 = GetId(_win2).innerHTML;
	fResized();
	window.onresize = function(){ fResized();};
	return true;
}

function fLog (txt,flag) {
	if(GetId('debug_view'))
	{
		GetId('debug_view').innerHTML=txt+GetId('debug_view').innerHTML;
		if(flag==1) fShowDebug('debug_view',1);
	}
}

function fResized() {
	var H,W;
	$('#wepmain').animate({ opacity: "show" }, "slow");
	H = document.body.offsetHeight-GetId('sysconf').offsetHeight-GetId('modulslist').offsetHeight-GetId('cmsinfo').offsetHeight-3;
	if(H<500) H=500;
	GetId(_win1).style.height=GetId(_win2).style.height= H+'px';

	W = document.body.offsetWidth-GetId(_win1).offsetWidth;
	if(W<600) W=600;
	//GetId(_win2).style.width= W+'px';
	
	GetId('debug_view').style.maxHeight=document.body.offsetHeight-20+'px'
}

function nameSel(obj) {
	$('.'+obj.className).css({fontWeight:'',color:''});
	obj.style.fontWeight = 'bold';
	obj.style.color = '#90EE90';
}

function fShowDebug (id,f) {
	if(GetId(id).style.display!='none' && !f)
		$('#'+id).animate({ opacity: "hide" }, "slow");
	else
		$('#'+id).animate({ opacity: "show" }, "slow");
}

function sf(modul,servname) {
	//fShowload(1);
	_last_load = 'js.php?_type='+servname+'&_modul='+modul;
	JSHR(_win2,_last_load);
}

function fSwin1 (flag) {
	$('#'+_win1).animate({ opacity: "hide" }, "fast");
	if(!flag) fSwin2();
}

function fSwin2 () {
	GetId(_win2).innerHTML = _cash_win2;
}

/*if (navigator.appName == 'Netscape') {
	addEventListener(Event.KEYDOWN);
}*/


	function fnShowProps(obj, objName,t){
		var result = "";
		for (var i in obj) // обращение к свойствам объекта по индексу
			result += objName + "." + i + " = " + obj[i] + "<br />\n";
		if(t==1) document.write(result);
		else alert(result);
	}
	var _arr_sort= new Object();
	var _as=0;


	function load_href(hrefs) {
		if(typeof hrefs=='object'){
			_last_load = 'js.php'+$(hrefs).attr('href');
		}
		else
			_last_load = 'js.php'+hrefs;
		JSHR(_win2,_last_load);
		return false;
	}


	function hrefConfirm(obj,mess)
	{
		if(confirm(MESS[mess])) {
			if(typeof hrefs=='object'){
				_last_load = 'js.php'+$(hrefs).attr('href');
			}
			else
				_last_load = 'js.php'+hrefs;
			JSHR(_win2,_last_load);
		}
		return false;
	}


	function _selid(obj) {
		$('div.tiname').attr({'class':'tiname'});
		if(obj)
			$('#'+obj+' > div.tiname').attr({'class':'tiname thover'});
		return 1;
	}

function JSHRWin(_href,param) {
	clearTimeout(timerid2);
	fShowload(1);
	$.getJSON(_href,param,
		function(result) {
			if(result.html!='') {
				fShowload(1,result.html);
			}
			if(result.eval!='') 
				eval(result.eval);
			if(result.text!='') 
				fLog(fSpoiler(result.text,'ERROR LIST'),1);
		},
		true  // do not disable caching
	);
	return false;
}

function cityAdd(cityid,cityname) {
	$('#tr_city > td > a').text(cityname);
	$("input[name='city']").attr("value", cityid);
	fShowload(0);
	return false;
}

function iSortable() {// сортировка
}

