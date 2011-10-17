
wep.apply(wep, {
	load_href: function(hrf) {
		if(typeof hrf=='object')
			window.location.href = jQuery(hrf).attr('href');
		else
			window.location.href = hrf;
		return false;
	},
});

function cityChange(ciyid) {
	str = window.location.href;
	if (str.indexOf('city=',0) != -1)
		window.location.href =str.replace(/city=(\d+)/i,'city='+ciyid);
	else if(str.indexOf('?',0) != -1)
		window.location.href =str+'&city='+ciyid;
	else
		window.location.href =str+'?city='+ciyid;
	return false;
}

function cityAdd(domen,cityname) {
	jQuery('#tr_city > div > a').text(cityname);
	jQuery("input[name='city']").attr("value", domen);
	fShowload(0);
	return false;
}

function rubricmain() {
	 jQuery(".rubmain ul li").hover(
		function (e) {
			var pos = jQuery(this).find('a').position();
			jQuery(this).find('.rubinfo').css('left',Math.round(pos.left)+jQuery(this).find('a').width()+2+'px');
			jQuery(this).find('.rubinfo').fadeIn(0);
		},
		function () {
			jQuery(this).find('.rubinfo').fadeOut(0);
		}
    );

}

function checkLoadFoto(obj) {
	var expr=30;
	if(jQuery(obj).attr('checked'))
		expr=-30;
	setCookie('checkloadfoto','0',expr);
	window.location = location.href;
}

function showBoardImg(obj,src) {
	jQuery(obj).append('<img src="'+src+'" alt="">').attr({'onclick':'','style':''});
}

function loadFormComm(obj,oid,modul) {
	if(jQuery('div.form_'+modul+'').size())
		commAnswerForm(0,oid,modul);
	else
		JSWin({'href':'/_js.php', 'data':'_modul='+modul+'&_view=add&_oid='+oid+'&_pid=', 'insertObj':obj, 'insertType':'after', 'call':function(){jQuery(obj).hide();}});
}

function commanswer(pid,oid,modul) {
	if(jQuery('div.form_'+modul+'').size())
		commAnswerForm(pid,oid,modul);
	else
		JSWin({'href':'/_js.php', 'data':'_modul='+modul+'&_view=add&_oid='+oid+'&_pid='+pid, 'insertObj':'span.button_comm', 'insertType':'after', 'call':function(){commAnswerForm(pid,oid);}});
	return false;
}
function commAnswerForm(pid,oid,modul) {
	jQuery('div.commformanswer').css('display','none');
	if(pid==0) {
		jQuery('#tr_sbmt input').val(jQuery('span.button_comm').text());
		jQuery('div.form_'+modul).css('position','');
		jQuery('span.button_comm').hide();
		jQuery('div.form_'+modul+' #parent_id').val(0);
	}
	else {
		if(jQuery('div.form_'+modul+'').css('position')!='absolute') {
			jQuery('span.button_comm').show();;
			jQuery('#tr_sbmt input').val('Написать ответ');
		}
		jQuery('div.commformanswer').css('display','none');
		var paramW = jQuery('div.form_'+modul+'').attr('clientWidth');
		var paramH = jQuery('div.form_'+modul+'').attr('clientHeight')+15;
		var pos = jQuery('#commitem'+pid+' > .commformanswer').css({'display':'block', 'width':paramW+'px', 'height':paramH+'px'}).position();
		jQuery('div.form_'+modul+'').css({'position':'absolute','top':pos['top']+'px','left':pos['left']+'px'});
		jQuery('div.form_'+modul+' #parent_id').val(pid);
	}
	return false;
}


function show_params(selector,obj) {
	if(jQuery(obj).hasClass('hideparam')) {
		jQuery(selector).hide();
		jQuery(obj).removeClass('hideparam');
		jQuery(obj).find('span').eq(0).show();
		jQuery(obj).find('span').eq(1).hide();
	}
	else {
		jQuery(selector).show();//.css('formparam');
		jQuery(obj).addClass('hideparam');
		jQuery(obj).find('span').eq(0).hide();
		jQuery(obj).find('span').eq(1).show();
	}
}

