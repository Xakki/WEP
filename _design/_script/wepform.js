var timerid = 0;
var timerid2 = 0;
var ajaxComplite = 1;

wep.form = {
    initForm: function (selector, param) {
        var jform = $(selector);

        if (jform.data('initform')) // если уже фрма была ранее инициализирована
        {
            reloadCaptcha('captcha');
            return false;
        }
        jform.data('initform', true);

        // Инициальзация аякса для формы
        wep.form.JSFR(jform, param);

        // Если есть капча
        var captcha = jform.find('#tr_captcha');
        if (captcha.size()) {
            jform.off('click', '.i-reload').on('click', '.i-reload', function () {
                reloadCaptcha('captcha', true);
            });
            jform.off('click', '#tr_captcha input').one('click', '#tr_captcha input', function () {
                wep.setCookie('testtest', 1);
            });
        }

        // AC
        // !!!!!!!!!! Больше не поддерживаем старые браузеры
        // $(selector+' span.labelInput').unbind('click').click(function() {
        // 	$(this).next().focus();
        // });
        // $(selector+' span.labelInput+input').unbind('focus').focus(function() {
        // 	$(this).prev().hide();
        // });
        // $(selector+' span.labelInput+input').unbind('focusout').focusout(function() {
        // 	if(this.value=='')
        // 		$(this).prev().show();
        // });

        // Обязательные поля
        $(selector + ' span.form-require').unbind('click').click(function () {
            var tx = $(this).attr('data-text');
            if (!tx) tx = 'Данное поле обязательно для заполнения!';
            wep.showHelp(this, tx, 2000, 1)
        });

        // Целочисленные поля
        jform.off("keydown.ini change.ini").on("keydown.ini change.ini", "input[type=int]", function (e) {
            return wep.form.checkInt(e);
        });
        // Дробные поля
        jform.off("focus.float").on("focus.float", "input.floatval", function (e) {
            return wep.form.checkFloat(e);
        });

        // Активный элемент формы

        jform.find('.div-tr').off('focusin').on('focusin',function () {
            $(this).addClass('active');
        }).off('focusout').on('focusout', function () {
                $(this).removeClass('active');
            });

        // SUBMIT
        jform.off('click', 'input[type=submit]').on('click', 'input[type=submit]', function () {
            var jObj = $(this);
            if (jObj.attr('name') == 'sbmt_close' && jform.attr('data-prevhref')) {
                location.href = jform.attr('data-prevhref');
                return false;
            }
            else if (jObj.attr('name') == 'sbmt_del') {
                // TODO - удаление без отправки кучи лишних данных
                //sbmt_del = true;
                //return false;
            }
        });

    },


    /**
     * Задаем параметры по умолчанию для АЯКС
     */
    setDefaultParamForm: function (param, jSelector) {
        param['type'] = 'non';

        // Вешаем затемнение на саму форму
        if (!param['fadeobj'])
            param['fadeobj'] = jSelector;

        if (typeof(param['wrapTitle']) == 'undefined')
            param['wrapTitle'] = 1;

        //if(typeof(param['fadeoff'])=='undefined')
        //	param['fadeoff'] = 1;

        wep.setDefaultParam(param);
    },

    // Аякс отправка формы
    JSFR: function (jSelector, param) {
        // NEED INCLUDE jquery.form
        if (!param) param = {};

        wep.include(wep.HREF_script + 'script.jquery/form.js', function () {
            jQuery(jSelector).ajaxForm(
                {
                    debug: 1,
                    beforeSubmit: function (a, f, o) {
                        wep.form.setDefaultParamForm(param, jSelector);

                        wep.loadAnimationOnAjax(param);

                        //var formElement = f[0];
                        o.dataType = 'json';
                        //a.push({name:'HTTP_X_REQUESTED_WITH', value:'xmlhttprequest'});
                        var cid = jQuery(jSelector).attr('data-cid');
                        if (cid) {
                            a.push({name: 'PGCID', value: cid});
                            param.marker = 'PGCID';
                        }
                        //console.log(a,f,o);
                        //console.error('**beforeSubmit', param, o);
                    },

                    error: function (XMLHttpRequest, statusText) {
                        if (XMLHttpRequest.responseText) {
                            var result = {};
                            if (XMLHttpRequest.responseText.substr(0, 1) == '{')
                                result = JSON.parse(XMLHttpRequest.responseText);
                            else {
                                result['text'] = XMLHttpRequest.responseText;
                            }
                            if (typeof(result) == 'object') {
                                // this.success(response, textStatus, XMLHttpRequest);
                                this.success(result);
                                return true;
                            }
                        }
                        logResponseText = XMLHttpRequest.responseText;
                        console.error('!!!!!!!!!!!!!!!', XMLHttpRequest, statusText);
                        alert(statusText + ' - Произошла ошибка при получении данныйх от сервера. Обратитесь в службу поддержки саита.');
                        // TODO : send log 2 server
                    },

                    success: function (result) {
                        console.error('!!!! JSFR success', param);
                        // AJAX форма ничего не выводит, а все делается через onload
                        if (result.formFlag == -1) {
                            $(jSelector).trigger('errorForm', [result, param]); // Ошибка валидации
                        }
                        else if (result.formFlag == 0) {
                            $(jSelector).trigger('showForm', [result, param]); // Обычная загрузка формы
                        }
                        else if (result.formFlag == 1) {
                            $(jSelector).trigger('successForm', [result, param]); // Успешно
                        }

                        wep.ajaxSuccess(result, param);
                    }

                });
            jQuery(jSelector).off('submit', 'sbmt').on('submit', 'sbmt', function () {
                wep.preSubmitAJAX();
            });
            jQuery(jSelector).off('click', '[type=submit],[type=image]', 'sbmt').on('click', '[type=submit],[type=image]', 'sbmt', function () {
                wep.preSubmitAJAX();
            });
        });

    },

    // Делаем форму на странице аяксовой
    ajaxForm: function (jSelector, param) {// OLD
        wep.form.JSFR(jSelector, param);
    },
    /*************************/

    // Мультисписок
    iList: function (obj, k) {
        $(obj).parent().find('.ilist-val').attr('name', k + '[' + $(obj).val() + ']');
    },
    // Мультисписок
    iListRev: function (obj, k) {
        $(obj).parent().find('.ilist-val').attr('name', k + '[' + $(obj).val() + ']');
    },
    iListCopy: function (ths, obj, max) {
        jObj = $(obj);
        var sz = jObj.size();
        if (sz < max || !max) {
            var clon = jObj.clone();
            var in1 = clon.find('.ilist-key');
            var defval = '';
            if (in1.attr('type') == 'int')
                defval = (parseInt(in1.val()) + 1);
            in1.val(defval);
            clon.find('.ilist-val').val('');
            clon.find('.ilistdel').show();
            $(ths).before(clon);
            in1.keyup();
            var cnt = parseInt($(ths).text()) - 1;
            $(ths).text(cnt);
            if (sz == (max - 1) && max) {
                $(ths).fadeTo('slow', 0.3).remove();
            } else {
            }
        }
    },

    iListdel: function (ths) {
        var tmp = $(ths).parent();
        var tmp2 = tmp.parent().find('span.ilistmultiple');
        var cnt = parseInt(tmp2.text()) + 1;
        tmp2.text(cnt);
        if (cnt == 1) tmp2.fadeTo('slow', 1);
        tmp.remove();
    },

    iListsort: function (id) {// сортировка
        // wep.include('/_design/_script/script.jquery/jquery-ui.js', function() {
        $(id).sortable({
            items: '>.ilist',
            axis: 'y',
            helper: 'original',
            opacity: 'false',
            revert: 100,// плавное втыкание
            //placeholder:'sortHelper',
            handle: '.ilistsort',
            tolerance: 'pointer'
            /*start: function(event, ui) {
             //console.log(ui.helper);
             },*/
            //`: function(event, ui) { ... },
            //change: function(event, ui) {console.log('*change');console.log(ui);},
        });
        // });
    },

    // Для старых браузеров не поддерживающие input type=number
    checkInt: function (e) {

        var isUnsigned = false;
        var step = 1;
        var min = 0;
        if (!isUnsigned)
            min = -99999999;
        var max = 99999999;
        var valNegative = '-';
        var val = e.target.value;
        var keyCode = keys_return(e);

        if (e.originalEvent && (e.originalEvent.type == 'keyup' || e.originalEvent.type == 'keydown')) {
            // TODO : доработать условия для спец нажатий
            if (keyCode == KEY.UP || keyCode == KEY.DOWN) // вверх и вверх
            {
                var intValue = toInt(val, isUnsigned);

                if (keyCode == KEY.DOWN) {
                    if (intValue <= min)
                        return false;
                    if (min > (intValue - step))
                        step = (intValue - min);
                    step = -step;
                    console.log('STEP', step, intValue);
                }
                else {
                    if (intValue >= max)
                        return false;
                    if (intValue > (max - step))
                        step = (max - intValue);
                }

                e.target.value = intValue + step;

                jQuery(e.target).change();

                return false;
            }
            if (keyCode >= 96 && keyCode <= 105) // ОТ 96 ДО 105 - NUMLOCK
                return true;
            if (keyCode <= 46) // спец КЛАВИШИ
                return true;
            if (keyCode >= 112 && keyCode <= 123) // ФУНКЦИОНАЛЬНЫЕ КЛАВИШИ
                return true;

            if (keyCode == 109 || keyCode == 189) // знак минус
                return true;

            var сhar = getKeyChar(e);
            if (!isUnsigned && val === сhar && val === valNegative) {
                return true;
            }
            intValue = '';

            if (e.originalEvent.type == 'keyup')
                e.target.value = toInt(val, isUnsigned);
            else {
                if (сhar != '')
                    intValue = сhar.replace(/[^0-9]+/g, '');
                else // Если это спец символ
                    return true;
                console.log(keyCode, '++', сhar, typeof(сhar));

                if (!intValue && intValue !== 0)
                    return false;
            }

        }
        else {
            e.target.value = toInt(val, isUnsigned);
        }

        return true;
    },

    checkFloat: function (ev, param) {
        var myObj = $(ev.target);
        if (myObj.data('checkFloat'))
            return true;
        // todo use extend dafault seting for `param`
        param = {type: "float", beforePoint: myObj.data('data-width0'), afterPoint: myObj.data('data-width1'), defaultValueInput: "0", decimalMark: "."};

        myObj.data('checkFloat', true);
        wep.include('/_design/_script/script.jquery/jquery.numberMask.js', function () {
            myObj.numberMask(param);
        });
    },

    /* Утилита для подсчёта кол сиволов в форме, автоматически создаёт необходимые поля*/
    textareaChange: function (obj, max) {
        if (!max) max = $(obj).attr('maxlength');
        if (!max) max = 5000;
        if (!jQuery('#' + obj.name + 't2').size()) {
            val = document.createElement('div');
            val.className = "dscr txtCounter";
            val.innerHTML = '<span>Cимволов:</span><input type="text" id="' + obj.name + 't2" maxlength="4" disabled class="textcount" style="text-align:right;"/><i>/</i><input type="text" id="' + obj.name + 't1" maxlength="4" disabled class="textcount" value="' + max + '"/>';
            jQuery(obj).after(val);
        }
        if (obj.value.length > max)
            obj.value = obj.value.substr(0, max);
        jQuery('#' + obj.name + 't2').val(obj.value.length);
    },

    putEMF: function (id, txt) {
        jQuery('#tr_' + id + ' .form-caption').after('<div class="caption_error">[' + txt + ']</div>');
        jQuery('#tr_' + id).addClass('error');
    },

    /**
     * Переключатель - редактора
     * @param ths
     * @param id
     */
    editorToggle: function(ths, id) {
        if (ths.checked) {
            globalEval('cke_' + id + '();');
        } else {
            globalEval('CKEDITOR.instances.id_' + id + '.destroy(true);');
        }
    }
}

