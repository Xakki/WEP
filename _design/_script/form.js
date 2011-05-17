var timerid = 0;
var timerid2 = 0;
var ajaxComplite = 1;

/*********** FORMa ********/

function JSFR(n) {
	jQuery(n).ajaxForm({
		beforeSubmit: 
			function(a,f,o) {
				//var formElement = f[0];
				o.dataType = 'json';
				//preSubmitAJAX (f);
			},
		/*notsuccess: 
			function(d,statusText) {
				//alert(statusText+'  - form notsuccess: '+d.responseText);
				clearTimeout(timerid);
				timerid2 = setTimeout(function(){fShowload(0);},200);
			},*/
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
}

function preSubmitAJAX (obj) {
	if(typeof CKEDITOR !== 'undefined') {
		jQuery.each(jQuery(obj).find("textarea"),function(){nm=jQuery(this).attr('name');if(nm) eval("if(typeof CKEDITOR.instances."+nm+" == 'object') {CKEDITOR.instances."+nm+".updateElement();}");});
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

function textareaChange(obj,max){/* Утилита для подсчёта кол сиволов в форме, автоматически создаёт необходимые поля*/
	if(!jQuery('#'+obj.name+'t2').size()){
		val = document.createElement('span');
		val.className = "dscr";
		val.innerHTML = 'Cимволов:<input type="text" id="'+obj.name+'t2" maxlength="4" readonly="false" class="textcount" style="text-align:right;"/>/<input type="text" id="'+obj.name+'t1" maxlength="4" readonly="false" class="textcount" value="'+max+'"/>';
		jQuery(val).appendTo(obj.parentNode);
	}
	if(obj.value.length>max)
		obj.value=obj.value.substr(0,max);
	jQuery('#'+obj.name+'t2').val(obj.value.length);
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
			jQuery("form input[type=submit]").attr({'disabled':''});
		}
	}else {
		jQuery("form input[name="+name+"]").attr({'class':'reject'});
		jQuery("form input[name=re_"+name+"]").attr({'class':'reject'});
	}
	return true;
}

function password_new() {
	var type1 = 'password';
	var type2 = 'text';
	if(!jQuery('input[type=password].password').length){
		type1 = 'text';
		type2 = 'password';
	}
	jQuery('.password').after("<input type='"+type2+"' value='"+jQuery('input[type='+type1+'].password').attr('value')+"' name='"+jQuery('input[type='+type1+'].password').attr('name')+"' class='password'/>");
	jQuery('input[type='+type1+'].password').remove();
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
				timerid5 = setTimeout(function(){ajaxlistClear(obj,view,key);},400);
				if(!obj.value){
					jQuery(obj).prev().show();
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

function ajaxlist(obj,view,key) { // функция контроля подгрузки слоя списка
	if(obj.value.length>1) {
		clearTimeout(timerid4);
		if(ajaxComplite==1)
			timerid4 = setTimeout(function(){getAjaxListData(obj.value,view,key);},900);
		else
			timerid4 = setTimeout(function(){ajaxlist(obj,view,key);},1000);
	} else {
		clearTimeout(timerid4);timerid4=0;
		var al = '#ajaxlist_'+view;
		if(typeof key !== 'undefined')
			al += '_'+key+'_';
		jQuery(al+' + input').val('');
		jQuery(al).prev('input').attr('class','reject');
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
	}
	else{
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
				jQuery(al).prev('input').attr('class','reject');
				if (_Browser.type == 'IE' && 8 > _Browser.version) {
					jQuery('select').toggleClass('hideselectforie7',true);
					jQuery('#tr_'+view).css('z-index','10');
				}
				var txt = '';
				if(result && result.data && jQuery(result.data).size()>0) {
					var c = 0;var temp = 0;
					for(k in result.data) {
						txt += '<label id="ajaxlabel'+k+'">'+result.data[k]+'</label>';
						if(result.data[k]==value){
							temp = k;
							c++;								
						}
					}
					if(c==1){
						jQuery(al+' + input').val(temp);
						jQuery(al+' #ajaxlabel'+temp).addClass('selectlabel');
						jQuery(al).prev('input').attr('class','accept');
					}
				}else
					txt = 'не найдено';

				jQuery(al).html(txt).show();
				jQuery(al).attr('val',value);
				jQuery(al+' label').click(function(){
					ajaxlist_click(view,this,key);
					if(jQuery(al+' + input').attr('onchange')) {
						jQuery(al+' + input').change();
					}
				});
				ajaxComplite = 1;
			}
		});
	}
}

function ajaxlist_click(view,ths,key) { // событие на клик на элементе списка
	var al = '#ajaxlist_'+view;
	if(typeof key !== 'undefined')
		al += '_'+key+'_';
	var key = jQuery(ths).attr('id');
	key = key.substring(9,15);
	jQuery(al+' + input').val(key);
	jQuery(al).prev('input').val(jQuery(ths).text());
	jQuery(al).prev('input').attr('class','accept');
	jQuery(al+' label').attr('class','');
	jQuery(al+' #ajaxlabel'+key).attr('class','selectl');
	if(ajaxlistover) { // решаем проблему с переключением фокуса
		jQuery(al).hide();
		if (_Browser.type == 'IE' && 8 > _Browser.version)
			jQuery('select').toggleClass('hideselectforie7',false);
	}
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