var timerid3;
function boardrubric(obj,id) {
	clearTimeout(timerid3);
	timerid3 = setTimeout(function(){boardrubricExe(obj,id);},400);
	return true;
}

function boardrubricExe(obj,id) {
	var objAfter;
	if(!obj) return 0;
	objAfter = '#tr_'+obj;
	obj=jQuery('select[name='+obj+']');

	if(!id) id='';
	jQuery('.addparam').remove();
	JSWin({'href':'/_js.php?_modul=board&_view=boardlist&_id='+id+'&_rid='+jQuery(obj).val(),'insertObj':objAfter,'body':objAfter,'insertType':'after'});
	return true;
}

function boardexport(region) {
	var objAfter = '#tr_boardexport';
	jQuery('div.boardexport').remove();
	jQuery('#tr_boardexport').hide();
	if(region) 
		JSWin({'href':'/_js.php?_modul=board&_view=boardexport&_region='+region,'insertObj':objAfter,'body':objAfter,'insertType':'after'});
	return true;
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
	jQuery('#tr_param > div input').val(valu);
	jQuery('#tr_param > div > a').text(txt);
	fShowload(0);
	return false;
}

/*пОКАЗ форму загрузки фото*/
function shownextfoto(crnt,nxt){
	jQuery('#tr_'+nxt).show();
	jQuery(crnt).remove();
}


/*подсветка обязательных полей*/
function rclaim(nm) {
	var val=jQuery('select[name='+nm+']').val();
	if(val==1 || val==3 || val==5)
		jQuery('.addparam span.form-requere').css('display','none');
	else{
		jQuery('.addparam span.form-requere').css('display','');
	}
}
/*Слайдер для фильтра, возможность установки чесловых пределов*/
function gSlide (id,_min,_max,val0, val1,stp) {
		if(!_max && val1) _max = val1*3;
		else if(!_max) _max = 100;
		if(!val1) val1 = _max;
		jQuery('#'+id+' .f_value').after("<div id='slide"+id+"'></div>");
		jQuery('#slide'+id).slider({
			range: true,
			step: stp,
			min: _min,
			max: _max,
			values: [val0, val1],
			slide: function(event, ui) {
				jQuery('#'+id+' .f_value input').eq(0).val(ui.values[0]);
				jQuery('#'+id+' .f_value input').eq(1).val(ui.values[1]);
			}
		});
		jQuery('#'+id+' .f_value input').eq(0).val(val0);
		jQuery('#'+id+' .f_value input').eq(1).val(val1);
		jQuery('#'+id+' .f_value input').eq(0).bind('change',function (){jQuery('#slide'+id).slider( 'values' , 0 , this.value)});
		jQuery('#'+id+' .f_value input').eq(1).bind('change',function (){jQuery('#slide'+id).slider( 'values' , 1 , this.value)});
}



/*Событие на клике чекбокса*/
function multiCheckBox() {
	if(this.name=='null') {
		//var tt = new Date();
		//var ttt = tt.getTime();

		jQuery(this.parentNode.parentNode).find('input[name!=null][checked=true]').each(function(){
			var tmp = this.name.substring(0,this.name.indexOf('[]'));
			tmp = jQuery('#form_tools_paramselect input[name="'+tmp+'_'+this.value+'[]"]:first');
			if(tmp.length)
				jQuery(tmp).parent().parent().parent().remove();
			jQuery(this).removeAttr('checked');
		});

		//var tt = new Date();
		//console.log(tt.getTime()-ttt);
	}else
		jQuery(this.parentNode.parentNode).find('input[name=null]').removeAttr('checked');
}
/*Скрытия части элементов. дабы не заполнять экран*/
function mCBoxShortHide(oid) {
	jQuery('#form_tools_paramselect input[name="param_'+oid+'[]"]').each(function(){
		if(!vfmcb[this.value] && !this.checked) {
			jQuery(this).parent().hide();
		}
	});
	jQuery('#form_tools_paramselect input[name="param_'+oid+'[]"]:first').parent().parent().append('<span onclick="mCBoxShortShow('+oid+')" class="ajaxlink">Показать всё</span>');
}
/**показ всех эелементов*/
function mCBoxShortShow(oid) {
	jQuery('#form_tools_paramselect input[name="param_'+oid+'[]"]').each(function(){
		jQuery(this).parent().show(); 
	});
	jQuery('#form_tools_paramselect input[name="param_'+oid+'[]"]:first').parent().parent().find('span.ajaxlink').remove();
}

