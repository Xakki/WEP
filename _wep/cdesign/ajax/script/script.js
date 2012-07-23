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

function Lmtree(modul,obj,id) {
	if(id) {
		//fShowload(1);
		if(GetId('_'+modul+'_'+id).style.display=='none') {
			if(obj)
				obj.className='buttonimg ti imgminus';
			_last_load = 'js.php?_type=modulschild&_modul='+modul+'&_id='+id;
			JSHR('_'+modul+'_'+id,_last_load);
		}else{
			if(obj)
				obj.className='buttonimg ti imgplus';
			$('#_'+modul+'_'+id).slideUp('fast');
		}
	}
	else{
		if(obj) {
			$('.'+obj.className).css({fontWeight:'',color:''});
			obj.style.fontWeight = 'bold';
			obj.style.color = '#90EE90';
		}
		_last_load = 'js.php?_type=modulstree&_modul='+modul;
		JSHR(_win1,_last_load);
		fSwin2();
	}
}

function LmtreeP(modul,oid,pid,req) {
	if(oid=='' || oid=='0') oid=pid;
	if(modul=='select_page') return 1;
	if(!req) req='';
	if(oid!='' && oid!='0') {
		_last_load = 'js.php?_type=modulschild&_modul='+modul+'&_id='+oid+req;
		JSHR('_'+modul+'_'+oid,_last_load);
	}
	else{
		_last_load = 'js.php?_type=modulstree&_modul='+modul+req;
		JSHR(_win1,_last_load);
	}
	
}

function lf(modul,id,oid,pid) {
	//fShowload(1);
	_last_load = 'js.php?_type=item&_id='+id+'&_modul='+modul;
	if(oid) _last_load +='&_oid='+oid;
	if(pid) _last_load +='&_pid='+pid;
	JSHR(_win2,_last_load);
}

function df(modul,id,n) {
	if(confirm('Вы действительно хотите удалить "'+n+'" ?')) {
		//fShowload(1);
		_last_load = 'js.php?_type=del&_id='+id+'&_modul='+modul;
		JSHR(_win2,_last_load);
	}
}

