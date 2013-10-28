wep.apply(wep, {
    load_href: function (hrf) {
        /*var base_href = $('base').attr('href');
         if(typeof hrf=='object')
         hrf = $(hrf).attr('href');
         if (hrf.substr(0, 7) != 'http://')
         hrf = base_href+hrf;*/
        window.location.href = hrf;
        return false;
    },
    hrefConfirm: function (obj, mess) {
        if (MESS[mess])
            mess = MESS[mess];

        if (confirm(mess)) {
            return true;
        }
        return false;
    }
});


var MESS = {
    'del': 'Вы действительно хотите провести операцию удаления?',
    'delprof': 'Вы действительно хотите удалить свой профиль?',
};


function contentIncParam(obj, path, funcparam) {
    var pagetype = obj.options[obj.selectedIndex].value;
    if (pagetype) {
        param = {
            'href': path + 'js.php?_view=contentIncParam&_modul=pg',
            'type': 'POST',
            'data': {'pagetype': pagetype, 'funcparam': funcparam},
            'precall': function (result, param) {
                jQuery('form div.flexform').remove();
            },
            'insertobj': '#tr_pagetype',
            'inserttype': 'after',
        };
        //replaceWith
        JSWin(param);
    } else {
        jQuery('form div.flexform').remove();
        jQuery('#tr_funcparam').hide();
    }
    return false;
}