//добавляем кнопку с возможность скрытия блока фильтра , также скрываем блоки котрые были скрыты до этого (куки)
function mCBoxVis(oid) {
	jQuery('#form_tools_paramselect input[name="param_'+oid+'[]"]:first').each(function(){
		var obj = jQuery(this).parent().parent(); 
		if(getCookie('armCBox'+oid)) {
			jQuery(obj).prev().addClass('foldedcap');
			jQuery(obj).hide();
		}
		jQuery(obj).prev().addClass('unfoldedcap').click(function() {
			jQuery(this).toggleClass('foldedcap');
			jQuery(obj).slideToggle('slow');
			if(getCookie('armCBox'+oid))
				setCookie('armCBox'+oid,0,-1);
			else
				setCookie('armCBox'+oid,1);
		});

	});
}

/*Загрузка подуровней чекбокса*/
function mCBoxCA(oid) {
	jQuery('#form_tools_paramselect input[name="param_'+oid+'[]"]').change(mCBoxCAClick);
}
function mCBoxCAClick() {
	if(this.checked) {
		JSWin({'href':'_js.php','data':{'_view':'mCBox','tname':this.name,'tval':this.value,'tcap':jQuery(this.parentNode).text()},'insertType':'after','insertObj':this.parentNode.parentNode.parentNode});
	}
	else {
		var tmp = this.name.substring(0,this.name.indexOf('[]'));
		jQuery('#form_tools_paramselect input[name="'+tmp+'_'+this.value+'[]"]:first').parent().parent().parent().remove();
	}
}

//функция обрабоки данных фильтра при его изменении значении 
function filterChange() {
	var pos = jQuery(this).offset();
	var top = pos.top - 84;
	var obj = jQuery('div.leftblock div.shres');
	if(obj.length)
		$(obj).html('Поиск').attr({'class':'shres shresload','style':'top:'+top+'px;left:252px;'});
	else {
		jQuery('div.leftblock').append('<div class="shres shresload" style="top:'+top+'px;left:252px;">Поиск</div>');
		obj = jQuery('div.leftblock div.shres');
	}
	JSWin({'href':'_js.php?_view=filterChange','data':jQuery(document.forms.form_tools_paramselect).serialize(),'insertObj':obj});
}

/***********/
/***UTILS***/
/***********/

wep.fLog = function(txt,flag) {
	if(jQuery('#debug_view').size())
		jQuery('#debug_view').html(txt+jQuery('#debug_view').html());
	else
		jQuery(".maintext .block").prepend("<div id='debug_view' style='border:1px solid blue	;'>"+txt+"</div>");
	if(flag==1) wep.fShowHide('debug_view',1);
};


