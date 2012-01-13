
var timerid = 0;
var timerid2 = 0;
var ajaxComplite = 1;

wep.form = {
	// Аякс отправка формы
	JSFR: function(n) {
		// NEED INCLUDE jquery.form
		jQuery(n).ajaxForm({
			beforeSubmit: 
				function(a,f,o) {
					//var formElement = f[0];
					o.dataType = 'json';
					//preSubmitAJAX (f);
				},
			error: 
				function(d,statusText) {
					alert(statusText+' - form notsuccess (may be wrong json data, see console log)');
					console.log(d.responseText);
				},
			success: 
				function(result) {
					//console.log(result);
					clearTimeout(timerid);
					timerid2 = setTimeout(function(){fShowload(0);},200);
					if(result.html!= undefined && result.html!='') jQuery('#ajaxload').html(result.html);
					if(result.eval!= undefined && result.eval!='') eval(result.eval);
					if(result.text!= undefined && result.text!='') fLog(fSpoiler(result.text,'AJAX text result'),1);

				}
		});
	},

	// Мультисписок
	ilist : function(obj,k) {
		$(obj).next().attr('name',k+'['+$(obj).val()+']');
	},
	ilistCopy : function(ths,obj,max) {
		var sz = $(obj).size();
		if(sz<max) {
			var clon = $(obj+':last').clone();
			var in1 = clon.find('.ilist-key');
			var defval = '';
			if(in1.attr('type')=='int')
				defval = (parseInt(in1.val())+1);
			in1.val(defval);
			clon.find('.ilist-val').val('');
			clon.find('.ilistdel').show();
			$(ths).before(clon);
			in1.keyup();
			var cnt = parseInt($(ths).text())-1;
			$(ths).text(cnt);
			if(sz==(max-1)) {
				$(ths).fadeTo('slow', 0.3);
			} else {
			}
		}
	},
	ilistdel : function(ths) {
		var tmp = $(ths).parent();
		var tmp2 = tmp.parent().find('span.ilistmultiple');
		var cnt = parseInt(tmp2.text())+1;
		tmp2.text(cnt);
		if(cnt==1) tmp2.fadeTo('slow', 1);
		tmp.remove();
	},

	preSubmitAJAX : function(obj) {
		if(typeof CKEDITOR !== 'undefined') {
			jQuery.each(jQuery(obj).find("textarea"),function(){nm=jQuery(this).attr('name');if(nm) eval("if(typeof CKEDITOR.instances."+nm+" == 'object') {CKEDITOR.instances."+nm+".updateElement();}");});
		}
		return true;
	},

	/*
	39 37 стрелки
	46 делете
	8 удал
	13 интер
	109 минус
	*/
	keys_return : function(ev) {
		var keys=0;
		if (navigator.appName == 'Netscape')
			{keys=ev.which;}
		else if(navigator.appName == 'Microsoft Internet Explorer')
			{keys=window.event.keyCode;}
		if(keys==8 || keys==46 || keys==13 || keys==39 || keys==37) keys=0;
		return keys;
	},
	checkInt : function(ev) {
		var keys = keys_return(ev);
		if (keys!=0 && (keys<0x30 || keys>0x39) && (keys<96 || keys>105) && keys!=189 && keys!=109) 
			return false;
		return true;
	},

	/* Утилита для подсчёта кол сиволов в форме, автоматически создаёт необходимые поля*/
	textareaChange : function(obj,max) {
		if(!jQuery('#'+obj.name+'t2').size()){
			val = document.createElement('span');
			val.className = "dscr";
			val.innerHTML = 'Cимволов:<input type="text" id="'+obj.name+'t2" maxlength="4" readonly="false" class="textcount" style="text-align:right;"/>/<input type="text" id="'+obj.name+'t1" maxlength="4" readonly="false" class="textcount" value="'+max+'"/>';
			jQuery(obj).after(val);
		}
		if(obj.value.length>max)
			obj.value=obj.value.substr(0,max);
		jQuery('#'+obj.name+'t2').val(obj.value.length);
	}
}

/*********** FORMa ********/

function JSFR(n) {
	wep.form.JSFR(n);
}


function preSubmitAJAX (obj) {
	return wep.form.preSubmitAJAX(obj);
}

function keys_return(ev) {
	return wep.form.keys_return(ev);
}

function checkInt(ev) {
	return wep.form.checkInt(ev);
}

function textareaChange(obj,max) {
	wep.form.textareaChange(obj,max);
}

function reloadCaptcha(id)
{
	jQuery('#'+id).attr('src',"/_captcha.php?"+ Math.random());
}