/*********** FORMa ********/

function JSFR(n, param) {
    wep.form.JSFR(n, param);
}

function textareaChange(obj, max) {
    wep.form.textareaChange(obj, max);
}

function reloadCaptcha(id, noclear) {
    if (!jQuery('#' + id).size()) return false;
    jQuery('#' + id).attr('src', "/_captcha.php?" + toInt(Math.random() * 100));
    if (!noclear)
        jQuery('input.secret').attr('value', '');
}

/*PASS FORM*/
function checkPass(name) {
    jQuery("form input[type=submit]").attr({'disabled': 'disabled'});
    val_1 = jQuery("form input[name=" + name + "]").val();
    val_2 = jQuery("form input[name=re_" + name + "]").val();
    if (val_1.length >= 6) {
        jQuery("form input[name=" + name + "]").attr({'class': 'accept'});
        if (val_1 != val_2)
            jQuery("form input[name=re_" + name + "]").attr({'class': 'reject'});
        else {
            jQuery("form input[name=re_" + name + "]").attr({'class': 'accept'});
            jQuery("form input[type=submit]").removeAttr('disabled');
        }
    } else {
        jQuery("form input[name=" + name + "]").attr({'class': 'reject'});
        jQuery("form input[name=re_" + name + "]").attr({'class': 'reject'});
    }
    return true;
}

