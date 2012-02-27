

var timerid3;
function productshop(obj,id) {
	clearTimeout(timerid3);
	timerid3 = setTimeout(function(){productshopExe(obj,id);},400);
	return true;
}

function productshopExe(obj,id) {
	var objAfter;
	if(!obj) return 0;
	objAfter = '#tr_'+obj;
	obj=jQuery('select[name='+obj+']');

	if(!id) id='';
	jQuery('.addparam').remove();
	JSWin({'href':'/_js.php?_modul=product&_fn=AjaxShopParam&_id='+id+'&_rid='+jQuery(obj).val(),'insertObj':objAfter,'body':objAfter,'insertType':'after'});
	return true;
}