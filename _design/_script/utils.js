var timerid = 0;
var timerid2 = 0;
var _Browser = getBrowserInfo();
var ajaxComplite = 1;
function JSHR(id,_href,param,body,insertType) {
	clearTimeout(timerid2);timerid2 = 0;
	timerid = setTimeout(function(){fShowload(1,'',body);},400);
	$.ajax({
		type: "GET",
		url: _href,
		data: param,
		dataType: "json",
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert(textStatus);
		},
		success: function(data, textStatus, XMLHttpRequest) {
			clearTimeout(timerid);
			timerid2 = setTimeout(function(){fShowload(0);},200);

			if(id!=0 && data.html != '') {
				if(typeof id=='object'){
					if(insertType=='after')
						$(id).after(data.html);
					else if(insertType=='before')
						$(id).before(data.html);
					else
						id.innerHTML = data.html;
				}
				else {
					if(insertType=='after')
						$('#'+id).after(data.html);
					else if(insertType=='before')
						$('#'+id).before(data.html);
					else
						GetId(id).innerHTML = data.html;
				}
			}
			if(data.eval != undefined && data.eval!='') eval(data.eval);
			if(data.text != undefined && data.text!='') fLog(fSpoiler(data.text,'AJAX text result'),1);
		}
	});
	return false;
}
/*********** FORMa ********/

function JSFR(n) {
	$(n).ajaxForm({
		beforeSubmit: 
			function(a,f,o) {
				//var formElement = f[0];
				o.dataType = 'json';
			},
		/*notsuccess: 
			function(d,statusText) {
				//alert(statusText+'  - form notsuccess: '+d.responseText);
				clearTimeout(timerid);
				timerid2 = setTimeout(function(){fShowload(0);},200);
			},*/
		success: 
			function(result) {
				//alert(dump(result));
				clearTimeout(timerid);
				timerid2 = setTimeout(function(){fShowload(0);},200);
				if(result.html!= undefined && result.html!='') GetId(_win2).innerHTML = result.html;
				if(result.eval!= undefined && result.eval!='') eval(result.eval);
				if(result.text!= undefined && result.text!='') fLog(fSpoiler(result.text,'AJAX text result'),1);

			}

	});
}


function CKSubmit() {
	nm = $(this).attr('name');
	eval("if(typeof CKEDITOR.instances."+nm+" == 'object') {CKEDITOR.instances."+nm+".updateElement();}");
	return true;
}

function preSubmitAJAX (obj) {
	if(typeof CKEDITOR !== 'undefined') {
		clearTimeout(timerid2);
		timerid = setTimeout(function(){fShowload(1);},400);
		if(parent.frames && parent.frames.length){
			for ( i = 0; i < parent.frames.length; ++i ){

				if ( parent.frames[i].FCK )
					parent.frames[i].FCK.UpdateLinkedField();
			}
		}
		jQuery.each($(obj).find("textarea"),CKSubmit);
	}
	return true;
}

function keys_return(ev) {
	var keys=0;
	if (navigator.appName == 'Netscape')
		{keys=ev.which;}
	else if(navigator.appName == 'Microsoft Internet Explorer')
		{keys=window.event.keyCode;}
	if(keys==8 || keys==46 || keys==13 || keys==39 || keys==37) keys=0;
	return keys;
	/*39 37 стрелки
	46 делете
	8 удал
	13 интер*/
}


function checkInt(ev) {
	var keys = keys_return(ev);
	if ((keys<0x30 || keys>0x39) && (keys<96 || keys>105) && keys!=0) 
		return false;
	return true;
}

function textareaChange(obj,max)
{
	/* Утилита для подсчёта кол сиволов в форме, автоматически создаёт необходимые поля*/

	if(!GetId(obj.name+'t2'))
	{
		$('<span class="dscr">Cимволов:<input type="text" id="'+obj.name+'t2" maxlength="4" readonly="false" class="textcount" style="text-align:right;"/>/<input type="text" id="'+obj.name+'t1" maxlength="4" readonly="false" class="textcount" value="'+max+'"/></span>').appendTo(obj.parentNode);
	}
	if(obj.value.length>max) obj.value=obj.value.substr(0,max);
	GetId(obj.name+'t2').value = obj.value.length;
}