/*PASS FORM*/
function checkPass(name) {
	jQuery("form input[type=submit]").attr({'disabled':'disabled'});
	val_1=jQuery("form input[name="+name+"]").val();
	val_2=jQuery("form input[name=re_"+name+"]").val();
	if(val_1.length>=6) {
		jQuery("form input[name="+name+"]").attr({'class':'accept'});
		if(val_1!=val_2)
			jQuery("form input[name=re_"+name+"]").attr({'class':'reject'});
		else{
			jQuery("form input[name=re_"+name+"]").attr({'class':'accept'});
			jQuery("form input[type=submit]").removeAttr('disabled');
		}
	}else {
		jQuery("form input[name="+name+"]").attr({'class':'reject'});
		jQuery("form input[name=re_"+name+"]").attr({'class':'reject'});
	}
	return true;
}

function password_new(obj) {
	var type1 = 'password';
	var type2 = 'text';
	var inp = jQuery(obj).parent().find('input.password');
	if(inp.attr('type')!='password' ) {
		type1 = 'text';
		type2 = 'password';
	}
	inp.after("<input name=\""+inp.attr('name')+"\" type=\""+type2+"\" value=\""+inp.attr('value')+"\" class=\"password\"/>");
	inp.remove();
}

/*AXAX LIST*/
var timerid4=0;
var timerid5=0;
var timerbagIE=0;// таймер для слоя списка
var ajaxlistover = 0; // флаг активности слоя списка

function chFocusList(f) {// это баг решает проблему когда мышкой передвигаешь скрул, то срабатывает onblur формы списка (в Хроме - тык на скрол не вызывает события фокуса для этого элемента,в FF только нормально)
	if(f)
		timerbagIE = setTimeout(function(){ajaxlistover=0;},500);
	else {
		clearTimeout(timerbagIE);
		ajaxlistover = 1;
	}
}

function show_hide_label(obj,view,flag,key) { // функция на событие активности формы
	clearTimeout(timerid5);
	//jQuery('#tr_city .form-caption').append(flag+'-');
	if(ajaxComplite==0 || timerid4) {
		setTimeout(function(){show_hide_label(obj,view,flag,key);},400);
	}else {
		setTimeout(function(){
			if(ajaxlistover) {
				setTimeout(function(){show_hide_label(obj,view,flag,key);},400);
			}
			else if(flag) {
				jQuery(obj).prev().hide();
				ajaxlist(obj,view,key);
			}
			else {
				var al = '#ajaxlist_'+view;
				if(typeof key !== 'undefined')
					al += '_'+key+'_';
				jQuery(al).hide();
				if (_Browser.type == 'IE' && 8 > _Browser.version)
					jQuery('select').toggleClass('hideselectforie7',false);

				var SEL = $(obj).next('div').find('.selected');
				if(SEL.size())
					ajaxlist_click(SEL,view,key);
				else {
					timerid5 = setTimeout(function(){ajaxlistClear(obj,view,key);},200);
					if(!obj.value){
						jQuery(obj).prev().show();
					}
				}
			}
		},200);
	}

}
function ajaxlistClear(obj,view,key) { // функ очистки формы если не верное значение выбранно
	var al = '#ajaxlist_'+view;
	if(typeof key !== 'undefined')
		al += '_'+key+'_';

	if(jQuery(al+' + input').val()=='') {
		jQuery(obj).val('');
		jQuery(obj).prev().show();
		clearTimeout(timerid4);timerid4=0;
	}
}

function ajaxlistOnKey(event,obj,view,key) {
	if (event.keyCode == '40' || event.keyCode == '38') { // вниз
		var W = 290;
		var PARENT = $(obj).next('div');
		var SEL = PARENT.find('.selected');
		if(!SEL.size()) {
			SEL = PARENT.find('label:first').addClass('selected');
		}
		if(event.keyCode == '40')
			var NEXT = SEL.next();
		else
			var NEXT = SEL.prev();
		if(NEXT.size()) {
			SEL.removeClass('selected');
			NEXT.addClass('selected');

			var stop = PARENT.scrollTop();
			var h = NEXT.outerHeight();
			if(event.keyCode == '40') {
				if(NEXT.position().top>W)
					PARENT.scrollTop(stop+h);
			}
			else {
				if(NEXT.position().top<10)
					PARENT.scrollTop(stop-h);
			}
		}
		return false;
	}
	else if (event.keyCode == '13') {
		var SEL = $(obj).next('div').find('.selected');
		if(!SEL.size())
			SEL = $(obj).next('div').find('label:first');
		ajaxlist_click(SEL,view,key);
		return false;
		// выбор
	} else {
		clearTimeout(timerid4);
		timerid4 = setTimeout(function(){ajaxlist(obj,view,key);},100);
	}
	return true;
}

