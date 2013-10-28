/* Подгрузка формы фильтра для продукции аяксом */
function ajax_display_filter(start_field, filter_num, obj, cat_id, content_id, end_field) {
    if (typeof end_field === 'undefined') {
        end_field = 0;
    }

    var params = {
        start_field: start_field,
        end_field: end_field,
        filter_num: filter_num,
        cat_id: cat_id,
        content_id: content_id
    }

    $.ajax({
        data: params,
        type: "get",
        url: "typo3conf/ext/apit_shop/pi3/filter/show_all.php",
        dataType: "html",
        success: function (data, textStatus) {
            //		obj.innerHTML = 'Скрыть';

            if (end_field == 0) {
                document.cookie = "apitshop_filter[" + content_id + "][" + filter_num + "]=2;path=/;";
            }
            else {
                document.cookie = "apitshop_filter[" + content_id + "][" + filter_num + "]=1;path=/;";
            }

            obj.onclick = function () {
                //			display_filter(this,filter_num);
                if (end_field == 0) {
                    end_field = min_filter_fields;
                }
                else {
                    end_field = 0;
                }
                ajax_display_filter(1, filter_num, obj, cat_id, content_id, end_field);

            }

//			document.getElementById('add_filter_flds_'+filter_num).innerHTML = data;
            $('#f_fields_' + filter_num).html(data);
//			display_filter(obj,filter_num);
            display_filter_labels();

            do_cuSel();
            $('#fancybox_sort').fancybox();

        }
    });
}
/* Подгрузка фильтра для продукции аяксом */

/* Скрыть/показать фильтр для продукции */
function display_filter(obj, filter_num) {
    add_filter = $('#add_filter_flds_' + filter_num);
    if (add_filter.is(":hidden")) {
        document.cookie = "apitshop_filter[" + content_id + "][" + filter_num + "]=2;path=/;";
        add_filter.fadeIn('slow');
        do_cuSel();
    }
    else {
        document.cookie = "apitshop_filter[" + content_id + "][" + filter_num + "]=1;path=/;";
        add_filter.fadeOut('slow');
    }
}
/* Скрыть/показать фильтр для продукции */


/* Очистка фильтра для продукции */
function clear_filter(content_id, url_templates, hash) {
    var params = "pref[content_id]=" + content_id + "&pref[url_templates]=" + url_templates + "&pref[hash]=" + hash;

    $.ajax({
        url: "typo3conf/ext/apit_shop/pi3/filter/ajax_filter.php?act=clear",
        type: "post",
        data: params,
        dataType: "html",
        success: function (data, textStatus) {
            document.getElementById('list_prod').innerHTML = data;

            clear_form();
            display_filter_labels();
            $('#fancybox_sort').fancybox();
        }
    });
}
/* Очистка фильтра для продукции */


function clear_form() {
    var filter_form = document.getElementById('filter_form');
    $(filter_form).find(':input').each(function () {
        switch (this.type) {
            case 'hidden':
                if (this.parentNode.id == 'cuselFrame-') {
                    $(this).val('');
                }
                break;
            case 'password':
            case 'select-multiple':
            case 'select-one':
            case 'text':
            case 'textarea':
                $(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
        }
    });
    $(filter_form).find('#cuselFrame-').each(function () {
        sel_items = $(this).find('.cusel-scroll-wrap #cusel-scroll- span');
        sel_items.removeClass('cuselActive');
        first_sel_item = sel_items.first();
        first_sel_item.addClass('cuselActive');
        $(this).find('.cuselText').html(first_sel_item.html());
    });
    $(filter_form).find('div.bold').removeClass('bold');
}

/* надписи от и до на соответствующих полях в фильтре */
function disp_bg_fld() {
    $('.interv_field').focus(function () {
        $(this).prev().css('display', 'none');
    });
    $('.interv_field').blur(function () {
        if (this.value == '') {
            $(this).prev().css('display', '');
        }
    });
}
/* надписи от и до на соответствующих полях в фильтре */


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
    return(setStr);
}