function reloadCaptha(id)
{
	$('#'+id).attr('src',"/_capcha.php?"+ Math.random());
}

function checkPass(name) {
	$("form input[type=submit]").attr({'disabled':'disabled'});
	val_1=$("form input[name="+name+"]").val();
	val_2=$("form input[name=re_"+name+"]").val();
	if(val_1.length>=6) {
		$("form input[name="+name+"]").attr({'class':'accept'});
		if(val_1!=val_2)
			$("form input[name=re_"+name+"]").attr({'class':'reject'});
		else{
			$("form input[name=re_"+name+"]").attr({'class':'accept'});
			$("form input[type=submit]").attr({'disabled':''});
		}
	}else {
		$("form input[name="+name+"]").attr({'class':'reject'});
		$("form input[name=re_"+name+"]").attr({'class':'reject'});
	}
	return true;
}

function password_new() {
	var type1 = 'password';
	var type2 = 'text';
	if(!$('input[type=password].password').length){
		type1 = 'text';
		type2 = 'password';
	}
	$('.password').after("<input type='"+type2+"' value='"+$('input[type='+type1+'].password').attr('value')+"' name='"+$('input[type='+type1+'].password').attr('name')+"' class='password'/>");
	$('input[type='+type1+'].password').remove();
}

function show_params(selector) {
	if($(selector).is(':hidden')) {
		$(selector).show().css({'background-color':'#EDEDED','width':'60%','margin':'0 auto'});
		$('#tr_showparam').hide();
		$('#tr_hideparam').show();
	}
	else {
		$(selector).hide();

		$('#tr_showparam').show();
		$('#tr_hideparam').hide();
	}

}
var timerid4=0;
var timerid5=0;
function show_hide_lable(obj,view,flag) {
	clearTimeout(timerid5);
	//$('#tr_city .td1').append(flag+'-');
	if(ajaxComplite==0 || timerid4) {
		setTimeout(function(){show_hide_lable(obj,view,flag);},950);
	}else {
		setTimeout(function(){
			//$('#tr_type .td1').append(flag+'.');
			if(flag){
				$(obj).prev().hide();
				ajaxlist(obj,view);
			}
			else{
				$('#ajaxlist_'+view).hide();
				timerid5 = setTimeout(function(){ajaxlistClear(obj,view);},800);
				if(!obj.value){
					$(obj).prev().show();
				}
				
			}
		},200);
	}

}
function ajaxlistClear(obj,view) {
	if($('#ajaxlist_'+view+' + input').val()=='') {
		$(obj).val('');
		$(obj).prev().show();
		clearTimeout(timerid4);timerid4=0;
	}
}

function ajaxlist(obj,view) {
	if(obj.value.length>1) {
		clearTimeout(timerid4);
		if(ajaxComplite==1)
			timerid4 = setTimeout(function(){getJsonData(obj.value,view);},900);
		else
			timerid4 = setTimeout(function(){ajaxlist(obj,view);},1000);
	}else {
		clearTimeout(timerid4);timerid4=0;
		$('#ajaxlist_'+view+' + input').val('');
		$('#ajaxlist_'+view).prev('input').attr('class','reject');
	}
}
//$('#tr_city .td1').append('+')

