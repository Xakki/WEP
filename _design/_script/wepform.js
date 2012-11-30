
var timerid = 0;
var timerid2 = 0;
var ajaxComplite = 1;

wep.form = {
	initForm: function() {

		$('form span.labelInput').click(function() {
			$(this).next().focus();
		});
		$('form span.labelInput+input').focus(function() {
			$(this).prev().hide();
		});
		$('form span.labelInput+input').focusout(function() {
			if(this.value=='')
				$(this).prev().show();
		});

		$('form span.form-requere').click(function() {
			var tx = $(this).attr('data-text');
			if(!tx) tx = 'Данное поле обязательно для заполнения!';
			wep.showHelp(this,tx,2000,1)
		});
	},
	// Делаем форму на странице аяксовой
	ajaxForm : function(id,contentID) {
		var arr = '';
		if(wep.pgParam) {
			arr = wep.pgParam;
			arr = arr.join("&pageParam[]=");
		}
		var hrf = wep.siteJS+'?';
		for (var key in wep.pgGet)
		{
			hrf += key+'='+wep.pgGet[key]+'&';
		}
		$(id).attr('action',hrf+'_modul=pg&_fn=AjaxForm&contentID='+contentID+'&pageParam[]='+arr);
		JSFR(id);
	},

	// Аякс отправка формы
	JSFR: function(n) {
		// NEED INCLUDE jquery.form
		wep.include(wep.HREF_script+'script.jquery/form.js', function() {
			jQuery(n).ajaxForm({
				beforeSubmit: 
					function(a,f,o) {
						//var formElement = f[0];
						o.dataType = 'json';
						//wep.preSubmitAJAX (f);
					},
				error: 
					function(d,statusText) {
						alert(statusText+' - form notsuccess (may be wrong json data, see console log)');
						console.log(d);console.log(d.responseText);
					},
				success: 
					function(result) {
						//console.log(result);
						clearTimeout(timerid);
						if(result.html!= undefined && result.html!='') {
							wep.fShowload(1,false,result.html);
						} else
							timerid2 = setTimeout(function(){wep.fShowload(0);},200);
						if(result.onload!= undefined && result.onload!='') eval(result.onload);
						if(result.text!= undefined && result.text!='') fLog(fSpoiler(result.text,'AJAX text result'),1);

					}
			});
		});
	},

	// Мультисписок
	iList : function(obj,k) {
		$(obj).parent().find('.ilist-val').attr('name',k+'['+$(obj).val()+']');
	},
	// Мультисписок
	iListRev : function(obj,k) {
		$(obj).parent().find('.ilist-val').attr('name',k+'['+$(obj).val()+']');
	},
	iListCopy : function(ths,obj,max) {
		var sz = $(obj).size();
		if(sz<max || !max) {
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
			if(sz==(max-1) && max) {
				$(ths).fadeTo('slow', 0.3).remove();
			} else {
			}
		}
	},

	iListdel : function(ths) {
		var tmp = $(ths).parent();
		var tmp2 = tmp.parent().find('span.ilistmultiple');
		var cnt = parseInt(tmp2.text())+1;
		tmp2.text(cnt);
		if(cnt==1) tmp2.fadeTo('slow', 1);
		tmp.remove();
	},

	iListsort : function(id) {// сортировка
		wep.include('/_design/_script/script.jquery/jquery-ui.js', function() {
			$(id).sortable({
				items: '>div.ilist',
				axis:	'y',
				helper: 'original',
				opacity:'false',
				revert: true,// плавное втыкание
				//placeholder:'sortHelper',
				handle: '.ilistsort',
				tolerance: 'pointer',
				/*start: function(event, ui) {
					//console.log(ui.helper);
				},*/
				//sort: function(event, ui) { ... },
				//change: function(event, ui) {console.log('*change');console.log(ui);},
			});
		});
	},

	/*
	39 37 стрелки
	46 делете
	8 удал
	13 интер
	109 минус
	*/
	keys_return : function(ev) 
	{
		var keys=0;
		if (!ev) var ev = window.event;
		if (ev.keyCode) keys = ev.keyCode;
		else if (ev.which) keys = ev.which;
		//
		if(keys==8 || keys==46 || keys==13 || keys==39 || keys==37) keys=0;
		return keys;
	},
	// Для старых браузеров не поддерживающие input type=number
	checkInt : function(event) {
		var val = event.srcElement.value;
		var sgn = '';
		if(val.substring(0,1)=='-')
			sgn = '-';
		val = val.replace(/[^0-9]+/g, '');
		event.srcElement.value = sgn+val;
		return true;
	},

	checkFloat : function(ev, param) {
		var myObj = $(ev.target);
		if(myObj.data('checkFloat'))
			return true;
		// todo use extend dafault seting for `param`
		param = {type:"float", beforePoint: myObj.data('data-width0'), afterPoint: myObj.data('data-width1'), defaultValueInput:"0", decimalMark:"."};

		myObj.data('checkFloat',true);
		wep.include('/_design/_script/script.jquery/jquery.numberMask.js',function() {
			myObj.numberMask(param);
		});
	},

	/* Утилита для подсчёта кол сиволов в форме, автоматически создаёт необходимые поля*/
	textareaChange : function(obj,max) {
		if(!max) max = $(obj).attr('maxlength');
		if(!max) max = 5000;
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

function textareaChange(obj,max) {
	wep.form.textareaChange(obj,max);
}

function reloadCaptcha(id)
{
	jQuery('#'+id).attr('src',"/_captcha.php?"+ Math.random());
	jQuery('input.secret').attr('value','');
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

function passwordShow(obj) {
	var type1 = 'password';
	var type2 = 'text';
	var inp = jQuery(obj).parent().find('input.password');
	jQuery.each(inp, function(i,val) {
		if($(val).attr('type')!='password' ) {
			type1 = 'text';
			type2 = 'password';
		}
		$(val).after("<input name=\""+$(val).attr('name')+"\" type=\""+type2+"\" value=\""+$(val).attr('value')+"\" class=\"password\"/>");
		$(val).remove();
	});
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


/////////////////////////////
///////////swfuploader/////////

wep.swfuploader = {
		PhotosResult : "",
		Count : 0,
		UploadedFiles : 0,
		photos_fileDialogComplete : function(numFilesSelected, numFilesQueued) {
			try {
				if (numFilesQueued > 0) {
//					PhotosResult = numFilesQueued == '1' ? ' картинка' : ' картинки';
//					PhotosResult = numFilesQueued + PhotosResult + " attached";
//					wep.swfuploader.PhotosResult = 'Картинка загружена';
					wep.swfuploader.Count = parseInt(numFilesQueued);
//					$('#AddPhotos').val('Загрузка...');

					$('#'+wep.swfuploader.config.field_name+'_progress_wrap').show();
					$('#'+wep.swfuploader.config.field_name+'_progress_wrap .progress').css('width', 0);
					
//					$('#submitStatus')
//						.attr('disabled', 'disabled')
//						.addClass('disabled');
					this.startUpload();
				}
			} catch (ex) {
			}
		},

		photos_uploadProgress : function(file, bytesLoaded) {
			try {					
				var pw = 100;				
//				var w = Math.ceil(pw * (wep.swfuploader.UploadedFiles / wep.swfuploader.Count + (bytesLoaded / (file.size * wep.swfuploader.Count))));			
				var w = Math.ceil(pw * (1 / (bytesLoaded / (file.size * 1))));			
				$('#'+wep.swfuploader.config.field_name+'_progress_wrap .progress').stop().animate({width: w+'%'});
			} catch (ex) {
			}
		},
		
		photos_uploadSuccess : function(file, serverData) {
			var serverData = $.parseJSON(serverData);
		//	$('#'+this.+'_temp_upload')
//			$('#'+wep.swfuploader.config.field_name+'_progress_wrap .progress').stop().css('width', 0);

			if (wep.isDef(serverData['swf_uploader'].name)) {
				wep.swfuploader.Count = 0;
				wep.swfuploader.UploadedFiles = 0;

				this.setFileUploadLimit(this.getSetting('file_upload_limit')+1);

		//		$('#AddPhotos').val('Upload');
				$('#'+wep.swfuploader.config.field_name+'_temp_upload_img').attr('src', serverData['swf_uploader'].path+serverData['swf_uploader'].name);
				
				var id;
				id = wep.swfuploader.config.field_name+'_temp_upload_name';
				if($('#'+id).size()==0)
					$('#'+wep.swfuploader.swfuPhotos.movieName).after('<input id="'+id+'" type="hidden" name="'+wep.swfuploader.config.field_name+'_temp_upload[name]" value="">')
				$('#'+id).val(serverData['swf_uploader'].name);

				id = wep.swfuploader.config.field_name+'_temp_upload_type';
				if($('#'+id).size()==0)
					$('#'+wep.swfuploader.swfuPhotos.movieName).after('<input id="'+id+'" type="hidden" name="'+wep.swfuploader.config.field_name+'_temp_upload[type]" value="">')
				$('#'+id).val(serverData['swf_uploader'].mime_type);

				$('#'+wep.swfuploader.config.field_name+'_notice_swf_uploader').html('Изображение загружено. Сохраните изменения');
				try {
					wep.swfuploader.UploadedFiles++;
				} catch (ex) {

				}
			}
			else {
				alert('Во время загрузки изображения произошли ошибки, пожалуйста обратитесь к администратору');
			}
				
			
		},

		photos_uploadComplete : function(file) {	
			try {
				if (this.getStats().files_queued > 0) {
					this.startUpload();
				} else {
			//		$('#UploadPhotos').hide();
//					$('#UploadResult').html(wep.swfuploader.PhotosResult);
				}
			} catch (ex) {
			}
		},
		
		photos_fileQueueError : function(file, errorCode, message) {
			try {
				switch (errorCode) {
					case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
						alert('Можно загружать не более одной картинки');
						break;
					case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
					case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
					case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
					case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
						break;
				}
			} catch (ex) {
			}

		},

		swfuploadLoaded : function() {		
/*			$('#Buttons object').hover(
				function() {
					$(this).next().addClass('hover');
				}
				,
				function() {
					$(this).next().removeClass('hover');
				}
			); */
		},
		
		bindSWFUpload : function(config) {
			var defaults = {
				file_dialog_complete_handler: wep.swfuploader.photos_fileDialogComplete,
				upload_progress_handler: wep.swfuploader.photos_uploadProgress,
				upload_success_handler: wep.swfuploader.photos_uploadSuccess,
				upload_complete_handler: wep.swfuploader.photos_uploadComplete,
				swfupload_loaded_handler: wep.swfuploader.swfuploadLoaded,
				file_queue_error_handler: wep.swfuploader.photos_fileQueueError,
				file_size_limit: "2 MB",
				file_types: "*.jpg;*.png;*.gif",
				file_types_description: "JPG, PNG, GIF",
				file_upload_limit: "1",
				flash_url: "/_design/_script/SWFUpload/swfupload_fp10/swfupload.swf",
				upload_url: "/_json.php",
				post_params: {
					//"wepID": SESSID
					'fileupload':'1'
				},
				button_width: 65,
				button_height: 29,
				button_image_url: "/_design/_img/spacer.gif",

				button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
				button_cursor: SWFUpload.CURSOR.HAND
			};
			
			this.config = $.extend(defaults, config);
			wep.swfuploader.swfuPhotos = new SWFUpload(this.config);
		}
	}