function ou(modul,id,obj) {
	_last_load = 'js.php?_type=sortup&_modul='+modul+'&_id='+id;
	JSHR('_'+modul+'_'+id,_last_load);
}
function od(modul,id,obj) {
	_last_load = 'js.php?_type=sortdown&_modul='+modul+'&_id='+id;
	JSHR('_'+modul+'_'+id,_last_load);
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

	function iSortable() {// сортировка
			$('div.ui-sortable').sortable({
				items: '>div.treeitem',
				axis:	'y',
				helper: 'original',
				opacity:'false',//opacity: 0.6
				//revert: true,// плавное втыкание
				//tolerance: 'pointer',
				//grid: [1, 13],
				placeholder:'sortHelper',
				//activeclass : 'sortableactive',
				//hoverclass : 'sortablehover',
				handle: '>div>a.ti_replace',
				tolerance: 'pointer',
				//start: function(event, ui) { ... },
				//sort: function(event, ui) { ... },
				//change: function(event, ui) { alert(ui);},
				update: function(event, ui) {
					o= ui.item;
					id= $(o).attr('id');
					modul = $(o).attr('mod');
					if(!_arr_sort[modul])
						_arr_sort[modul]= new Object();
					t1 = $(o).prev('div.treeitem').attr('id');
					t3 = $(o).next('div.treeitem').attr('id');
					if(t3) {
						_arr_sort[modul][_as]={'t':'next','id':id,'id2':t3};
					}
					else if(t1) {
						_arr_sort[modul][_as]={'t':'prev','id':id,'id2':t1};
					}else{
						alert(t1+' * '+t3);
					}
					_as++;
					checkOrd(modul);
				}
			}
		);
	}


	function checkOrd (modul) {
		if(count(_arr_sort[modul])>0)
			 GetId(_win2).innerHTML = '<div style="position:absolute;top:50%;left:50%;"><div style="width:200px;height:100px;position:absolute;top:-50px;left:-100px;white-space:nowrap;"> <a href="##" onClick="return sendOrd(\''+modul+'\')">СОРТИРОВАТЬ модуль "'+modul+'" ('+count(_arr_sort[modul])+' элементов)</a></div></div>';
		else{
			 GetId(_win2).innerHTML = '<div style="position:absolute;top:50%;left:50%;"><div style="width:200px;height:100px;position:absolute;top:-50px;left:-100px;"><img src="img/login.gif" width="250"/></div></div>';
		}
		return true;
	}

	function sendOrd (modul) {
		if(count(_arr_sort)>0) {
			_last_load = 'js.php?_type=sort&_modul='+modul+'&_obj='+serialize(_arr_sort[modul]);
			JSHR(_win2,_last_load);
		}
		return false;
	}

function serialize( mixed_value ) {
	// http://kevin.vanzonneveld.net
	// +   original by: Arpad Ray (mailto:arpad@php.net)
	// +   improved by: Dino
	// +   bugfixed by: Andrej Pavlovic
	// +   bugfixed by: Garagoth
	// %          note: We feel the main purpose of this function should be to ease the transport of data between php & js
	// %          note: Aiming for PHP-compatibility, we have to translate objects to arrays
	// *     example 1: serialize(['Kevin', 'van', 'Zonneveld']);
	// *     returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
	// *     example 2: serialize({firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'});
	// *     returns 2: 'a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}'
 
	var _getType = function( inp ) {
		var type = typeof inp, match;
		var key;
		if (type == 'object' && !inp) {
			return 'null';
		}
		if (type == "object") {
			if (!inp.constructor) {
				return 'object';
			}
			var cons = inp.constructor.toString();
			if (match = cons.match(/(\w+)\(/)) {
				cons = match[1].toLowerCase();
			}
			var types = ["boolean", "number", "string", "array"];
			for (key in types) {
				if (cons == types[key]) {
					type = types[key];
					break;
				}
			}
		}
		return type;
	};
	var type = _getType(mixed_value);
	var val, ktype = '';
 
	switch (type) {
		case "function": 
			val = ""; 
			break;
		case "undefined":
			val = "N";
			break;
		case "boolean":
			val = "b:" + (mixed_value ? "1" : "0");
			break;
		case "number":
			val = (Math.round(mixed_value) == mixed_value ? "i" : "d") + ":" + mixed_value;
			break;
		case "string":
			val = "s:" + mixed_value.length + ":\"" + mixed_value + "\"";
			break;
		case "array":
		case "object":
			val = "a";
			/*
			if (type == "object") {
				var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);
				if (objname == undefined) {
					return;
				}
				objname[1] = serialize(objname[1]);
				val = "O" + objname[1].substring(1, objname[1].length - 1);
			}
			*/
			var count = 0;
			var vals = "";
			var okey;
			var key;
			for (key in mixed_value) {
				ktype = _getType(mixed_value[key]);
				if (ktype == "function") { 
					continue; 
				}
 
				okey = (key.match(/^[0-9]+$/) ? parseInt(key) : key);
				vals += serialize(okey) +
						serialize(mixed_value[key]);
				count++;
			}
			val += ":" + count + ":{" + vals + "}";
			break;
	}
	if (type != "object" && type != "array") val += ";";
	return val;
}

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
		if(confirm(mess)) {
			_last_load = 'js.php'+$(obj).attr('href');
			JSHR(_win2,_last_load);
			return true;
		}
		return false;
	}

	function _selid(obj) {
		$('div.tiname').attr({'class':'tiname'});
		if(obj)
			$('#'+obj+' > div.tiname').attr({'class':'tiname thover'});
		return 1;
	}

	function addFileInput(obj,cnt) {
		var temp =$(obj.previousSibling).clone();
		if($("input[name="+temp.attr('name')+"]").length>=cnt) {
			return 1;
		}
		temp.attr({'value':''});
		$(obj).before('<img src="/admin/design/default/img/del.png" alt="DEL" style="cursor:pointer;" onClick="delFileInput(this)"/><br/>');
		$(obj).before(temp);
		return 1;
	}

	function delFileInput(obj) {
		$(obj.previousSibling.previousSibling).remove();
		$(obj.previousSibling).remove();
		$(obj).remove();
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