function passwordShow(obj) {
    var type1 = 'password';
    var type2 = 'text';
    jQuery(obj).parent().find('input.password').each(function (i, val) {

        if ($(val).attr('type') != 'password') {
            type1 = 'text';
            type2 = 'password';
        }
        var attr = ' type="' + type2 + '" name="' + $(val).attr('name') + '" value="' + $(val).val() + '" class="' + $(val).attr('class') + '"';
        if ($(val).attr('placeholder'))
            attr += ' placeholder="' + $(val).attr('placeholder') + '"';
        $(val).after('<input ' + attr + '/>');
        $(val).remove();
    });
}

function input_file(obj) {
    var myRe = /.+\.([A-Za-z]{3,4})/i;
    var myArray = myRe.exec(jQuery(obj).val());
    jQuery(obj).parent().find('span.fileinfo').html(myArray[1]);
}

function clearErrorForm(formSelector) {
    //reloadCaptcha('captcha');
    jQuery(formSelector).find('.div-tr.error').removeClass('error').find('.caption_error').remove();
}

function invert_select(selector) {
    $(selector + ' input[type=checkbox]').each(function () {
        this.checked = !this.checked;
    });
    return false;
}


/////////////////////////////
///////////swfuploader/////////

wep.swfuploader = {
    PhotosResult: "",
    Count: 0,
    UploadedFiles: 0,
    photos_fileDialogComplete: function (numFilesSelected, numFilesQueued) {
        try {
            if (numFilesQueued > 0) {
//					PhotosResult = numFilesQueued == '1' ? ' картинка' : ' картинки';
//					PhotosResult = numFilesQueued + PhotosResult + " attached";
//					wep.swfuploader.PhotosResult = 'Картинка загружена';
                wep.swfuploader.Count = parseInt(numFilesQueued);
//					$('#AddPhotos').val('Загрузка...');

                $('#' + wep.swfuploader.config.field_name + '_progress_wrap').show();
                $('#' + wep.swfuploader.config.field_name + '_progress_wrap .progress').css('width', 0);

//					$('#submitStatus')
//						.attr('disabled', 'disabled')
//						.addClass('disabled');
                this.startUpload();
            }
        } catch (ex) {
        }
    },

    photos_uploadProgress: function (file, bytesLoaded) {
        try {
            var pw = 100;
//				var w = Math.ceil(pw * (wep.swfuploader.UploadedFiles / wep.swfuploader.Count + (bytesLoaded / (file.size * wep.swfuploader.Count))));			
            var w = Math.ceil(pw * (1 / (bytesLoaded / (file.size * 1))));
            $('#' + wep.swfuploader.config.field_name + '_progress_wrap .progress').stop().animate({width: w + '%'});
        } catch (ex) {
        }
    },

    photos_uploadSuccess: function (file, serverData) {
        var serverData = $.parseJSON(serverData);
        //	$('#'+this.+'_temp_upload')
//			$('#'+wep.swfuploader.config.field_name+'_progress_wrap .progress').stop().css('width', 0);

        if (wep.isDef(serverData['swf_uploader'].name)) {
            wep.swfuploader.Count = 0;
            wep.swfuploader.UploadedFiles = 0;

            this.setFileUploadLimit(this.getSetting('file_upload_limit') + 1);

            //		$('#AddPhotos').val('Upload');
            $('#' + wep.swfuploader.config.field_name + '_temp_upload_img').attr('src', serverData['swf_uploader'].path + serverData['swf_uploader'].name);

            var id;
            id = wep.swfuploader.config.field_name + '_temp_upload_name';
            if ($('#' + id).size() == 0)
                $('#' + wep.swfuploader.swfuPhotos.movieName).after('<input id="' + id + '" type="hidden" name="' + wep.swfuploader.config.field_name + '_temp_upload[name]" value="">')
            $('#' + id).val(serverData['swf_uploader'].name);

            id = wep.swfuploader.config.field_name + '_temp_upload_type';
            if ($('#' + id).size() == 0)
                $('#' + wep.swfuploader.swfuPhotos.movieName).after('<input id="' + id + '" type="hidden" name="' + wep.swfuploader.config.field_name + '_temp_upload[type]" value="">')
            $('#' + id).val(serverData['swf_uploader'].mime_type);

            $('#' + wep.swfuploader.config.field_name + '_notice_swf_uploader').html('Изображение загружено. Сохраните изменения');
            try {
                wep.swfuploader.UploadedFiles++;
            } catch (ex) {

            }
        }
        else {
            alert('Во время загрузки изображения произошли ошибки, пожалуйста обратитесь к администратору');
        }


    },

    photos_uploadComplete: function (file) {
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

    photos_fileQueueError: function (file, errorCode, message) {
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

    swfuploadLoaded: function () {
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

    bindSWFUpload: function (config) {
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
            upload_url: "/_js.php",
            post_params: {
                //"wepID": SESSID
                'fileupload': '1'
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


