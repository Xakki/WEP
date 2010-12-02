
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

function invert_select(form_id)
{
	$('#'+form_id+' input[type=checkbox]').each(function() {
		this.checked = !this.checked;
	});
	return false;
}


