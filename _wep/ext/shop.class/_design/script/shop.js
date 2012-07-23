

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

wep.shop = {
	basketContenId : 0,
	updateBasketBlock : function() {
		wep.ajaxLoadContent(wep.shop.basketContenId,'#basketBlock');
		/*var arr = '';
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
		JSFR(id);*/
	},
	updateFullCost : function() {
		var newSumm = 0;

		$(".basket-list-item tr.checked td.summ span").each(function () {
			newSumm += 1*$(this).text();
		});

		var opt = $('select[name="typedelivery"] option:selected');
		var minsumm = opt.attr('data-minsumm');
		if(!minsumm || newSumm<minsumm)
			newSumm += opt.attr('data-cost')*1;
		$('#basketitogo').text(newSumm);
	}
}

$(document).ready(function() {
	ajaxjob = false;

	/*Табличный список - корзина*/
	$('td.addbasket').each(function(i) {
		$(this).find('a').click(function() {
			if(ajaxjob) return false; // не выполнять пока незавершится работа АЯКС запроса
			var nn = $(this).nextAll('input');
			var pp = $(this).parent().parent();
			ajaxjob = true;
			if($(this).hasClass('addlink')) {
				JSWin({
					'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&id_product='+pp.attr('data-id')+'&count='+nn.val(), 'call':function(){
						ajaxjob = false;
						pp.addClass('sel');
						nn.attr('disabled','disabled');
						wep.shop.updateBasketBlock();
					}
				});
			}
			else {
				JSWin({
					'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&id_product='+pp.attr('data-id')+'&count=0',
					'call':function(){
						ajaxjob = false;
						pp.removeClass('sel');
						nn.removeAttr('disabled');
						wep.shop.updateBasketBlock();
					}
				});
			}
			return false;
		});
	});

	/*Подробная инфа о товаре - корзина*/
	$('div.buybutton a').click(function() {
		var nn = $(this).nextAll('input');
		if(!nn.attr('disabled')) {
			JSWin({'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&id_product='+$(this).attr('data-id')+'&count='+nn.val()});
			nn.attr('disabled','disabled');
			$(this).parent().addClass('sel');
		} else {
			JSWin({'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&id_product='+$(this).attr('data-id')+'&count=0'});
			nn.removeAttr('disabled');
			$(this).parent().removeClass('sel');
		}
		wep.shop.updateBasketBlock();
		return false;
	});

	/*В корзине - удаление*/
	if($('.basket-list-item').size()) {

		$('.basket-list-item .dellink a').click(function() {
			var pp = $(this).parent().parent();
			JSWin({
				'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&id_product='+pp.attr('data-id')+'&count=0',
				'call':function(){
					pp.slideUp().remove();
					wep.shop.updateBasketBlock();
					wep.shop.updateFullCost();
				}
			});
			return false;
		});

		$('.basket-list-item .count input').change(function() {
			var pp = $(this).parent().parent();
			var tp = $(this).parent();
			var oldCost = tp.next().find('span').text();
			var newCost = tp.prev().find('span').text()*this.value;
			tp.next().find('span').text(newCost);
			var deff = (newCost-1*oldCost);
			$('#basketitogo').text((1*$('#basketitogo').text()+deff));
			JSWin({
				'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&id_product='+pp.attr('data-id')+'&count='+this.value,
				'call':function(){
					wep.shop.updateBasketBlock();
					wep.shop.updateFullCost();
				}
			});
			return false;
		});

		$('.basket-list-item input[type="checkbox"]').change(function() {
			var pp = $(this).parent().parent();
			var data = {'_modul':'shopbasket', '_fn':'jsCheckedBasket', 'id_product':pp.attr('data-id'), '_checked':1};
			if(this.checked) {
				$(this).parent().parent().addClass('checked');
			} else {
				$(this).parent().parent().removeClass('checked');
				data['_checked'] = 0;
			}
			wep.shop.updateFullCost();
			JSWin({
				'href':wep.siteJSON,
				'data':data
			});
		});

		$('select[name="typedelivery"]').change(function(){
			wep.shop.updateFullCost();
		});
	}
});
