
function load_href(hrf) {
	if(typeof hrf=='object')
		window.location.href = $(hrf).attr('href');
	else
		window.location.href = hrf;
	return false;
}

var MESS = {
	'del':'Вы действительно хотите провести операцию удаления?',
	'delprof':'Вы действительно хотите удалить свой профиль?',
};
function hrefConfirm(obj,mess)
{
	if(MESS[mess])
		mess = MESS[mess];

	if(confirm(mess)) {
		return true;
	}
	return false;
}


function contentIncParam(obj,path,funcparam) {
	var pagetype = obj.options[obj.selectedIndex].value;
	param = {
		'href':path+'/js.php?_view=contentIncParam&_modul=pg',
		'type':'POST',
		'data': {'pagetype':pagetype,'funcparam':funcparam},
		'call': function () {jQuery('form>div.addparam').remove();jQuery('#tr_pagetype').after(this.html2);}
	};
	//replaceWith
	JSWin(param);
	return false;
}