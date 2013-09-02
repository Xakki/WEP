

/***************************/
/*  Главный набор скриптов */
/***************************/


// true - чтобы отключить все логи
if(typeof(isProduction)=='undefined')
	isProduction = false;

if(!isProduction) {
	var hash = location.hash;
	if(hash.indexOf('#console')!=-1) {
		isProduction = true;
	}
}

/**
* Проверяем существование необходимых консольных функции в браузере
*/
if(typeof(console)=='undefined')
    console = {};

if(isProduction)
{
    console.log = function() {};
    console.error = function() {};
    console.assert = function() {};
    console.timeEnd = function() {};
    console.time = function() {};
}
else if(typeof(opera)!="undefined")
{
    console.log = console.log || function() {opera.postError(arguments);};//opera.postError(comm);
    console.error = console.error || function() {opera.postError(arguments);};
    console.assert = console.assert || function() {if(!arguments[0]) opera.postError(arguments);};
    console.timeEnd = console.timeEnd || function() {};
    console.time = console.time || function() {};
}
else
{
    console.log = console.log || function() {};
    console.error = console.error || function() {/*TODO send error mess to server*/};
    console.assert = console.assert || function() {};
    console.timeEnd = console.timeEnd || function() {};
    console.time = console.time || function() {};
}




// ф для работы с JSON
var JSON = JSON || {};
// implement JSON.stringify serialization
JSON.stringify = JSON.stringify || function (obj) {
    var t = typeof (obj);
    if (t != "object" || obj === null) {
        // simple data type
        if (t == "string") obj = '"'+obj+'"';
        return String(obj);
    }
    else {
        // recurse array or object
        var n, v, json = [], arr = (obj && obj.constructor == Array);
        for (n in obj) {
            v = obj[n]; 
            t = typeof(v);
            if (t == "string") v = '"'+v.replace(/"+/g,'&quot;')+'"';
            else if (t == "object" && v !== null) v = JSON.stringify(v);
            json.push((arr ? "" : '"' + n + '":') + String(v));
        }
        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
};

// implement JSON.parse de-serialization
JSON.parse = JSON.parse || function (str) {
    if (str === "") str = '""';

    str = str.replace(/&quot;+/g,'\'');
    console.log(str);
    eval("var p=" + str + ";");
    return p;
};


window.KEY = {
    UP: 38,
    DOWN: 40,
    DEL: 46,
    TAB: 9,
    RETURN: 13,
    ESC: 27,
    COMMA: 188,
    PAGEUP: 33,
    PAGEDOWN: 34,
    BACKSPACE: 8,
    LEFT: 37,
    RIGHT: 39
};

window.wep = {
	ready: false,
	version: '0.1.2',/*Версия скрипта*/
	BH:'',
	DOMAIN:'',
	HREF_style:'/_design/_style/',
	HREF_script:'/_design/_script/',
	pgId:0,/* ID текущей страницы (загружается из onLOAD)*/
	pgParam: [],/* параметры текущей страницы (загружается из onLOAD)*/
	pgGet : {}, // GET параметры
	siteJS: "/_js.php",
	form: {},/*Функции работы с формой*/
	popUp: {
        onclk: true,
		fadeobj: '.PopUp',
		insertobj: '.PopUp .PopUpContent',
		template: '<div class="PopUp"><div class="PopUpBackgound CloseTarget"></div><div class="PopUpLoading">Загружаю...</div><div class="PopUpContent"></div></div>'
	},
	isDef: function(v) {
		return typeof v !== 'undefined';
	},
	// START DOM
	init: function()
	{
		if(this.ready)
			return false;
		var tmp = wep.getCookie(wep.wepVer);
		if(tmp==false){
			wep.setCookie(wep.wepVer, document.referrer);
		}

		wep.clickAjax('a.isAjaxLink');
	},
	/**
	* Ссылка с подтверждением
	*/
	click: function(selector)
	{
		$(selector).click(function() {
			wep.clickConfirm($(this));
		});
	},
	clickConfirm: function(obj)
	{
		var data = obj.attr('data-send');
		var href = obj.attr('href');
		var confirmMess = obj.attr('data-confirm');
		
		if(!confirmMess || confirm(confirmMess))
		{
			if(!href)
				href = location.href;
			if(!strpos(href, '?'))
				href += '?';
			else
				href += '&';
			if(data)
				href += data;
			location.href = href;
		}
	},

	clickAjax: function(selector)
	{
		$('body').off('click', selector).on('click', selector, function() {
			wep.ajaxMenu(this);
			return false;
		});
	},
	/**
	* Аякс загрузка любой страницы
	* атрибут у ссылки 
	* * data-marker - указывает какой маркер загружать, по умолчанию 'text'
	* * data-ajax - по умолчанию 'popup' , загружает в popup окошко, и затемняет фон ; иначе этот атрибут используется как селектор
	*/
	ajaxMenu: function(obj) 
	{
		var jobj = $(obj);


        var data = {};
		var dataData = jobj.attr('data-data');
		if(dataData)
		{
			dataData = dataData.toString().split ( '&' );
			for(var i in dataData)
			{
				var temp = dataData[i].toString().split ( '=' );
                data[temp[0]] = temp[1];
			}
		}
		var dataMarker = jobj.attr('data-marker');
		if(dataMarker)
            data['PGMARKER'] = dataMarker;
		else
            data['PGMARKER'] = 'text';

		// marker.push('onload');
		// marker.push('styles');
		// marker.push('script');

		var param = {};
		var attr = jobj[0].attributes;

		for (var i = 0; i < attr.length; i++)
		{
            if(attr[i].name!='data-marker' && attr[i].name!='data-data' && attr[i].name!='class') {
			    param[attr[i].name] = attr[i].value;
            }
		}

		param['type'] = jobj;
		param['data'] = data;
		wep.JSWin(param);
		return false;
	},

	/**
	* Загрузка(обновление) определенных контейнеров АЯКСОМ на текущей страницы
	*/
	ajaxLoadPage: function(obj, marker, call) 
	{
		marker['onload']=1;
		marker['styles']=1;
		marker['script']=1;
		// TODO marker = wep.pgGet + marker;
		param = {
			'href': location.href,
			'type': 'GET',
			'data': { PGMARKER : marker }
		};

		if(call)
			param['call'] = call;

		wep.JSWin(param);
		return false;
	},

	/**
	* Загрузка(обновление) определенного контента АЯКСОМ на текущей страницы
	*/
	ajaxLoadContent: function(domObject,paramCustom) {

		if(!$(domObject).hasClass('wep-pgc')) {
			return false;
		}
		var ctId = substr($(domObject).attr('id'),3);
		if(!ctId) return false;

		param = {
			href : location.href,
			type : 'GET',
			data : {'PGCID':ctId},
			marker : 'PGCID',
			insertobj : $(domObject),
			inserttype : 'replace'
		};

		$.extend(param, paramCustom);

		wep.JSWin(param);

		return false;
	},

	/*
	* Аякс отправка формы с примитивными полями
	* @param obj - объект формы 
	* @param param - дополнительные параметры запроса
	* TODO : отправка фаилов флешом
	*/
	jsForm: function(obj,param) { 
		/*Отлавливаем клик и ставим пометку*/
		$(obj).find('[type=submit],[type=image]').bind('click',function(e){
			$(this).attr('data-click','1');
			return true;
		});
		$(obj).bind('submit',function(e) {
			param['type']  = this;
			var sbmt = $(this).find('[data-click=1]');
			param['sbmt']  = sbmt.attr('name')+'='+sbmt.attr('value');// передаем данные о кнопке на которую нажали
			sbmt.removeAttr('data-click');
			JSWin(param);
			return false;
		});
	},
	/*
	* Аякс запрос
	* @param param - параметры запроса
	** param['type'] 
	*    1) - передача объекта ССЫЛКИ (тип запроса будет GET по ссылке указанный в атрибуте href )
	*    1) - передача объекта ФОРМЫ (тип запроса указанный в атриб. method  по ссылке указанный в атрибуте action, и передаются данные из элеменотов форм )
	*    1) - передача строки GET либо POST (по умолчанию GET)
	*
	* trigger
	* * ajaxSuccess - Получен ответ по аяксу, выполняем самым первым
	* * fShowloadContent - Если отрисован полученный контент
	* * execLoadFunction - После получения ответа, загружаются скрипты и тогда выполняется
	*
	*
	*/
	JSWin: function(param) 
	{
		wep.setDefaultParam(param);
		
		wep.loadAnimationOnAjax(param);

		$.ajax({
			type: param['type'],
			url: param['href'],
			data: param['data'],
			datatype: param['datatype'],
			/*beforeSend: function(XMLHttpRequest) {
				return true;
			},*/
			404: function() {
		      alert("page not found");
		    },
			error: function(XMLHttpRequest, textStatus, errorThrown) 
			{
				if(XMLHttpRequest.responseText)
				{
					var result = {};
					if(XMLHttpRequest.responseText.substr(0, 1)=='{')
						result = JSON.parse(XMLHttpRequest.responseText);
					else
					{
						result['text'] = XMLHttpRequest.responseText;
					}
					if(result['text'])
					{
						// this.success(response, textStatus, XMLHttpRequest);
						wep.ajaxSuccess(result, param);
						return true;
					}
				}
                console.error(result);
                console.log(param);
				console.log(XMLHttpRequest);
				console.log(textStatus);
				console.log(errorThrown);
				return false;
			},
			/*dataFilter: function(data, type) {
				return data;
			},*/
			success: function(result, textStatus, XMLHttpRequest){
				wep.ajaxSuccess(result, param);
			}
		});
		return false;
	},

	/**
	* Задаем параметры по умолчанию для АЯКС ЗАГРУЗКИ
	*/
	setDefaultParam: function(param)
	{
		if(typeof param['type']=='object') {
			var OBJ = jQuery(param['type']);
			if(OBJ.get(0).tagName=='A') 
			{
				if(!param['href'])
					param['href'] = OBJ.attr('href');
				param['type'] = 'GET'; // always GET
			}
			else // FORM
			{
				param['href'] = OBJ.attr('action');
				param['data'] = OBJ.serialize();

				if(!param['sbmt'])
					param['data'] += '&sbmt=1'
				else
					param['data'] += '&'+param['sbmt']
				param['type'] = OBJ.attr('method');
				if(!param['type']) param['type'] = 'POST'; // default
			}
		}
		else if(!param['type']) 
			param['type'] = 'GET';

		// Линк
		if(!param['href'])		
			param['href'] = location.href;
		
		// данные для передачи в зарпрос
		if(!param['data']) 		
			param['data'] = '';

		// Это попап окно?
		if(typeof(param['wrapTitle'])=='undefined') 		
			param['wrapTitle'] = 0;

		// Тип получаемых данных
		if(!param['datatype'])	
			param['datatype'] = 'json';

		// Если нужно отключить затемнение после завершения
		if(typeof param['fadeoff'] == 'undefined') 
		{ // зНачение по умолчанию
			if(!param['insertobj']) //Если обект для вставки не задан, то будет всплывающее окно и затемнение не убираем
				param['fadeoff'] = false; 
			else // иначе после выполнения убираем затемнение
				param['fadeoff'] = true;
		}

		// маркер полученного содержимого из принятого массива
		if(!param['marker']) {
			if(typeof(param.data)=='object' && param.data.PGMARKER) {
				param['marker'] = param.data.PGMARKER;
			}
			else {
				param['marker'] = 'text';
			}
		}
		// Заголовок для Popup
		if(!param['markerTitle']) 		
			param['markerTitle'] = 'title';
		// По умолчанию задаем обертку
		/*if(typeof param['wraper'] == 'undefined')
		{
			param['wraper'] = 1;
			param['set_content_position'] = 1;
		}*/

		if(typeof param['set_content_position'] == 'undefined')
			param['set_content_position'] = 0;

		//область затемнения [false,true,object]
		if(typeof param['fadeobj'] == 'undefined' && param['insertobj'])
			param['fadeobj'] = param['insertobj'];

		// POPUP 
		// объект в который(в зависимости от param['inserttype']) будут вставляться result.html
		if(!param['insertobj']) 
		{
			param['insertobj'] = wep.popUp.insertobj;
			param['fadeobj'] = wep.popUp.fadeobj;
			param['wraper'] = 1;
			param['wrapTitle'] = 1;
			param['fadeoff'] = false; 
			param['onclk'] = 'close'; 
			param['set_content_position'] = 1; 
			
			wep.createPopUp();
		}

		param['timeBG'] = 0; // таймер
	},

	/**
	*
	*/
	ajaxSuccess: function(result, param) 
	{
		console.error('!!!! ajaxSuccess ', [result, param]);
		if(result.redirect) 
		{
			window.location.href = result.redirect;
			return false;
		}
		else if(result.redirectConfirm) 
		{
			if(confirm('Редирект на '+result.redirectConfirm))
			{
				window.location.href = result.redirectConfirm;
			}
			return false;
		}

		jQuery(param['insertobj']).trigger('ajaxSuccess', [result, param]);

		//return false;
		 // подключение Стилей
		if(typeof(result.styles) != 'undefined')  {
			wep.cssLoad(result.styles);
		}

		// WTF?
		if(typeof(result.param) == 'object')  {
			for(var i in result.param) {
				param[i] = result.param[i];
			}
			
		}

		wep.helperCallBackFunction(param['precall'], result, param);

		// Show content
		wep.fShowloadContent(result, param);

		wep.helperCallBackFunction(param['aftercall'], result, param);

		// Вывод ошибок и прочего текста
		if(typeof (result.logs) != 'undefined' && result.logs!='') 
			fLog(fSpoiler(result.logs,'AJAX text result'),1);

		 // подключение скриптов
		if(typeof (result.script) != 'undefined')  {
			//console.log(result.script);
			wep._loadCount = 0;
			wep.scriptLoad(result.script);
		}

		wep.timerExecLoadFunction(result, param);
	},

	timerExecLoadFunction: function (result, param) 
	{
		if(wep._loadCount!=0)
		{
			console.log('--in process--'+wep._loadCount);
			setTimeout(function(){wep.timerExecLoadFunction(result, param);},500);
		}
		else 
		{
			console.log('--READY--'+wep._loadCount);
			wep.execLoadFunction(result, param);
		}
	},

	// Выполнение функции при полной загрузке
	execLoadFunction: function (result, formParam) 
	{
		jQuery(formParam['insertobj']).trigger('execLoadFunction', [result, formParam]);

		//Запуск функции пользователя
		// if(typeof formParam['call'] != 'undefined') 
		// {
		// 	if(typeof formParam['call'] == 'function')
		// 		formParam['call'].call(this, result, formParam);
		// 	else if(typeof formParam['call'] == 'string')
		// 		eval(formParam['call']+'(result, formParam);');
		// }
		wep.helperCallBackFunction(formParam['call'], result, formParam);
		
		 // запуск onload функции
		if(typeof result.onload != 'undefined')  {
			if(typeof result.onload == 'function')
				result.onload.call(this, result, formParam);
			else if(result.onload!='') {
				eval(result.onload);
			}
		}

		wep.loadAnimationOffAjax(formParam);

		jQuery(formParam['insertobj']).trigger('theend', [result, formParam]);
	},

	helperCallBackFunction: function(call, result, param)
	{
		//функц. предзапуска 
		if(typeof call != 'undefined') 
		{
			if(typeof call == 'function')
				call.call(this, result, param);
			else if(typeof call == 'string')
				eval(call+'(result, param);');
		}
	},

	/**
	* Отображает контент
	*/
	fShowloadContent: function(result, param) 
	{
		if(typeof(param['marker'])=='object') {
			param['marker'] = 'text';
			console.log('TODO: marker');
			//return;
		}

		if(!result[param['marker']])
			return;

		if(param['insertobj'])
			jQuery(param['insertobj']).trigger('fShowloadContent', [result, param]);

		var htmlOut = result[param['marker']];

		if(param['wrapTitle'])
		{
			// Обертка для попапа
			if(result[param['markerTitle']])
				htmlOut = '<div class="blockhead">'+result[param['markerTitle']].substr(0,strpos(result[param['markerTitle']],' -') )+'</div><hr/>'+htmlOut;
		}

		if(param['wraper']) 
			htmlOut = wep.wraperLayerBlock(htmlOut, param);

		if(param['insertobj']) 
		{
			// if(typeof (htmlOut) != 'undefined' && htmlOut!='')
			if(param['inserttype']==='after') // вставка до
				jQuery(param['insertobj']).after(htmlOut);
			else if(param['inserttype']==='before') // Вставка после
				jQuery(param['insertobj']).before(htmlOut);
			else if(param['inserttype']==='replace') // Замена
				jQuery(param['insertobj']).replaceWith(htmlOut);
			else //Внутрь контейнера
				jQuery(param['insertobj']).html(htmlOut);
			//param['fadeobj'] = false;
		}
		/*else if (typeof(param['data'])=='object')
		{
			// Функция обратного вызова при получении данных от аякса
			for(var i in param['data']) {
				if(result[i] && param['data'][i] && typeof(param['data'][i])=='string') {
					jQuery(param['data'][i]).html(result[i]);
				}
			}
		}
		else
			param['fadeoff'] = true;*/

		return false;
	},

	loadAnimationOnAjax: function(param)
	{
		if(param['fadeobj'])  // Вешаем затемнение
		{
			wep.openPopUp(param);

			jQuery(param['fadeobj']).addClass('ajaxProccess');

			param['timeBG'] = setTimeout(function() 
			{
				jQuery(param['fadeobj']).addClass('loadAnimation').addClass('fadeBackgraund');
				param['timeBG'] = null;
			},200);
		}
	},

	loadAnimationOffAjax: function(param)
	{
		if(param['set_content_position'])
		{
			wep.fMessPos(param['insertobj']);
			setTimeout(function(){wep.fMessPos(param['insertobj']);},300);
		}
		///////////////////////////////

		//Если включено затемнение
		if(param['fadeobj']) 
		{
			// Если  таймер затемения ещё не сработал, то откл таймер
			if(param['timeBG'])
				clearTimeout(param['timeBG']); // Чистим таймер и тем самым затеменение не отобразиться
			else
				jQuery(param['fadeobj']).removeClass('loadAnimation');

			// Если нужно отключить затемнение после завершения
			if(param['fadeoff']) 
				jQuery(param['fadeobj']).removeClass('fadeBackgraund popupActive');

			wep.setEventClosePopUp(param);

			jQuery(param['fadeobj']).removeClass('ajaxProccess');
		}
	},

	/* Показываем попап */
	openPopUp: function(param)
	{
		if(param['fadeobj'])
			jQuery(param['fadeobj']).addClass('popupActive');

		if (_Browser.type == 'IE' && 8 > _Browser.version)
			jQuery('select').toggleClass('hideselectforie7',true);
	},

	/* Закрываем попап */
	closePopUp: function(param)
	{
		if(param['onclk']==='reload')
		{
			window.location.reload();
			return false;
		}

		if(param['fadeobj'])
			jQuery(param['fadeobj']).removeClass('popupActive loadAnimation fadeBackgraund ajaxProccess');

		if (_Browser.type == 'IE' && 8 > _Browser.version)
			jQuery('select').toggleClass('hideselectforie7',false);
	},

	/* Событие на закрытие попап окна */
	setEventClosePopUp: function(param)
	{
		if(param['onclk'])
		{
			jQuery(param['insertobj']).off('click.close').on('click.close', '.CloseTarget', function(e){
				wep.closePopUp(param);
				return false;
			});

			// if(param['fadeobj']!==param['insertobj'])
				jQuery(param['fadeobj']).off('click.close').on('click.close', '.CloseTarget', function(e){
					wep.closePopUp(param);
					return false;
				});
		}

	},

	/* Обертка для с кнопкой закрытия */
	wraperLayerBlock: function(txt, param)
	{
		return '<div class="blockclose CloseTarget"></div><div class="layerblock">'+txt+'</div>';
	},
	
	createPopUp: function()
	{
		if(!jQuery(wep.popUp.insertobj).size())
			jQuery('body').append(wep.popUp.template);
	},
	/**********************************/


	fShowloadReload: function() 
	{
		$('.ajaxload .blockclose').click(function(){window.location.reload();});
	},

	/**********************************/

	getBrowserInfo: function() {
		var t,v = undefined;
		if (window.opera) t = 'Opera';
		else if (document.all) {
			t = 'IE';
			var nv = navigator.appVersion;
			var s = nv.indexOf('MSIE')+5;
			v = nv.substring(s,s+2);
			v = wep.trim(v, ';.');
		}
		else if (navigator.appName) t = 'Netscape';
		return {type:t,version:v};
	},

	// Логи скриптов
	fLog: function(txt,flag) {
		if(jQuery('#bugmain').size())
			jQuery("#bugmain").prepend(txt);
		else {
			jQuery("body").prepend("<div id=\"bugmain\">"+txt+"</div>");
			wep.includeCSS('/_design/_style/bug.css');
			wep.include('/_design/_script/bug.js');
		}
		if(flag==1) wep.fShowHide('bugmain',1);
	},

	hTopPos: 20, 
	// Позициоонирует блок по центру
	fMessPos: function(obj) {
		if(!obj) obj='.ajaxload';
		var FC = jQuery(obj);
		if(!FC[0]) return false;

		jQuery(obj).css( {'width':'auto', 'height':'auto'} );

		var H=document.documentElement.clientHeight;
		var Hblock= FC[0].scrollHeight;
		if(typeof Hblock == 'undefined') return;
		var hh=Math.round((H-Hblock)/2);
		if(hh<wep.hTopPos) hh=wep.hTopPos;

		var W=document.documentElement.clientWidth;
		var Wblock= FC[0].scrollWidth;
		var ww=Math.round((W-Wblock)/2);
		if(ww<10) ww=10;

		jQuery(obj).css({'top':hh+'px','left':ww+'px'});

		if(Hblock>(H-hh)) {
			Hblock = H-hh;
			jQuery(obj).css({'height':(Hblock)+'px'});
		}
	
		if(Wblock>(W-20)) 
			Wblock = W;
		jQuery(obj).css({'width':(Wblock+22)+'px'});

		wep.setResize('fMessPos#'+obj, function() {
			if(jQuery(obj).size())
				wep.fMessPos(obj);
		});
	},

	// всплывающая подсказка
	showHelp: function(obj,mess,time,nomiga) 
	{
		if(!obj || !mess || !jQuery(obj).size() || jQuery(obj).next().attr('class')=='helpmess') return false;
		jQuery('div.helpmess').remove();
		var pos = jQuery(obj).offset();
		pos.top = parseInt(pos.top);
		pos.left = parseInt(pos.left);
		if(!time) 
			time = 5000;
		jQuery(obj).after('<div class="helpmess">'+mess+'<div class="trgl trgl_d"> </div></div>');//Вставляем всплыв блок
		var slct = jQuery(obj).next();// бурем ссылку на добавленный блок
		var H = jQuery(slct).height(); // Определяем его высоту
		var flag = pos.top-12-H;// Определяем абсолютную позицию элемента

		pos = jQuery(obj).position();//Далее будем работать только с относительной позицией , чтобы избежать ошитбки с позиционированим
		pos.top = parseInt(pos.top);
		pos.left = parseInt(pos.left);
		if(flag<5) {
			H = pos.top+10+jQuery(obj).height();//по новой высчитываем, и располагаем блогк снизу
			jQuery(slct).find('div.trgl').attr('class','trgl trgl_u');
		}
		else
			H = pos.top-12-H;//по новой высчитываем
		jQuery(slct).css({'top':H,'left':pos.left,'opacity':0.8});
		//if(!nomiga)
		//	miga(slct,0.8);

		setTimeout(function(){jQuery(slct).stop().fadeOut(300,function(){jQuery(slct).remove()});},time);
		//if(time>5000)
			jQuery(slct).click(function(){
				jQuery(slct).stop().fadeOut(300,function(){jQuery(slct).remove()});
			});
	},
	miga: function(obj,opc1,opc2)
	{
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
	},
	fSpoiler: function(txt,nm) {
		//initSpoilers();
		wep.includeCSS('/_design/_style/bug.css');
		if(!nm) nm ='Скрытый текст';
		return '<div class="bspoiler-wrap folded clickable"><div onclick="var obj=this.parentNode;if(obj.className.indexOf(\'unfolded\')>=0) obj.className = obj.className.replace(\'unfolded\',\'\'); else obj.className = obj.className+\' unfolded\';" class="spoiler-head">'+nm+'</div><div class="spoiler-body">'+txt+'</div></div>';
	},
	initSpoilers: function(context){
		context = context || 'body';
		jQuery('div.spoiler-head', jQuery(context))
			.click(function(){
				jQuery(this).toggleClass('unfolded');
				jQuery(this).next('div.spoiler-body').slideToggle('fast');
			});
	},
	fShowHide: function(id,f) {
		if(jQuery('#'+id).css('display')!='none' && !f)
			jQuery('#'+id).animate({ opacity: "hide" }, "slow");
		else
			jQuery('#'+id).animate({ opacity: "show" }, "slow");
	},

    /***/

    staticPopUp: function(content)
    {
        wep.createPopUp();
        $(wep.popUp.insertobj).html(wep.wraperLayerBlock(content));
        wep.staticOpenPopUp();
    },
    staticOpenPopUp: function()
    {
        wep.openPopUp(wep.popUp);
        wep.setEventClosePopUp(wep.popUp);
        wep.fMessPos(wep.popUp['insertobj']);
    },

	setCookie: function(name, value, expiredays, domain, path, secure) {

		if (!name) return false;
		if (!domain) domain = wep.DOMAIN;
		if (!path) path = '/';

		var str = name + '=' + encodeURIComponent(value);
		if(typeof expiredays=='undefined')
			expiredays = 999999;
		if (expiredays!=0) {
			var exdate=new Date();
			exdate.setDate(exdate.getDate()+expiredays);
			if (exdate) str += '; expires=' + exdate.toGMTString();
		}

		if (path)    str += '; path=' + path;
		if (domain)  str += '; domain=' + domain;
		if (secure)  str += '; secure';
		document.cookie = str;
		return true;
	},

	getCookie: function(name) {
		var pattern = "(?:; )?" + name + "=([^;]*);?";
		var regexp  = new RegExp(pattern);
		
		if (regexp.test(document.cookie))
			return decodeURIComponent(RegExp["$1"]);
		return false;

		/*var cookie = " " + document.cookie;
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
		return setStr;*/
	},

	deleteCookie: function(name, domain, path) {
		if (!domain) domain = wep.DOMAIN;
		if (!path) path = '/';
		this.setCookie(name, null, -100, domain, path);
		return true;
	},

	cssLoad: function(css) {
		for(var i in css) 
		{
			if(typeof css[i] == 'string' && css[i].substr(0,1)=='<')
				alert('Error css include');

			if (typeof i == 'string' && wep.isUrl(i))
			{
				wep.includeCSS(i);
			}

			if (typeof css[i] == 'string' && css[i])
			{
				if(wep.isUrl(css[i]))
				{
					wep.includeCSS(css[i]);
				}
				else
				{
					console.warn('CSS - ', css[i]);
					var style = document.createElement('style');
					style.innerHTML = css[i];
					document.getElementsByTagName('head')[0].appendChild(style);
				}
			}
			else if(typeof css[i] == 'object')
			{
				wep.cssLoad(css[i]);
			}

		}
	},
	includeCSSlist: {},
	includeCSS: function(css, onComplete) 
	{
		if(typeof(css)=='string')
			css = [css];
		var i = 1;
		var ii = css.length; 
		var onCssLoaded = function() 
		{
			if (i++ == ii && onComplete) 
				onComplete.call();
		} ; 
		for(var s in css) {
			var validSrc = wep.checkCSSInclude(css[s]);

			if(validSrc)
			{
				var styleCss = document.createElement('link');
				//styleCss.type = 'text/css';
				styleCss.rel = 'stylesheet';
				styleCss.href = validSrc;
				$(styleCss).ready(onCssLoaded);
				document.getElementsByTagName('head')[0].appendChild(styleCss);
			}
			else if ( onComplete )
				onComplete.call(this);
		};
		return true;
	},
	checkCSSInclude: function(url) {
		url = wep.absPath(url);
		if(jQuery.isEmptyObject(wep.includeCSSlist)) 
		{// проверка на уникальность подключаемого стиля
			wep.includeCSSlist[url] = 1;
			var flag = 0;
			jQuery('link[href!=""]').each(function(){
				var href = wep.absPath(this.href);
				wep.includeCSSlist[href] = 1;
				if(href==url) flag = 1;
			});
			if(flag == 1) {
				wep.includeCSSlist[url]=2;
				return false;
			}
		} 
		else 
		{
			if(wep.includeCSSlist[url]) {
				wep.includeCSSlist[url]++;
				return false;
			}
			else wep.includeCSSlist[url] = 1;
		}
		return url;
	},

	_onLoad: false,
	_loadCount:0,
	scriptLoad: function(script) {
		var thisObj = this;
		--wep._loadCount;
		if(typeof script == 'object') 
		{
			for(var i in script) 
			{
				if(typeof script[i] == 'string' && script[i].substr(0,1)=='<')
					alert('Error script include');

				if (typeof script[i] == 'string' && script[i])
				{
					if(wep.isUrl(script[i]))
					{
						wep.includeHelper(i);
					}
					else
					{
						//console.warn('eval - ', script[i]);
						globalEval(script[i]);
					}
				}

				var src = '';
				if (typeof i == 'string' && wep.isUrl(i))
				{
					
					if(typeof script[i] == 'object')
					{
						wep.includeHelper(i, script[i]);
					}
					else
					{
						wep.includeHelper(i);
					}
				}

			}
		}
		else {
			globalEval(script);
		}
		++wep._loadCount;
	},
	// Обертка для запуска функции include
	includeHelper: function(src, subsrc)
	{
		var thisObj = this;
		--wep._loadCount;
		wep.include(src, function(){ 
			if(subsrc) 
				thisObj.scriptLoad(subsrc); 
			++thisObj._loadCount; 
		});
	},

	isUrl: function(str)
	{
		if(
			strpos(str, '//')===0 || 
			strpos(str, 'https://')===0 || 
			strpos(str, 'http://')===0
		)
			return true;
		return false;
	},
	
	includejslist: {},

    // загружаем скрипт
    include: function(scripts, onComplete) 
    {
        if(typeof(scripts)=='string')
            scripts = [scripts];
        var i = 1;
        var ii = scripts.length; 
        var onScriptLoaded = function() { 
            if (i++ == ii && onComplete) 
            {
                onComplete.call(); i++;
            }
        }; 
        for(var s in scripts) {
            var validSrc = wep.checkJsInclude(scripts[s]);
            if(validSrc)
            {
                var scriptElement = document.createElement('script');
                //styleCss.type = 'text/javascript';
                scriptElement.src = validSrc;
                // SET READY 
                scriptElement.onload = function () {
                    onScriptLoaded.call();
                };
                scriptElement.onreadystatechange = function () {
                    if ( this.readyState != "complete" && this.readyState != "loaded" ) return;
                    onScriptLoaded.call();
                };
                //$(scriptElement).ready(onScriptLoaded);
                document.getElementsByTagName('head')[0].appendChild(scriptElement);
            }
            else
            {
                onScriptLoaded.call();
            }
        };

        return;
    },

	checkJsInclude: function(url) {
		url = wep.absPath(url);
		if(jQuery.isEmptyObject(wep.includejslist)) 
		{// проверка на уникальность подключаемого 
			wep.includejslist[url] = 1;
			var flag = 0;
			jQuery('script[src!=""]').each(function(){
				var href = wep.absPath(this.src);
				wep.includejslist[href] = 1;
				if(href==url) flag = 1;
			});

			if(flag == 1) {
                wep.includejslist[url]=2;
                return false;
            }
		} 
		else 
		{
			if(wep.includejslist[url]) {
                wep.includejslist[url]++;
                return false;
            }
            else wep.includejslist[url] = 1;
		}
		return url;
	},

	baseHref : '',
	absPath: function(url) 
	{
		var pos = strpos(url, '?t=');
		if(pos)
		{
			url = url.substr(0,pos);
		}
		if(url.substr(0,4)!='http' && url.substr(0,2)!='//') {
			if(wep.baseHref=='') {
				wep.baseHref = jQuery('base').attr('href');
				if(!wep.baseHref)
					wep.baseHref = '//'+window.location.host;
				else {
					wep.baseHref = wep.trim(wep.baseHref,'/');
				}
			}
			url = wep.baseHref+'/'+wep.trim(url,'/');
		}else {
			var i = url.indexOf('../');
			while(i>-1){
				url = url.replace(RegExp("[^\/]+\/\.\.\/","g"), '');
				i = url.indexOf('../');
			}
		}
		url = url.replace(/\?.+/, '');
		url = url.replace(/^http:/, '');
		return url;
	},

	ShowTools: function(hrf) {
		/*Панель инструментов модуля(фильтр, статистика, обновление таблицы итп)*/
		if(typeof hrf=='object')
			_last_load = jQuery(hrf).attr('href');
		var param = {'href':hrf};
		JSWin(param);
		return false;
	},

	readyPlot: function(options) {
		var settings = {
			idObj : 'statschart1',
			caption : 'Stats',
			xName : 'X',
			yName : 'Y',
			yStep : '10'
		};

		if(options) { jQuery.extend(settings, options); };

		$.jqplot.config.enablePlugins = true;

		plot1 = $.jqplot(settings.idObj, [line1], {
			title: settings.caption,
			// Turns on animatino for all series in this plot.
			animate: true,
			// Will animate plot on calls to plot1.replot({resetAxes:true})
			animateReplot: true,
			axes: {
				xaxis:{label:settings.xName, renderer:$.jqplot.DateAxisRenderer},
				yaxis:{label:settings.yName, min:0, tickOptions:{formatString:'%d'}, autoscale:false, useSeriesColor:true }
			},
			highlighter: {
				show: true,
				sizeAdjust: 7
			},
			cursor:{show: true, zoom: true},
			series:[{lineWidth:4, markerOptions:{style:'square'}}]
		});

		plot2 = $.jqplot('statschart2', [line1], {
			title: settings.caption,
			animate: true,
			animateReplot: true,
			seriesDefaults:{neighborThreshold:0, showMarker: false},
			axes: {
				xaxis:{label:settings.xName, renderer:$.jqplot.DateAxisRenderer},
				yaxis:{label:settings.yName, min:0, tickOptions:{formatString:'%d'} , useSeriesColor:true}
			},
			cursor:{showTooltip: false, zoom: true, constrainZoomTo: 'x'},
			series:[{lineWidth:2}]
		});

      
		$.jqplot.Cursor.zoomProxy(plot1, plot2);
	},

	pagenum_super: function(total,pageCur,cl,order) {
		if(!order) order = false;
		if(total>20) {
			wep.includeCSS('/_design/_style/style.jquery/paginator.css');
			pg = 1;
			reg = new RegExp('\&*'+cl+'_pn=([0-9]*)', 'i');
			tmp = reg.exec(lochref);

			if(tmp)// Если уже выбрана страница, то берем номер текущей страницы
				pg =  tmp[2];
			else if(!tmp && order)// если не выбрана страница, но включена обратная постраницка то текущий номер - последний номер
				pg = total;
			
			loc = lochref.replace(reg, '');
			var param = '';
			if(/\?/.exec(loc)) {
				if(loc.substr(-1,1)!='&' && loc.substr(-1,1)!='?')
					param += '&';
			}
			else
				param += '?';
			param += cl+'_pn='; // строка которую вставляем в состав адреса для перехода по страницам

			wep.include('/_design/_script/script.jquery/paginator.js',function() {
				var s_left = jQuery('.pagenum').position();
				var s_width = jQuery('.pagenum').parent().width();
				jQuery('div.pagenum').after('<div class="ppagenum">Загрузка...</div>');
				jQuery('div.pagenum').hide();
				s_left = (s_width-s_left.left-80);
				var s_pages = s_left/49;
				jQuery('div.ppagenum').each(function(i){
					$(this).width(s_left+'px').paginator({
						pagesTotal:total, 
						pagesSpan:parseInt(s_pages), 
						returnOrder:order,
						pageCurrent:pageCur, 
						baseUrl: function (page) {
							// Срабатывает при нажатии на номер страницы
							if((!order && page!=1) || (order && page!=total))
								loc += param+page;
							window.location.href = loc;
						}
					});
				});
				wep.setResize('paginator', function() {
					jQuery('div.ppagenum').remove();
					jQuery('div.pagenum').show();
					wep.pagenum_super(total,pageCur,cl,order);
				});
			});
			//returnOrder: true
		}
	},

	pagenum: function(total,order) {
		if(!order) order = false;
		if(total>10) {
			wep.includeCSS('/_design/_style/style.jquery/paginator.css');
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
			wep.include('/_design/_script/script.jquery/paginator.js',function(){
				var s_left = jQuery('.pagenum').position();
				var s_width = jQuery('.pagenum').parent().width();
				jQuery('div.pagenum').after('<div class="ppagenum">Загрузка...</div>');
				jQuery('div.pagenum').hide();
				s_left = (s_width-s_left.left-70);
				var s_pages = s_left/40;
				jQuery('div.ppagenum').each(function(){
					$(this).width(s_left+'px').paginator({
						pagesTotal:total, 
						pagesSpan:parseInt(s_pages),
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
				wep.setResize('paginator', function() {
					jQuery('div.ppagenum').remove();
					jQuery('div.pagenum').show();
					wep.pagenum(total,order);
				});

			});
		}
	},

	ZeroClipboard: function(obj,txt) {
		wep.include('/_design/_script/zeroclipboard/ZeroClipboard.js',function() {
			clip = new ZeroClipboard.Client();
			clip.setHandCursor( true );
			
			clip.addEventListener("load", function (client) {
				debugstr("Flash movie loaded and ready.");
			});
			
			clip.addEventListener("mouseup", function (client) {
				clip.setText(txt);
			});
			
			clip.addEventListener("complete", function (client, text) {
				alert("код для вставки в блог скопирован в буфер обмена");
			});
			
			clip.glue(obj);
		});
	},

	iSortable : function() {// сортировка
		// wep.include('/_design/_script/script.jquery/jquery-ui.js',function() {
			$('table.superlist>tbody').sortable({
				items: '>tr.tritem',
				axis:	'y',
				helper: 'original',
				opacity:'false',
				//revert: true,// плавное втыкание
				//placeholder:'sortHelper',
				handle: '>td a.imgdragdrop',
				tolerance: 'pointer',
				/*start: function(event, ui) {
					//console.log(ui.helper);
				},*/
				//sort: function(event, ui) { ... },
				//change: function(event, ui) {console.log('*change');console.log(ui);},
				update: function(event, ui) {
					var Obj = $(ui.item);
					var id= Obj.attr('data-id');
					var modul = Obj.attr('data-mod');
					var pid = Obj.attr('data-pid');
					var t1 = Obj.prev('tr.tritem').attr('data-id');
					var t2 = Obj.next('tr.tritem').attr('data-id');
					var param = {
						'data' : {'_modul':modul, '_type' : 'tools', '_func':'_sorting','id':id,'t1':t1,'t2':t2,'pid':pid}
					};
					JSWin(param);
				}
			});
		// });
	},

	setEventFilterMultiselect: function(selector) {
		// enableFiltering : true
		// includeSelectAllOption: true
		// selectAllText: true
		//  selectAllValue: 'multiselect-all',
		// filterPlaceholder: 'Search'
		$(selector).multiselect({
			buttonClass: 'btn',
			buttonWidth: '',
			buttonContainer: '<div class="btn-group" />',
			maxHeight: 300,
			buttonText: function(options, select) {
				//console.log(options, select, this);
				if (options.length == 0) {
					return 'Все <b class="caret"></b>';
				}
				else if (options.length > 2) {
					return options.length + ' выбрано  <b class="caret"></b>';
				}
				else {
					var selected = '';
					options.each(function() {
						selected += $(this).text() + ', ';
					});
					return selected.substr(0, selected.length -2) + ' <b class="caret"></b>';
				}
			},
			onChange: function(element, checked) {
				var selectObj = $(element).parent();
				if($(element).val()==='') {
					if(checked) {
						$('option', selectObj).each(function(element) {
							if($(this).val()!=='')
								selectObj.multiselect('deselect', $(this).val());
						});
					}
				}
				else {
					selectObj.multiselect('deselect', '');
				}
			}
		});
	},


	SuperGroup: function(obj) {
		$('#tools_block').hide("slow");
		var sg = parseInt($('span.button-SuperGroup i:first').text());
		if(obj.checked) {
			setCookie(obj.name,1,1);
			sg++;
			$('span.button-SuperGroup i').text(sg);
			if(sg>0)
				$('span.button-SuperGroup').parent().show("slow");
		}
		else {
			setCookie(obj.name,0,-1000);
			sg--;
			$('span.button-SuperGroup i').text(sg);
			if(sg<1)
				$('span.button-SuperGroup').parent().hide("slow");
		}
	},
	SuperGroupClear: function(type) {
		if(type=='on') {
			$('table.superlist input:checked').each(function(i) {
				$(this).prevAll('a.img0').removeClass('img0').addClass('img1');
				$(this).removeAttr('checked');
			});
		}
		else if(type=='off') {
			$('table.superlist input:checked').each(function(i) {
				$(this).prevAll('a.img1').removeClass('img1').addClass('img0');
				$(this).removeAttr('checked');
			});
		}
		else if(type=='del') {
			$('table.superlist input:checked').each(function(i) {
				$(this).parent().parent().remove();
			});
		}
		else {
			$('table.superlist input:checked').removeAttr('checked');
		}
	},
	SuperGroupInvert: function(obj) {
		$('table.superlist td input[type=checkbox]').each(function(i) {
			if($(this).attr('checked'))
				$(this).removeAttr('checked');
			else
				$(this).attr('checked','checked');
			wep.SuperGroup(this);
		});
		
	},

	exit: function(){
		if(confirm('Вы действительно хотите выйти?'))
			JSWin({'href':wep.siteJS+'?_view=exit'});
		return false;
	},

	load_href: function(hrf) {
		/*var base_href = $('base').attr('href');
		if(typeof hrf=='object')
			hrf = $(hrf).attr('href');
		if (hrf.substr(0, 7) != 'http://')
			hrf = base_href+hrf;*/
		if(!hrf)
		{
			window.location.reload();
			return false;
		}

		window.location.href = hrf;
		return false;
	},

	hrefConfirm: function(obj,mess)
	{
		if(MESS[mess])
			mess = MESS[mess];

		if(confirm(mess)) {
			return true;
		}
		return false;
	},

	preSubmitAJAX : function() {
		if(typeof(CKEDITOR)!=='undefined') 
		{
			for ( instance in CKEDITOR.instances )
	    		CKEDITOR.instances[instance].updateElement();
		}
		return true;
	},

	/*FILTER*/
	/*Слайдер для фильтра, возможность установки чесловых пределов*/
	gSlide : function(id,_min,_max,val0, val1,stp) {
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
					jQuery('#'+id+' input:eq(0)').val(ui.values[0]);
					jQuery('#'+id+' input:eq(1)').val(ui.values[1]);
				},
				stop: function( event, ui ) {
					jQuery('#'+id+' input:eq(0)').change();
				}
			});
			jQuery('#'+id+' input:eq(0)').bind('change',function (){jQuery('#slide'+id).slider( 'values' , 0 , this.value)});
			jQuery('#'+id+' input:eq(1)').bind('change',function (){jQuery('#slide'+id).slider( 'values' , 1 , this.value)});
	},

	clearHref : function() {
		window.location.href=window.location.pathname;
		return false;
	},

	// Массив функции выполняющиеся при изменении размера окна 

	setResize : function (k, funct){
		if(!wep.flagResizeStart) wep.setResizeTimer();
		wep.winResize[k] = funct;
	},

	winResize : {},
	flagResizeStart : false,
	resizeTimer : null,

	setResizeTimer : function () {
		wep.flagResizeStart = true;
		$(window).resize(function() {
		    if (wep.resizeTimer) clearTimeout(wep.resizeTimer);
		    wep.resizeTimer = setTimeout(wep.wResize, 300);
		});
	},

	wResize : function () {
		for(var item in wep.winResize) {
			if(typeof wep.winResize[item] == 'function') {
				wep.winResize[item]();
			}
		}
	},

	trim: function( str, charlist ) {	// Strip whitespace (or other characters) from the beginning and end of a string
		charlist = !charlist ? ' \s\xA0' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
		var re = new RegExp('^[' + charlist + ']+|[' + charlist + ']+$', 'g');
		return str.replace(re, '');
	},

	/*таймер обратного отчета*/
	timer: function(selector)
	{
		var timerobj = jQuery(selector);
		setInterval(function() {
			var timeEnd = timerobj.attr('data-time');
			var strTime = '';
			var D = new Date();
			var temeLeft = timeEnd - Math.floor(D.getTime()/1000);
			var temp;

			if(temeLeft<=0)
			{
				window.location.reload();return false;
			}

			if(temeLeft>3600)
			{
				temp = Math.floor(temeLeft/3600);
				temeLeft = (temeLeft-temp*3600);
				strTime += temp+' час. ';
			}
			temp = Math.floor(temeLeft/60);
			temeLeft = (temeLeft-temp*60);
			strTime += temp+' мин. ';
			strTime += temeLeft+' сек.';

			timerobj.html(strTime);
			return;
		},1000);
	},

	timerFunction: function(func, selectorMess, addSelector)
	{
		var timerID =0;
		var maxTime = parseInt(jQuery(selectorMess+' i').text()); 
		// Отменить
		if(addSelector)
			addSelector = ','+addSelector;
		else
			addSelector = '';
		jQuery(selectorMess+addSelector).click(function(){
			jQuery(selectorMess).hide();
			clearInterval(timerID);
		});
		timerID = setInterval(function() {
			if(maxTime<1) 
				return false
			else if(maxTime==1) 
			{
				func.call();
				jQuery(selectorMess).hide();
				clearInterval(timerID);
				return false
			}
			jQuery(selectorMess+' i').text(--maxTime);
		},1000);
	},
    
	/**
	*
	*/
    convertRelativePathToAbsolute: function(url) 
    {
    	if(!url)
    	{
    		alert('Ошибка скрипта. Функция convertRelativePathToAbsolute не получила нужные данные');
    		return false;
    	}
        if(url.substr(0,4)!='http' && url.substr(0,2)!='//') {
            url = this.getBaseHref()+'/'+trim(url,'/');
        }else {
            var i = url.indexOf('../');
            while(i>-1){
                url = url.replace(RegExp("[^\/]+\/\.\.\/","g"), '');
                i = url.indexOf('../');
            }
        }
        //url = url.replace(/\?.+/, '');
        url = url.replace(/^http:/, '');
        return url;
    },

    /**
    *
    */
    convertAbsolutePathToRelative: function(link)
    {
        if(!link)
            link = document.location.href;

        link = link.replace(/^http:/, '');

        link = decodeURIComponent(link);

        // var bh = this.getBaseHref();
        // var i = link.lastIndexOf(bh);

        // if(i!==-1)
        // {
        //     i += bh.length;
        //     link = link.substr(i);
        // }

        return link;
    },

    /**
    *
    */
    getBaseHref: function()
    {
        return '//'+document.location.host;
    },

    /**
    *
    */
    getCurrentPath: function()
    {
        var link = this.convertAbsolutePathToRelative();
        var i = link.indexOf('?');
        if(i>0)
            link = link.substr(0, i);
        return link;
    },

    /**
    * Получить урл с нужными параметрами
    * param - обект {param1:123, param2: 'abcd'}
    * url - не обязательно, берется текущий адрес
    * replace - true удалит все предыдущие параметры
    */
	getUrlWithNewParam: function(param, url, replace)
	{
	    if(!url)
	        url = this.convertAbsolutePathToRelative();

	    var parsedUrl = urlDecode(url);

	    if(replace)
	        parsedUrl[0].args = param;
	    else
	        $.extend(parsedUrl[0].args, param);

	    return urlEncode(parsedUrl);
	}
};


wep.apply = function(o, c, defaults){
    // no "this" reference for friendly out of scope calls
    if(defaults){
        wep.apply(o, defaults);
    }
    if(o && c && typeof c == 'object'){
        for(var p in c){
            o[p] = c[p];
        }
    }
    return o;
};

//////////////////////////////////////////

var timerid = 0;
var timerid2 = 0;

function JSWin(param) {
	return wep.JSWin(param);
}

function OnJSWin(obj,param) {
	if(!param) param = {};
	param['type'] = jQuery(obj);
	jQuery(obj).submit(function() {JSWin(param);return false;});
	return false;
}


function fLog(txt,flag) {
	return wep.fLog(txt,flag);
}

function fMessPos(obj) {
	return wep.fMessPos(obj);
}

/*Показ тултип*/
function showHelp(obj,mess,time,nomiga) {
	return wep.showHelp(obj,mess,time,nomiga);
}

function miga(obj,opc1,opc2){
	return wep.miga(obj,opc1,opc2);
}

/*SPOILER*/

function fSpoiler(txt,nm) {
	return wep.fSpoiler(txt,nm);
}

function initSpoilers(context){
	return wep.initSpoilers(context);
}

/*END SPOILER*/

function fShowHide(id,f) {
	return wep.fShowHide(id,f);
}

function ulToggle(obj,css) {
	jQuery(obj).toggleClass(css);
	jQuery(obj).parent().find('>ul').slideToggle('fast');
	return true;
}
/************************/
/*simple script*/

function setCookie(name, value, expiredays, domain, path, secure) {
	return wep.setCookie(name, value, expiredays, domain, path, secure);
}

function getCookie(name) {
   return wep.getCookie(name);
}


/************************/
/****************/

function readyPlot(cap,Xname,Yname,stepY) {
	return wep.readyPlot(cap,Xname,Yname,stepY);
}


var lochref = window.location.href;
var i = lochref.indexOf('#', 0);
if(i >= 0) {
	lochref = lochref.substring(0, i);
}
var tmp;

function pagenum_super(total,pageCur,cl,order) {
	wep.pagenum_super(total,pageCur,cl,order);
}

function pagenum(total,order) {
	wep.pagenum(total,order);
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

function strpos( haystack, needle, offset){	// Find position of first occurrence of a string
	var i = haystack.indexOf( needle, offset ); // returns -1
	return i >= 0 ? i : false;
}

function substr( f_string, f_start, f_length ) {	// Return part of a string
	if(f_start < 0) {
		f_start += f_string.length;
	}

	if(f_length == undefined) {
		f_length = f_string.length;
	} else if(f_length < 0){
		f_length += f_string.length;
	} else {
		f_length += f_start;
	}

	if(f_length < f_start) {
		f_length = f_start;
	}

	return f_string.substring(f_start, f_length);
}

function getKeyChar(e) 
{
    return String.fromCharCode(keys_return(e));
}
/*
39 37 стрелки
46 делете
8 удал
13 интер
109 минус
*/
function keys_return(e) 
{
	var keys=0;
	if (!e) var e = window.event;
	if (e.keyCode) keys = e.keyCode;
	else if (e.which) keys = e.which;
	//
	if(keys==8 || keys==46 || keys==13 || keys==39 || keys==37) keys=0;
	return keys;
}

function trim( str, charlist ) {
    charlist = !charlist ? ' \\s\xA0' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
    var re = new RegExp('^[' + charlist + ']+|[' + charlist + ']+$', 'g');
    return str.replace(re, '');
}

function ltrim( str, charlist ) {
    charlist = !charlist ? ' \\s\xA0' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
    var re = new RegExp('^[' + charlist + ']', 'g');
    return str.replace(re, '');
}

function rtrim( str, charlist ) {
    charlist = !charlist ? ' \\s\xA0' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
    var re = new RegExp('[' + charlist + ']+$', 'g');
    return str.replace(re, '');
}


function toInt(val, unsigned)
{
	var valNegative = '-';
    if(typeof(val)=='string')
    {
    	var sgn = '';
        if(unsigned)
            val = val.replace(/[^0-9]+/g, '');
        else
        {
			if(val.substring(0,1)===valNegative)
				sgn = '-';
            val = val.replace(/[^0-9]+/g, '');
        }
        val = ltrim( val , '0' );
        val = sgn+val;
    }

    val = parseInt(val);
    if(isNaN(val))
        val = 0;

    return val;
}

function getBodyScrollTop()
{
    return jQuery(($.browser.opera || $.browser.msie || $.browser.mozilla)?'html':'body').scrollTop();
}

function setBodyScrollTop(pos)
{
    if(!pos) pos = 0;
    return jQuery(($.browser.opera || $.browser.msie || $.browser.mozilla)?'html':'body').scrollTop(pos);
}

function show_fblock(obj,selector) {
	if(jQuery(selector).is(':hidden')) {
		jQuery(selector).show();
		jQuery(obj).addClass('shhide');
	}
	else {
		jQuery(selector).hide();
		jQuery(obj).removeClass('shhide');
	}

}

/* вспомогательные функции для DatePicker*/
var disabledDays = [];
function nationalDays(date) {
	var m = date.getMonth(), d = date.getDate(), y = date.getFullYear();
	//console.log('Checking (raw): ' + m + '-' + d + '-' + y);
	for (i = 0; i < disabledDays.length; i++) {
		if($.inArray((m+1) + '-' + d + '-' + y,disabledDays) != -1 || new Date() > date) {
			//console.log('bad:  ' + (m+1) + '-' + d + '-' + y + ' / ' + disabledDays[i]);
			return [false];
		}
	}
	//console.log('good:  ' + (m+1) + '-' + d + '-' + y);
	return [true];
}
function noWeekendsOrHolidays(date) {
	var noWeekend = jQuery.datepicker.noWeekends(date);
	return noWeekend[0] ? nationalDays(date) : noWeekend;
}


function urlDecode( str )
{
    str = decodeURIComponent(str);
    var arr = str.split('#');
 
    var result = new Array();
    var ctr=0;
    for( var part in arr )
    {
        part = arr[part];
        var qindex = part.indexOf('?');
        result[ctr] = {};
        if( qindex==-1 )
        {
            result[ctr].mid=part;
            result[ctr].args = [];
            ctr++;
            continue;
        }
        result[ctr].mid = part.substring(0,qindex);
        var args = part.substring(qindex+1);
        args = args.split('&');
        
        result[ctr].args = {};
        for( var val in args )
        {
            val = args[val];
            var keyval = val.split('=');
            var localctr = keyval[0];
            var i = localctr.indexOf('[]');
            if(i>0)
            {
                localctr = localctr.substr(0, i);
                if(!result[ctr].args[localctr])
                    result[ctr].args[localctr] = [];
                result[ctr].args[localctr].push(keyval[1]);
            }
            else
            {
                result[ctr].args[localctr] = keyval[1];
            }
        }
        ctr++;
    }
    return result;
}

function urlEncode( objUrl )
{
    var result = '';

    for(var i in objUrl)
    {
        var param = [];
        for(var k in objUrl[i].args)
        {
            var temp;
            if(typeof(objUrl[i].args[k])=='object')
            {
                for(var l in objUrl[i].args[k])
                {
                    temp = k+'[]='+objUrl[i].args[k][l];
                    param.push(temp);
                }

            }
            else
            {
                temp = k+'='+objUrl[i].args[k];
                if(k)
                    param.push(temp);
            }
            
        }
        param = param.join('&');

        objUrl[i] = objUrl[i].mid;
        if(param)
           objUrl[i] += '?'+param;
    }
    result = objUrl.join('#');
    return result;
}

// Функция запуска eval в глобальном контексте
var globalEval = function globalEval(src) {
	if (src == undefined || src == '') {
		return;
	}
    if (window.execScript) {
        window.execScript(src);
        return;
    }
    var fn = function() {
        window.eval.call(window,src);
    };
    fn();
};

/**
 *  Запуск и обрыв действия
 * @param input
 * @param func
 */
function timeAction(selector, func, tm)
{
    var timer = $(selector).data('timer');
    if(timer) {
        clearTimeout(timer);
    }
    if(func) {
        if(!tm) tm = 200;
        $(selector).data('timer', setTimeout(func, tm) );
    }
}

window._Browser = wep.getBrowserInfo();


/**********************************/
/**********************************/
/**********************************/

/*AXAX LIST*/
var timerid4=0;
var timerid5=0;
var timerbagIE=0;// таймер для слоя списка
var ajaxlistover = 0; // флаг активности слоя списка
function setEventAjaxList(input, hidden, list)
{
    var ajaxlist = $(input).parent();
    $(ajaxlist).on('focusin', function() {
        console.log('focus+++++++++');
        ajaxListControl(input, hidden, list);
    });
    $(ajaxlist).on('focusout', function(){
        console.log('focus---------');
        if(!$(list).data('mousedown')) {
            ajaxListHide(input, hidden, list);
        }
    });

    $(list).on('mousedown', function() {
        console.log('focus!!!!!+++++++++');
        $(this).data('mousedown', true);
    });
    $(list).on('mouseup', function(){
        console.log('focus!!!!!---------');
        $(this).data('mousedown', false);
        $(input).focus();
    });

    $(input).on('keydown', function(){
        ajaxlistOnKey(event, input, hidden, list)
    });
    $(list).on('click', 'label', function() {
        ajaxListHide(input, hidden, list, $(this));
    });
    $(list).on('mouseover', 'label', function () {
        $(this).siblings().removeClass('selected');
        $(this).addClass('selected');
    });
}

function ajaxListStop(input)
{
    $(input).data('value', '');
}


/**
 * Скрываем список и выбираем значение выделенное
 * @param input
 * @param hidden
 * @param list
 */

function ajaxListHide(input, hidden, list, SEL)
{
    console.log('ajaxListHide');
    ajaxListStop(input);
    jQuery(list).hide();
    // Fix для IE
    if (_Browser.type == 'IE' && 8 > _Browser.version)
        jQuery('select').toggleClass('hideselectforie7',false);

    if(!SEL) {
        SEL = $(list).find('.selected');
    }
    if(SEL.size())
        ajaxListSelect(SEL, input, hidden, list);
    else {
        ajaxListClear(input, hidden, list);
    }
}

/**
 * функ очистки формы если не верное значение выбранно
 * @param input
 * @param hidden
 * @param list
 */
function ajaxListClear(input, hidden, list) {
    if(jQuery(hidden).val()=='') {
        jQuery(input).val('');
    }
}

function ajaxlistOnKey(e, input, hidden, list)
{
    console.log('ajaxlistOnKey');
	var keyCode = keys_return(e);
	if (keyCode == '40' || keyCode == '38') { // вниз
		var W = 290;
		var listObj = $(list);
		var SEL = listObj.find('.selected');
		if(!SEL.size()) {
			SEL = listObj.find('label:first').addClass('selected');
		}
		if(keyCode == '40')
			var NEXT = SEL.next();
		else
			var NEXT = SEL.prev();
		if(NEXT.size()) {
			SEL.removeClass('selected');
			NEXT.addClass('selected');

			var stop = listObj.scrollTop();
			var h = NEXT.outerHeight();
			if(keyCode == '40') {
				if(NEXT.position().top>W)
                    listObj.scrollTop(stop+h);
			}
			else {
				if(NEXT.position().top<10)
                    listObj.scrollTop(stop-h);
			}
		}
		return false;
	}
	else if (keyCode == '13') {
        var listObj = $(list);
		var SEL = listObj.find('.selected');
		if(!SEL.size())
			SEL = listObj.find('label:first');
        ajaxListHide(input, hidden, list, SEL);
		return false;
	} else {
        ajaxListStop(input);
        timeAction(input, function(){ajaxListControl(input, hidden, list);}, 100);
	}
	return true;
}


/**
 * функция контроля подгрузки слоя списка
 * @param input
 * @param hidden
 * @param list
 */
function ajaxListControl(input, hidden, list)
{
    console.log('ajaxListControl');
    var value = $(input)[0].value;
    if(value.length>2) {
        var listObj = $(list);
        if(listObj.attr('val')==value) {
            listObj.show();
            listObj.find('label:first').addClass('selected');
        }
        else {
            $(input).data('value', value);
            var parentObj = listObj.parent();
            parentObj.addClass('load');
            var key = $(hidden).attr('name');
            var dt = {'_view':'ajaxlist', '_srlz':jQuery('#srlz_'+key).val(), '_value':value, '_hsh':jQuery('#hsh_'+key).val()};
            $.ajax({
                type: "GET",
                url: '/_js.php',
                data: dt,
                dataType: "json",
                cache:true,
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert('error: '+textStatus);
                },
                success: function(result, textStatus, XMLHttpRequest) {
                    if($(input).data('value')!=value) {
                        return;
                    }
                    console.log('ajaxListControl - !!!!!!!!!!!!!!');

                    if (_Browser.type == 'IE' && 8 > _Browser.version) {
                        jQuery('select').toggleClass('hideselectforie7',true);
                        parentObj.css('z-index','10');
                    }
                    var txt = '';
                    if(result && result.data && jQuery(result.data).size()>0) {
                        var c = 0;var temp = 0;
                        for(k in result.data) {
                            txt += '<label data-id="'+result.data[k][0]+'"';
                            if(k==0)
                                txt += ' class="selected"';
                            txt += '>'+result.data[k][1]+'</label>';
                            if(result.data[k][1]==value){
                                temp = result.data[k][0];
                                c++;
                            }
                        }
                        if(c==1){
                            jQuery(hidden).val(temp);
                            jQuery(list).find('[data-id='+temp+']').addClass('selected');
                            parentObj.removeClass('reject');
                        }
                    }else
                        txt = 'не найдено';

                    jQuery(list).html(txt).show();
                    jQuery(list).attr('val',value);
                    parentObj.removeClass('load');
                }
            });
        }
    } else {
        jQuery(hidden).val('');
        jQuery(input).parent().addClass('reject');
        jQuery(list).hide();
        jQuery(list).find('.selected').removeClass('selected');
    }
}

/**
 * Клик по элементу списка
 * @param OBJ - DOM объекс элемента Label по котороу кликнули
 * @param input - Элемент формы в котором отображается текст выбранного значения
 * @param hidden - скрытый элемент формы в котором записывается ID выбранного значения
 * @param list - выпадающий список
 */
function ajaxListSelect(OBJ, input, hidden, list) { // событие на клик на элементе списка
    console.log('ajaxListSelect');
    OBJ = $(OBJ);
	var ID = OBJ.attr('data-id');
	jQuery(hidden).val(ID).change(); // Сохраняем ID
	$(input).val(OBJ.text()); // Выводим текст
    $(input).parent().removeClass('reject'); // Удаляем стиль "не верного значения"
    $(list).find('[data-id]').removeClass('selected'); // удаляем выбор у всех
	$(list).find('[data-id='+ID+']').addClass('selected'); // ставим выбор у кликнутого
	if(ajaxlistover) { // решаем проблему с переключением фокуса
		jQuery(list).hide(); // скрываем список
		if (_Browser.type == 'IE' && 8 > _Browser.version)
			jQuery('select').toggleClass('hideselectforie7',false);
	}
}

