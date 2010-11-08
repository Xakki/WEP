
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