function ajaxlist(obj,view,key) { // функция контроля подгрузки слоя списка
console.log(obj.value+' = '+obj.value.length);
	if(obj.value.length>2) {
		clearTimeout(timerid4);
		if(ajaxComplite==1)
			timerid4 = setTimeout(function(){getAjaxListData(obj.value,view,key);},400);
		else
			timerid4 = setTimeout(function(){ajaxlist(obj,view,key);},600);
	} else {
		clearTimeout(timerid4);timerid4=0;
		var al = '#ajaxlist_'+view;
		if(typeof key !== 'undefined')
			al += '_'+key+'_';
		jQuery(al+' + input').val('');
		jQuery(al).prev('input').attr('class','reject');
		jQuery(obj).next('div').hide();
		jQuery(al+' label.selected').removeClass('selected');
		ajaxComplite=1;
	}
}
//jQuery('#tr_city .td1').append('+')

function getAjaxListData(value,view,key) { // загрузка списка
	timerid4 = 0;
	var al = '#ajaxlist_'+view;
	if(typeof key !== 'undefined')
		al += '_'+key+'_';
	if(jQuery(al).attr('val')==value) {
		jQuery(al).show();
		jQuery(al+' label:first').addClass('selected');
	}
	else {
		jQuery(al).prev('input').attr('class','load');
		ajaxComplite = 0;
		$.ajax({
			type: "GET",
			url: '/_json.php',
			data: {'_view':'ajaxlist', '_srlz':jQuery('input[name="srlz_'+view+'"]').val(),'_value':value, '_hsh':jQuery('input[name="hsh_'+view+'"]').val()},
			dataType: "json",
			cache:true,
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert('error: '+textStatus);
			},
			success: function(result, textStatus, XMLHttpRequest) {
				var PREV = jQuery(al).prev('input');
				PREV.attr('class','reject');
				if (_Browser.type == 'IE' && 8 > _Browser.version) {
					jQuery('select').toggleClass('hideselectforie7',true);
					jQuery('#tr_'+view).css('z-index','10');
				}
				var txt = '';
				if(result && result.data && jQuery(result.data).size()>0) {
					var c = 0;var temp = 0;
					for(k in result.data) {
						txt += '<label id="ajaxlabel'+result.data[k][0]+'"';
						if(k==0)
							txt += ' class="selected"';
						txt += '>'+result.data[k][1]+'</label>';
						if(result.data[k][1]==value){
							temp = result.data[k][0];
							c++;								
						}
					}
					if(c==1){
						jQuery(al+' + input').val(temp);
						jQuery(al+' #ajaxlabel'+temp).addClass('selectlabel');
						PREV.attr('class','accept');
					}
				}else
					txt = 'не найдено';

				jQuery(al).html(txt).show();
				jQuery(al).attr('val',value);
				jQuery(al+' label').click(function(){
					ajaxlist_click(this,view,key);
				});
				jQuery(al+' label').hover(
					function () {
						$(this).siblings().removeClass('selected');
						$(this).addClass('selected');
					}
				);
				ajaxComplite = 1;
			}
		});
	}
}

function ajaxlist_click(OBJ,view,key) { // событие на клик на элементе списка
	var al = '#ajaxlist_'+view;
	if(typeof key !== 'undefined')
		al += '_'+key+'_';
	var ID = $(OBJ).attr('id');
	ID = ID.substring(9,15);
	jQuery(al+' + input').val(ID).change();
	var PREV = jQuery(al).prev('input');
	PREV.val(jQuery(OBJ).text());
	PREV.attr('class','accept');
	jQuery(al+' label.selectl').removeClass('selectl');
	jQuery(al+' #ajaxlabel'+ID).attr('class','selectl');
	if(ajaxlistover) { // решаем проблему с переключением фокуса
		jQuery(al).hide();
		if (_Browser.type == 'IE' && 8 > _Browser.version)
			jQuery('select').toggleClass('hideselectforie7',false);
	}
	if(jQuery(al+' + input').attr('onchange')) {
		jQuery(al+' + input');
	}
	chFocusList(1);
	show_hide_label(OBJ,view,0);
}

function input_file(obj) {
	var myRe=/.+\.([A-Za-z]{3,4})/i;
	var myArray = myRe.exec(jQuery(obj).val());
	jQuery(obj).parent().find('span.fileinfo').html(myArray[1]);
}

function putEMF(id,txt) {
	jQuery('#tr_'+id+' .form-caption').after('<div class="caption_error">['+txt+']</div>');
}


function invert_select(selector)
{
	$(selector+' input[type=checkbox]').each(function() {
		this.checked = !this.checked;
	});
	return false;
}

function SetWysiwyg(obj) {
	
	var myRe=/([A-Za-z0-9]+)_.+/i;
	var cid = myRe.exec(obj.name);
	if(obj.checked) {
		eval('CKEDITOR.instances.id_'+cid[1]+'.destroy(true);');
	} else {
		eval('cke_'+cid[1]+'();');
	}
}

$(document).ready(function() {
	$('form input[type=int]').keydown(function(event){
		return checkInt(event);
	});
});