wep.fMessPos = function(body,obj) {
	if(body) body=body+'>';
	else body = '';
	if(!obj) obj='#ajaxload';
	jQuery(body+obj).css('width','auto');
	var H=document.documentElement.clientHeight;
	var FC = jQuery(body+obj+':first');
	//alert(FC.text());
	var Hblock= FC.outerHeight();
	if(typeof Hblock == 'undefined') return;
	var hh=Math.round((H-Hblock)/2);
	if(hh<5) hh=5;
	var W=document.documentElement.clientWidth;
	var Wblock= FC.outerWidth();
	var ww=Math.round((W-Wblock)/2);
	if(ww<5) ww=5;
	jQuery(body+obj).css({'top':hh+'px','left':ww+'px'});
	if(Hblock>H) {
		Hblock = Hblock - 30;
		jQuery(body+obj).css({'height':(Hblock+5)+'px'});
	}
	if(Wblock>W) 
		Wblock = Wblock - 30;
	jQuery(body+obj).css({'width':(Wblock+5)+'px'});
};



/*SPOILER*/
function clickSpoilers(obj) {
	jQuery(obj).toggleClass('unfolded');
	jQuery(obj).next('div.spoiler-body').slideToggle('fast');
}

/*END SPOILER*/



/************************/

function pagenum(total,order) {
	if(!order) order = false;
	if(jQuery('.ppagenum').size()>0 && total>10) {
		$.includeCSS('/_design/_style/style.jquery/paginator.css');
		pg = 1;
		reg = /_p([0-9]*)/;
		tmp = reg.exec(lochref);
		if(tmp)
			pg =  tmp[1];
		else if(!tmp && order)
			pg = total;
		if(!tmp)
			reg = /(.*)\.html(.*)/g;
		else
			reg = /(.*)_p[0-9]*\.html(.*)/g;
		tmp = reg.exec(lochref);
		//alert(dump(tmp));
		$.include('/_design/_script/script.jquery/paginator.js',function(){
			jQuery('.pagenum').css('display','none');
			jQuery('.ppagenum').css('display','block').paginator({pagesTotal:total, 
				pagesSpan:8,
				returnOrder:order,
				pageCurrent:pg, 
				baseUrl: function (page){
					loc = tmp[1];
					if((!order && page!=1) || (order && page!=total))
						loc += '_p'+page;
					loc += '.html'+tmp[2];
					window.location.href = loc;
				}
			});
		});
	}
}

function invert_select(form_id)
{
	jQuery('#'+form_id+' input[type=checkbox]').each(function() {
		this.checked = !this.checked;
	});
	return false;
}

var setMap;//объект карты
var setPlacemark; // Метка
var setToolbar;
var viewMap=0;
function boardOnMap(tp) {
	if(tp) viewMap = tp;
	if(jQuery('#boardOnMap').size()==0) {
		jQuery('body').append('<div id="boardOnMap" style="display:none;"><div class="layerblock"><div onclick="delMap()" class="blockclose">&#160;</div><div id="YMapsID" style="width:700px;height:500px;background-color: white;"></div></div></div>');
	}
	jQuery('#boardOnMap').show().css('height','auto');
	showBG(0,1);
	fMessPos(0,'#boardOnMap');
	if(!setMap) {
		YMaps.load(initMap);
	}
}

