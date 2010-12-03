var timerid = 0;
var timerid2 = 0;
var ajaxComplite = 1;

/*********** FORMa ********/

function JSFR(n) {
	$(n).ajaxForm({
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
				clearTimeout(timerid);
				timerid2 = setTimeout(function(){fShowload(0);},200);
				if(result.html!= undefined && result.html!='') $('#ajaxload').html(result.html);
				if(result.eval!= undefined && result.eval!='') eval(result.eval);
				if(result.text!= undefined && result.text!='') fLog(fSpoiler(result.text,'AJAX text result'),1);

			}

	});
}

function preSubmitAJAX (obj) {
	if(typeof CKEDITOR !== 'undefined') {
		jQuery.each($(obj).find("textarea"),CKSubmit);
	}
	return true;
}

function CKSubmit() {
	nm = $(this).attr('name');
	eval("if(typeof CKEDITOR.instances."+nm+" == 'object') {CKEDITOR.instances."+nm+".updateElement();}");
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
	if(!$('#'+obj.name+'t2').size()){
		val = document.createElement('span');
		val.className = "dscr";
		val.innerHTML = 'Cимволов:<input type="text" id="'+obj.name+'t2" maxlength="4" readonly="false" class="textcount" style="text-align:right;"/>/<input type="text" id="'+obj.name+'t1" maxlength="4" readonly="false" class="textcount" value="'+max+'"/>';
		$(val).appendTo(obj.parentNode);
	}
	if(obj.value.length>max)
		obj.value=obj.value.substr(0,max);
	$('#'+obj.name+'t2').val(obj.value.length);
}

function reloadCaptcha(id)
{
	$('#'+id).attr('src',"/_captcha.php?"+ Math.random());
}

/*PASS FORM*/
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

/*AXAX LIST*/
var timerid4=0;
var timerid5=0;
function show_hide_label(obj,view,flag) {
	clearTimeout(timerid5);
	//$('#tr_city .td1').append(flag+'-');
	if(ajaxComplite==0 || timerid4) {
		setTimeout(function(){show_hide_label(obj,view,flag);},950);
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
			timerid4 = setTimeout(function(){getAjaxListData(obj.value,view);},900);
		else
			timerid4 = setTimeout(function(){ajaxlist(obj,view);},1000);
	}else {
		clearTimeout(timerid4);timerid4=0;
		$('#ajaxlist_'+view+' + input').val('');
		$('#ajaxlist_'+view).prev('input').attr('class','reject');
	}
}
//$('#tr_city .td1').append('+')

function getAjaxListData(value,view) {
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
				if(result && result.data && $(result.data).size()>0) {
					var c = 0;var temp = 0;
					for(k in result.data) {
						txt += '<label id="ajaxlabel'+k+'">'+result.data[k]+'</label>';
						if(result.data[k]==value){
							temp = k;
							c++;								
						}
					}
					if(c==1){
						$('#ajaxlist_'+view+' + input').val(temp);
						$('#ajaxlist_'+view+' #ajaxlabel'+temp).addClass('selectlabel');
						$('#ajaxlist_'+view).prev('input').attr('class','accept');
					}
				}else
					txt = 'не найдено';

				$('#ajaxlist_'+view).html(txt).show();
				$('#ajaxlist_'+view).attr('val',value);
				$('#ajaxlist_'+view+' label').click(function(){
					var key = $(this).attr('id');
					key = key.substring(9,15);
					$('#ajaxlist_'+view+' + input').val(key);
					$('#ajaxlist_'+view).prev('input').val($(this).text());
					$('#ajaxlist_'+view).prev('input').attr('class','accept');
					$('#ajaxlist_'+view+' label').attr('class','');
					$('#ajaxlist_'+view+' #ajaxlabel'+key).attr('class','selectl');
				});
				ajaxComplite = 1;
			}
		});
	}
}

function input_file(obj) {
	var myRe=/.+\.([A-Za-z]{3,4})/i;
	var myArray = myRe.exec($(obj).val());
	$(obj).parent().find('span.fileinfo').html(myArray[1]);
}

function putEMF(id,txt) {
	$('#tr_'+id+' .form-caption').after('<div class="caption_error">['+txt+']</div>');
}