function getJsonData(value,view) {
	timerid4 = 0;
	if($('#ajaxlist_'+view).attr('val')==value) {
		$('#ajaxlist_'+view).show();
	}
	else{
		$('#ajaxlist_'+view).prev('input').attr('class','reject');
		ajaxComplite = 0;
		$.ajax({
			type: "GET",
			url: '/_json.php',
			data: {'_view':'ajaxlist', '_srlz':$('input[name="srlz_'+view+'"]').val(),'_value':value, '_hsh':$('input[name="hsh_'+view+'"]').val()},
			dataType: "json",
			cache:true,
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert('error: '+textStatus);
			},
			success: function(result, textStatus, XMLHttpRequest) {
				var txt = '';
				if(result && result.data && count(result.data)>0) {
					var c = 0;var temp = 0;
					for(k in result.data) {
						txt += '<lable name="'+k+'">'+result.data[k]+'</lable>';
						if(result.data[k]==value){
							temp = k;
							c++;								
						}
					}
					if(c==1){
						$('#ajaxlist_'+view+' + input').val(temp);
						$('#ajaxlist_'+view+' lable[name="'+temp+'"]').addClass('selectlable');
						$('#ajaxlist_'+view).prev('input').attr('class','accept');
					}
				}else
					txt = 'не найдено';
				$('#ajaxlist_'+view).html(txt).show();
				$('#ajaxlist_'+view).attr('val',value);
				$('#ajaxlist_'+view+' lable').click(function(){
					var key = $(this).attr('name');
					$('#ajaxlist_'+view+' + input').val(key);
					$('#ajaxlist_'+view).prev('input').val($(this).text());
					$('#ajaxlist_'+view).prev('input').attr('class','accept');
					$('#ajaxlist_'+view+' lable').attr('class','');
					$('#ajaxlist_'+view+' lable[name="'+key+'"]').attr('class','selectl');
				});
				ajaxComplite = 1;
			}
		});
	}
}

/************************/

function count (o) {
	cnt=0;
	if(typeof o=='object'){
		for(var key in o)
			cnt++;
	}
	return cnt;
}

function GetId(id)
{
	return document.getElementById(id);
}

function showi(obj,id,show,hide) {
//функция для отображения елемента id и смены рисунка на объекте obj
	if(GetId(id).style.display=='block') {
		if(obj.src) 
			obj.src = hide;
		else
			obj.style.backgroundPosition = hide;
		GetId(id).style.display='none';
	}
	else {
		if(obj.src) 
			obj.src = show;
		else 
			obj.style.backgroundPosition = show;
		GetId(id).style.display='block';
	}
}

/*
_mask =  new Object();
function checkKey(ev,filter) {
var keys = keys_return(ev);
if (in_array(keys,_mask[filter])) 
	return true;
return false;
}
*/
var timerid3;
function boardrubric(obj,id) {
	clearTimeout(timerid3);
	timerid3 = setTimeout(function(){boardrubricExe(obj,id);},400);
	return true;
}

function boardrubricExe(obj,id) {
	var objAfter;
	if(!obj) return 0;
	if(typeof obj!='object') {
		obj=$('select[name='+obj+']');
		objAfter = 'tr_'+obj;
	}else
		objAfter = $(obj).parent().parent();
	if(!id) id='';
	$('.addparam').remove();
	JSHR(objAfter,'/_js.php?_modul=board&_view=boardlist&_id='+id+'&_rid='+$(obj).val(),'',0,'after');
	return true;
}

function rclaim(nm) {
	var val=$('select[name='+nm+']').val();
	if(val==1 || val==3 || val==5)
		$('.addparam span.form-requere').css('display','none');
	else{
		$('.addparam span.form-requere').css('display','');
	}
}
	
function CreateElem(name, attrs, style, text)
{
	var e = document.createElement(name);
	if (attrs) {
		for (key in attrs) {
			if (key == 'class') {
				e.className = attrs[key];
			} else if (key == 'id') {
				e.id = attrs[key];
			} else {
				e.setAttribute(key, attrs[key]);
			}
		}
	}
	if (style) {
		for (key in style) {
			e.style[key] = style[key];
		}
	}
	if (text) {
		e.appendChild(document.createTextNode(text));
	}
	return e;
}

function clickSpoilers(obj) {
	$(obj).toggleClass('unfolded');
	$(obj).next('div.spoiler-body').slideToggle('fast');
}

function fSpoiler (txt,nm) {
	//initSpoilers();
	if(!nm) nm ='Скрытый текст';
	return '<div class="spoiler-wrap"><div class="spoiler-head folded clickable" onClick="clickSpoilers(this)">+ '+nm+'</div><div class="spoiler-body" style="display: none;">'+txt+'</div></div>';
}

function initSpoilers(context){
	var context = context || 'body';
	$('div.spoiler-head', $(context))
		.click(function(){
			$(this).toggleClass('unfolded');
			$(this).next('div.spoiler-body').slideToggle('fast');
		})
	;
}