function initMap() {
	//jQuery('body').append('<div id="boardOnMap"><div class="layerblock"><div id="YMapsID" style="width:600px;height:400px;background-color: white;"></div></div></div>');
	// Создает обработчик события window.onLoad
	YMaps.jQuery(function () {
// Создает экземпляр карты и привязывает его к созданному контейнеру
		if(!setMap) setMap = new YMaps.Map(YMaps.jQuery("#YMapsID")[0]);
		
		// Устанавливает начальные параметры отображения карты: центр карты и коэффициент масштабирования
		var mapx = jQuery('#mapx').val();
		var mapy = jQuery('#mapy').val();
		var flag = true;
		if(mapx==0) {
			if (YMaps.location) {
				mapx = YMaps.location.longitude;
				mapy = YMaps.location.latitude;
			} else {
				mapx = 37.64;
				mapy = 55.76;
			}
			flag= false;
		}
		setMap.setCenter(new YMaps.GeoPoint(mapx, mapy), 10);
		
		if(!setPlacemark) {
			var opt = {};
			if(viewMap==0)
				opt.draggable = true;
			setPlacemark = new YMaps.Placemark(new YMaps.GeoPoint(mapx, mapy), opt);
			setPlacemark.name = "Метка";
			YMaps.Events.observe(setPlacemark, setPlacemark.Events.PositionChange, function (obj,Point) {
				jQuery('#mapx').val(Point.newPoint.__lng);jQuery('#mapy').val(Point.newPoint.__lat);
			}, setMap);
		}
		if(flag) {
			setPlacemark.setGeoPoint(new YMaps.GeoPoint(mapx, mapy));
			setMap.addOverlay(setPlacemark);
		}

		if(!setToolbar) {
			// Создает панель инструментов без кнопки "Линейка"
			setToolbar = new YMaps.ToolBar([
				new YMaps.ToolBar.MoveButton(), 
				new YMaps.ToolBar.MagnifierButton()
			]);


			//////////
			var button2 = new YMaps.ToolBarToggleButton({ 
				icon: "http://api.yandex.ru/i/maps/icon-fullscreen.png", 
				hint: "Разворачивает карту на весь экран"
			});
			// Если кнопка активна, разворачивает карту на весь экран
			YMaps.Events.observe(button2, button2.Events.Select, function () {
				setSize(1024, 768);
			});
			// Если кнопка неактивна, устанавливает фиксированный размер карты
			YMaps.Events.observe(button2, button2.Events.Deselect, function () {
				setSize(700, 500);
			});
			// Функция устанавливает новые размеры карты
			function setSize (newWidth, newHeight) {
				YMaps.jQuery("#YMapsID").css({
					width: newWidth || "", 
					height: newHeight || ""
				});
				setMap.redraw();
				fMessPos(0,' #boardOnMap');jQuery('#boardOnMap').css('height','auto');
			}
			setToolbar.add(button2);
			
			if(viewMap==0) {
				//////////////
				var button = new YMaps.ToolBarToggleButton({ 
					caption: "Добавить метку", 
					hint: "Добавляет метку в центр карты"
				});
				YMaps.Events.observe(button, button.Events.Select, function () {
					//button.setContent('Удалить метку');
					var center = setMap.getCenter();
					jQuery('#mapx').val(center.__lng);jQuery('#mapy').val(center.__lat);
					setPlacemark.setGeoPoint(center);
					this.addOverlay(setPlacemark);
				}, setMap);
				YMaps.Events.observe(button, button.Events.Deselect, function () {
					this.removeAllOverlays();
				}, setMap);
				setToolbar.add(button);
			}

			// Добавление панели инструментов на карту
			setMap.addControl(setToolbar);
			setMap.addControl(new YMaps.Zoom());
            setMap.addControl(new YMaps.TypeControl());
			setMap.addControl(new YMaps.SearchControl());
            setMap.enableScrollZoom();
			setMap.enableRightButtonMagnifier();
		}
	});
}
// Функция для отображения результата геокодирования
// Параметр value - адрес объекта для поиска
function showAddress (value) {
	// Удаление предыдущего результата поиска
	map.removeOverlay(geoResult);
	// Запуск процесса геокодирования
	var geocoder = new YMaps.Geocoder(value, {results: 1, boundedBy: map.getBounds()});
	// Создание обработчика для успешного завершения геокодирования
	YMaps.Events.observe(geocoder, geocoder.Events.Load, function () {
		// Если объект был найден, то добавляем его на карту
		// и центрируем карту по области обзора найденного объекта
		if (this.length()) {
			geoResult = this.get(0);
			//map.addOverlay(geoResult);
			map.setBounds(geoResult.getBounds());
		}else {
			alert("Ничего не найдено")
		}
	});

	// Процесс геокодирования завершен неудачно
	YMaps.Events.observe(geocoder, geocoder.Events.Fault, function (geocoder, error) {
		alert("Произошла ошибка: " + error);
	})
}

function delMap() {
	jQuery('#boardOnMap').hide();
	showBG();
	//setMap.destructor();
}