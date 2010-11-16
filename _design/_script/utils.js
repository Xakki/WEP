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
	if($('#debug_view').size())
		$('#debug_view').html(txt+$('#debug_view').html());
	else
		$(".maintext .block").prepend("<div id='debug_view' style='border:1px solid blue	;'>"+txt+"</div>");
	if(flag==1) fShowHide('debug_view',1);
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
	$(body+obj).css("top",hh+"%").css("left",ww+"%").css("height",H+'px');//.css("width",Wblock+'px')
}

/*Показ тултип*/
function showHelp(obj,mess,time,nomiga) {
	if(!obj || !mess || !$(obj).size() || $(obj).next().attr('class')=='helpmess') return false;
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


/*Bacground*/
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


/*SPOILER*/
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

/*END SPOILER*/

function fShowHide (id,f) {
	if($('#'+id).css('display')!='none' && !f)
		$('#'+id).animate({ opacity: "hide" }, "slow");
	else
		$('#'+id).animate({ opacity: "show" }, "slow");
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