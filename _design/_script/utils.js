var timerid = 0;
var timerid2 = 0;
var _Browser = getBrowserInfo();

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

function fLog(txt,flag) {
	if(jQuery('#debug_view').size())
		jQuery('#debug_view').html(txt+jQuery('#debug_view').html());
	else
		jQuery(".maintext .block").prepend("<div id='debug_view' style='border:1px solid blue;'>"+txt+"</div>");
	if(flag==1) fShowHide('debug_view',1);
}

function fMessPos(body,obj) {
	if(!body) body='body';
	if(!obj) obj=' #ajaxload';
	jQuery(body+'>'+obj).css("width",'');
	var H=document.documentElement.clientHeight;
	var Hblock= jQuery(body+'>'+obj+' :first-child').attr("offsetHeight");
	var hh=Math.round(50*((H-Hblock)/H));
	if(hh<4) hh=4;
	var W=document.documentElement.clientWidth;
	var Wblock= jQuery(body+'>'+obj+' :first-child').attr("offsetWidth");
	var ww=Math.round(50*((W-Wblock)/W));
	if(ww<4) ww=4;
	jQuery(body+'>'+obj).css("top",hh+"%").css("left",ww+"%").css("height",H+'px');//.css("width",Wblock+'px')
}

/*Показ тултип*/
function showHelp(obj,mess,time,nomiga) {
	if(!obj || !mess || !jQuery(obj).size() || jQuery(obj).next().attr('class')=='helpmess') return false;
	var pos = jQuery(obj).position();
	pos.top = parseInt(pos.top);
	pos.left = parseInt(pos.left);
	if(!time) time = 5000;
	jQuery(obj).after('<div class="helpmess">'+mess+'<div class="trgl trgl_d"> </div></div>');
	var slct = jQuery(obj).next();
	var H = jQuery(slct).height();
	H = pos.top-17-H;
	if(H<5) {
		H = pos.top+10+jQuery(obj).height();
		jQuery(slct).find('.trgl').attr('class','trgl trgl_u');
	}
	jQuery(slct).css({'top':H,'left':pos.left,'opacity':0.8});
	//if(!nomiga)
	//	miga(slct,0.8);
	setTimeout(function(){jQuery(slct).stop().fadeOut(1000,function(){jQuery(slct).remove()});},time);
	//if(time>5000)
		jQuery(slct).click(function(){
			jQuery(slct).stop().fadeOut(1000,function(){jQuery(slct).remove()});
		});
}

function miga(obj,opc1,opc2){
	if(jQuery(obj).size()==0)
		return false;
	var opc = jQuery(obj).css('opacity');
	if(!opc2) opc2 = 0.4;
	if(opc==opc2)
		opc = (opc1?opc1:1);
	else
		opc=opc2;
	jQuery(obj).animate({'opacity': opc},1000,function(){miga(obj,opc1,opc2);});
	return false;
}


/*Bacground*/
function fShowload (show,txt,body,objid) {
	if(!body) body='body';
	if(!objid) objid = 'ajaxload';
	obj = ' #'+objid;
	if(!show){
		jQuery(body+'>'+obj).css('display','none');
		showBG(body);
		if (_Browser.type == 'IE' && 8 > _Browser.version)
			jQuery('select').toggleClass('hideselectforie7',false);
	}else{
		if (_Browser.type == 'IE' && 8 > _Browser.version)
			jQuery('select').toggleClass('hideselectforie7',true);

		if(jQuery(body+'>'+obj).size()==0)
			jQuery(body).append("<div id='"+objid+"'>&#160;</div>");
		if(!txt || txt==''){
			txt = "<div class='layerloader'><img src='_design/_img/load.gif' alt=' '/><br/>Подождите. Идёт загрузка</div>";
			jQuery('.layerblock').hide();
		}
		else {
			jQuery('.layerloader').hide();
			if(objid == 'ajaxload') 
				txt = '<div class="layerblock"><div class="blockclose" onClick="fShowload(0)"></div>'+txt+'</div>';
		}		
		showBG(body,1);
		if(txt && txt!='') 
			jQuery(body+' > '+obj).html(txt);
		jQuery(body+' > '+obj).show();
		if(body=='body')
			fMessPos(body,obj);
	}
}

function showBG(body,show,k) {
	if(!body) body='body';
	if(!show){
		jQuery(body+' > #ajaxbg').hide();
	}
	else {
		if(!k) k= 0.5;
		if(jQuery(body+' > #ajaxbg').size()==0)
			jQuery(body).append("<div id='ajaxbg'>&#160;</div>");
		jQuery(body+' > #ajaxbg').css('opacity', k).show();
	}
}


/*SPOILER*/