$(document).ready(function () {
    // надписи от и до на соответствующих полях в фильтре
//	disp_bg_fld();

    // отображение надписи "показать всё"
    $('.show_all').css('display', '');

    // отправка формы с фильтром
    document.forms['filter_form'].onsubmit = function () {
        if (this.elements['pref[not_ajax]'] != undefined) {
            return true;
        }

        submit_flt_form(this, null);

        return false;
    }


    // убираем подписи к полям, где есть текст


    // раскрываем полный фильтр или скрываем его там, где нужно
    do_cuSel();
    for (i = 1; i <= 4; i++) {
        apishop_filter = getCookie("apitshop_filter[" + content_id + "][" + i + "]");
        if (apishop_filter == 2) {
            obj = $('#filter_content_' + i + ' .show_all').get(0);
            ajax_display_filter(1, i, obj, cat_id, content_id);
        }
        else if (apishop_filter == 0) {
            $('#filter_content_' + i + ' div:first').attr('class', 'label_s fhide');
            $('#filter_content_' + i + ' .f_content').css('display', 'none');
        }
    }

    display_filter_labels();


    // раскрытие фильтра
    $(".label_s").click(function () {
        id_filter = $(this).parent().attr('id');
        num_filter = id_filter.charAt(id_filter.length - 1);
        if ($(this).next(".f_content").is(":hidden")) {
            document.cookie = "apitshop_filter[" + content_id + "][" + num_filter + "]=1;path=/;";
            $(this).next(".f_content").fadeIn("slow");
            $(this).attr('class', 'label_s');
        } else {
            document.cookie = "apitshop_filter[" + content_id + "][" + num_filter + "]=0;path=/;";
            $(this).next(".f_content").fadeOut("slow");
            $(this).attr('class', 'label_s fhide');
        }
    });
    do_cuSel();
});

function display_filter_labels() {
    var filter_form = document.getElementById('filter_form');
    for (i = 0; i < filter_form.length; i++) {
        if (filter_form[i].tagName == 'INPUT' && filter_form[i].type == 'text' && filter_form[i].value != '') {
            $(filter_form[i]).prev().css('display', 'none');
        }
        else {
            $(filter_form[i]).prev().css('display', '');
        }
    }
}


function submit_flt_form(flt_form, vendor) {
    if (flt_form.elements['pref[not_ajax]'] != undefined) {
        if (vendor != undefined) {
            flt_form.innerHTML += '<input type="hidden" name="vendor_id" value="' + vendor + '">';
        }
        flt_form.submit();
        return true;
    }

    if (vendor != undefined) {
        var pars = 'vendor_id=' + vendor + '&';
    }
    else {
        var pars = '';
    }

    for (var i = 0; i < flt_form.length; i++) {
        if (flt_form[i].type == 'checkbox') {
            if (flt_form[i].checked == true || flt_form[i].checked == 'checked') {
                pars += flt_form[i].name + '=' + flt_form[i].value + '&';
            }
        }
        else {
            pars += flt_form[i].name + '=' + flt_form[i].value + '&';
        }
    }

    var flag = true;
    $.ajax({
        url: "typo3conf/ext/apit_shop/pi3/filter/ajax_filter.php?act=set",
        type: "post",
        data: pars,
        dataType: "html",
        success: function (data, textStatus) {
            flag = false;
            $.fancybox.close();
            document.getElementById('list_prod').innerHTML = data;
        }
    });

    window.setTimeout(function () {
        if (flag == true) {
            $.fancybox('<img src="uploads/pics/preload.jpeg">', {
                'scrolling': 'no',
                'transitionIn': 'none',
                'transitionOut': 'none',
                'padding': 0,
                'showCloseButton': false
            });
        }
    }, 500);
}


function do_cuSel() {
    var params = {
        changedEl: ".all_filter select",
        visRows: 70,
        scrollArrows: true,
        checkZIndex: true
    }
    cuSel(params);

    jQuery("#showSel").click(
        function () {
            jQuery("#hidden-select")
                .css("display", "block")
                .css("width", "150px");
            /* показанному селект указываем width */
            params = {
                refreshEl: "#hidden-select",
                visRows: 4
            }
            cuSelRefresh(params);
        });
}