function showBG(body,show,k) {
	if(!body) body='body';
	if(!show){
		$(body+' #ajaxbg').hide();
	}
	else {
		if(!k) k= 0.5;
		if($(body+' #ajaxbg').size()==0)
			$(body).append("<div id='ajaxbg'>&#160;</div>");
		$(body+' #ajaxbg').css('opacity', k).show();
	}
}

function fShowload (show,txt,body,objid) {
	if(!body) body='body';
	if(!objid) objid = 'ajaxload';
	obj = ' #'+objid;
	if(!show){
		$(body+obj).css('display','none');
		showBG(body);
		if (_Browser.type == 'IE' && 7 > _Browser.version && _Browser.version >= 4)
			$('select').css('display','block');
	}else{
		if (_Browser.type == 'IE' && 7 > _Browser.version && _Browser.version >= 4)
			$('select').css('display','none');
		if($(body+obj).size()==0)
			$(body).append("<div id='"+objid+"'>&#160;</div>");
		if(!txt || txt==''){
			txt = "<div class='layerloader'><img src='/_design/_img/load.gif' alt=' '/><br/>Подождите. Идёт загрузка</div>";
			$('.layerblock').hide();
		}
		else {
			$('.layerloader').hide();
			if(objid == 'ajaxload') 
				txt = '<div class="layerblock"><div class="blockclose" onClick="fShowload(0)"></div>'+txt+'</div>';
		}		
		showBG(body,1);
		$(body+obj).html(txt);
		$(body+obj).show();
		if(body=='body')
			fMessPos(body,obj);
	}
}

function fMessPos(body,obj) {
	if(!body) body='body';
	if(!obj) obj=' #ajaxload';
	$(body+obj).css("width",'');
	var H=document.documentElement.clientHeight;
	var Hblock= $(body+obj+' :first-child').attr("offsetHeight");
	var hh=Math.round(50*((H-Hblock)/H));
	if(hh<4) hh=4;
	var W=document.documentElement.clientWidth;
	var Wblock= $(body+obj+' :first-child').attr("offsetWidth");
	var ww=Math.round(50*((W-Wblock)/W));
	if(ww<4) ww=4;
	$(body+obj).css("top",hh+"%").css("left",ww+"%").css("width",Wblock+'px');
}