function fSpoiler (txt,nm) {
	//initSpoilers();
	if(!nm) nm ='Скрытый текст';
	return '<div class="spoiler-wrap"><div class="spoiler-head folded clickable" onClick="clickSpoilers(this)">+ '+nm+'</div><div class="spoiler-body" style="display: none;">'+txt+'</div></div>';
}

function initSpoilers(context){
	var context = context || 'body';
	jQuery('div.spoiler-head', jQuery(context))
		.click(function(){
			jQuery(this).toggleClass('unfolded');
			jQuery(this).next('div.spoiler-body').slideToggle('fast');
		})
	;
}

/*END SPOILER*/

function fShowHide (id,f) {
	if(jQuery('#'+id).css('display')!='none' && !f)
		jQuery('#'+id).animate({ opacity: "hide" }, "slow");
	else
		jQuery('#'+id).animate({ opacity: "show" }, "slow");
}

function ulToggle(obj,css) {
	jQuery(obj).toggleClass(css);
	jQuery(obj).parent().find('>ul').slideToggle('fast');
}
/************************/
/*simple script*/

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


/************************/
/****************/

function ShowTools(id,hrf) {
	/*Панель инструментов модуля(фильтр, статистика, обновление таблицы итп)*/
	jQuery('#'+id).hide();

	if(typeof hrf=='object')
		_last_load = jQuery(hrf).attr('href');
	JSWin({'href':hrf,'insertObj':'#'+id});
	jQuery('#'+id).fadeIn();

	return false;
}

function readyPlot(cap,Xname,Yname,stepY) {
	plot1 = $.jqplot('statschart2', [line1], {
		title:cap,
		axes:{
			xaxis:{label:Xname,renderer:$.jqplot.DateAxisRenderer},
			yaxis:{label:Yname,min:0,tickInterval:stepY,tickOptions:{formatString:'%d'} }},
		cursor:{zoom: true},
		series:[{lineWidth:4, markerOptions:{style:'square'}}]
	});
}

function JSWin(param) {
	if(typeof param['type']=='object') {
		if(typeof CKEDITOR !== 'undefined') {
			jQuery.each(jQuery(param['type']).find("textarea"),function(){nm=jQuery(this).attr('name');if(nm) eval("if(typeof CKEDITOR.instances."+nm+" == 'object') {CKEDITOR.instances."+nm+".updateElement();}");});
		}
		param['href'] = jQuery(param['type']).attr('action');
		param['data'] = jQuery(param['type']).serialize()+'&sbmt=1';
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
			if(param['insertObj'] && result.html != '') {
				if(param['insertType']=='after')
					jQuery(param['insertObj']).after(result.html);
				else if(param['insertType']=='before')
					jQuery(param['insertObj']).before(result.html);
				else
					jQuery(param['insertObj']).html(result.html);
				timerid2 = setTimeout(function(){fShowload(0,'',param['body']);},200);
			}
			else if(result.html!='') fShowload(1,result.html,param['body']);
			else timerid2 = setTimeout(function(){fShowload(0,'',param['body']);},200);

			if(typeof result.text != 'undefined' && result.text!='') fLog(fSpoiler(result.text,'AJAX text result'),1);
			if(typeof result.eval != 'undefined' && result.eval!='') eval(result.eval);
			if(typeof param['call'] != 'undefined' && typeof param['call'] == 'function') 
				param['call'].call(result);
		}
	});
	return false;
}


var lochref = window.location.href;
var i = lochref.indexOf('#', 0);
if(i >= 0) {
	lochref = lochref.substring(0, i);
}
var tmp;

function pagenum_super(total,pageCur,cl,order) {
	if(!order) order = false;
	if(jQuery('.ppagenum').size()>0 && total>20) {
		$.includeCSS('/_design/_style/paginator.css');
		pg = 1;
		reg = new RegExp('\&*'+cl+'_pn=([0-9]*)', 'i');
		tmp = reg.exec(lochref);
		if(tmp)
			pg =  tmp[2];
		else if(!tmp && order)
			pg = total;
		
		loc = lochref.replace(reg, '');
		var param = '';
		if(/\?/.exec(loc)) {
			if(loc.substr(-1,1)!='&' && loc.substr(-1,1)!='?')
				param += '&';
		}
		else
			param += '?';
		param += cl+'_pn=';
		//alert(dump(loc));
		$.include('/_design/_script/jquery.paginator.js',function(ob){
			jQuery('.pagenum').css('display','none');
			jQuery('.ppagenum').css('display','block').paginator({pagesTotal:total, 
				pagesSpan:8, 
				returnOrder:order,
				pageCurrent:pageCur, 
				baseUrl: function (page){
					if((!order && page!=1) || (order && page!=total))
						loc += param+page;
					window.location.href = loc;
				},
			});
		});
		//returnOrder: true
	}
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