function getBrowserInfo() {
	var t,v = undefined;
	if (window.opera) t = 'Opera';
	else if (document.all) {
		t = 'IE';
		var nv = navigator.appVersion;
		var s = nv.indexOf('MSIE')+5;
		v = nv.substring(s,s+1);
	}
	else if (navigator.appName) t = 'Netscape';
	return {type:t,version:v};
}

	
function dump(arr, level) {/*аналог ф в ПХП print_r*/
    var dumped_text = "";
    if(!level) level = 0;

    var level_padding = "    ";

    if(typeof(arr) == 'object') {
        for(var item in arr) {
            var value = arr[item];
 
            if(typeof(value) == 'object') {
                dumped_text += level_padding + "’" + item + "’ …\n";
                if(level>0) dumped_text += dump(value,level-1);
            }
            else {
                dumped_text += level_padding + "’" + item + "’ => \"" + value + "\"\n";
            }
        }
    }
    else {
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}

function getToText(obj) {
	var valu='';
	var txt='';
	for(var i=0; i<obj.elements.length; i++) {
		if(obj.elements[i].type!='submit') {
			if(obj.elements[i].type!='checkbox' || obj.elements[i].checked)
				valu += obj.elements[i].name+'='+obj.elements[i].value+'&';
			if(obj.elements[i].name=='rubric')
				txt=obj.elements[i].options[obj.elements[i].selectedIndex].text;
			if(obj.elements[i].name=='type')
				txt += '; '+obj.elements[i].options[obj.elements[i].selectedIndex].text
			if(obj.elements[i].name=='cost')
				txt += '; от '+obj.elements[i].value+' руб.';
			if(obj.elements[i].name=='cost_2')
				txt += '; до '+obj.elements[i].value+' руб.';
		}
	}
	$('#tr_param > div input').val(valu);
	$('#tr_param > div > a').text(txt);
	fShowload(0);
	return false;
}

function gSlide (id,_min,_max,val0, val1,stp) {
		if(!_max && val1) _max = val1*3;
		else if(!_max) _max = 100;
		if(!val1) val1 = _max;
		$('#'+id+' div').after("<div id='slide"+id+"' style='margin:3px 10px 5px;'></div>");
		$('#slide'+id).slider({
			range: true,
			step: stp,
			min: _min,
			max: _max,
			values: [val0, val1],
			slide: function(event, ui) {
				$('#'+id+' div input').eq(0).val(ui.values[0]);
				$('#'+id+' div input').eq(1).val(ui.values[1]);
			}
		});
		$('#'+id+' div input').eq(0).val(val0);
		$('#'+id+' div input').eq(1).val(val1);
		$('#'+id+' div input').eq(0).bind('change',function (){$('#slide'+id).slider( 'values' , 0 , this.value)});
		$('#'+id+' div input').eq(1).bind('change',function (){$('#slide'+id).slider( 'values' , 1 , this.value)});
}

function setCookie(name, value, expiredays, path, domain, secure) {
   if (expiredays) {
      var exdate=new Date();
      exdate.setDate(exdate.getDate()+expiredays);
      var expires = exdate.toGMTString();
   }
   document.cookie = name + "=" + escape(value) +
   ((expiredays) ? "; expires=" + expires : "") +
   ((path) ? "; path=" + path : "") +
   ((domain) ? "; domain=" + domain : "") +
   ((secure) ? "; secure" : "");
}

function getCookie(name) {
   var cookie = " " + document.cookie;
   var search = " " + name + "=";
   var setStr = null;
   var offset = 0;
   var end = 0;
   if (cookie.length > 0) {
      offset = cookie.indexOf(search);
      if (offset != -1) {
         offset += search.length;
         end = cookie.indexOf(";", offset)
         if (end == -1) {
            end = cookie.length;
         }
         setStr = unescape(cookie.substring(offset, end));
      }
   }
   return setStr;
}

function shownextfoto(crnt,nxt){
	$('#tr_'+nxt).show();
	$(crnt).remove();
}

var MESS = {
	'del':'Вы действительно хотите провести операцию удаления?',
	'delprof':'Вы действительно хотите удалить свой профиль?',
};

function showHelp(obj,mess,time,nomiga) {
	if(!obj || !mess || $(obj).next().attr('class')=='helpmess') return false;
	var pos = $(obj).position();
	pos.top = parseInt(pos.top);
	pos.left = parseInt(pos.left);
	if(!time) time = 5000;
	$(obj).after('<div class="helpmess">'+mess+'<div class="trgl trgl_d"> </div></div>');
	var slct = $(obj).next();
	var H = $(slct).height();
	H = pos.top-17-H;
	if(H<5) {
		H = pos.top+10+$(obj).height();
		$(slct).find('.trgl').attr('class','trgl trgl_u');
	}
	$(slct).css({'top':H,'left':pos.left,'opacity':0.8});
	//if(!nomiga)
	//	miga(slct,0.8);
	setTimeout(function(){$(slct).stop().fadeOut(1000,function(){$(slct).remove()});},time);
	//if(time>5000)
		$(slct).click(function(){
			$(slct).stop().fadeOut(1000,function(){$(slct).remove()});
		});
}

function miga(obj,opc1,opc2){
	if($(obj).size()==0)
		return false;
	var opc = $(obj).css('opacity');
	if(!opc2) opc2 = 0.4;
	if(opc==opc2)
		opc = (opc1?opc1:1);
	else
		opc=opc2;
	$(obj).animate({'opacity': opc},1000,function(){miga(obj,opc1,opc2);});
	return false;
}

function input_file(obj) {
	var myRe=/.+\.([A-Za-z]{3,4})/i;
	var myArray = myRe.exec($(obj).val());
	$(obj).parent().find('span.fileinfo').html(myArray[1]);
}

function putEMF(id,txt) {
	$('#tr_'+id+' .form-caption').after('<div class="caption_error">['+txt+']</div>');
}


function ShowTools(id,hrf) {
	/*Панель инструментов модуля(фильтр, статистика, обновление таблицы итп)*/
	$('#'+id).hide();

	if(typeof hrf=='object')
		_last_load = $(hrf).attr('href');
	JSHR(id,hrf);
	$('#'+id).fadeIn();

	return false;
}