

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
	JSWin({'href':'/_js.php?_modul=product&_fn=AjaxShopParam&_id='+id+'&_rid='+jQuery(obj).val(),'insertobj':objAfter,'body':objAfter,'inserttype':'after'});
	return true;
}

wep.shop = {
	basketContenId : 0,
	updateBasketBlock : function() {
		wep.ajaxLoadContent(wep.shop.basketContenId, '#basketBlock', function() {
			jQuery("#basketBlock").css('margin-top', (jQuery(window).scrollTop()+5)+'px');
		});
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
		jQuery(id).attr('action',hrf+'_modul=pg&_fn=AjaxForm&contentID='+contentID+'&pageParam[]='+arr);
		JSFR(id);*/
	},
	updateFullCost : function() {
		var newSumm = 0;

		jQuery(".basket-list-item tr.checked td.summ span").each(function () {
			newSumm += 1*jQuery(this).text();
		});

		var opt = jQuery('#typedelivery input[name="typedelivery"]:checked');
		var minsumm = opt.attr('data-minsumm');
		if(!minsumm || newSumm<minsumm)
			newSumm += opt.attr('data-cost')*1;
		jQuery('#basketitogo').text(newSumm);
	}
}

jQuery(document).ready(function() {
	ajaxjob = false;

	jQuery('#basketBlock').live('click', function() {
		window.location.href = '/'+wep.shop.pageBasket;
		return false;
	});
	var offset = jQuery("#basketBlock").offset();
	var topPadding = 15;

	if (offset)
		jQuery(window).scroll(function() {
			if (jQuery(window).scrollTop() > offset.top) {
				jQuery("#basketBlock").stop().animate({marginTop: jQuery(window).scrollTop() - offset.top + topPadding});
			}
			else {
				jQuery("#basketBlock").stop().animate({marginTop: 0});
			}
		});

	/*Табличный список - корзина*/
	jQuery('td.addbasket').each(function(i) {
		jQuery(this).find('a').click(function() {
			if(ajaxjob) return false; // не выполнять пока незавершится работа АЯКС запроса
			if(jQuery(this).hasClass('addlink')) {
				var nn = jQuery(this).nextAll('input');
				var pp = jQuery(this).parent().parent();
				ajaxjob = true;
				JSWin({
					'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&product_id='+pp.attr('data-id')+'&count='+nn.val(), 'call':function(){
						ajaxjob = false;
						pp.addClass('sel');
						nn.attr('disabled','disabled');
						wep.shop.updateBasketBlock();
					}
				});
			}
			else {
				window.location.href = '/'+wep.shop.pageBasket;
				/*JSWin({
					'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&product_id='+pp.attr('data-id')+'&count=0',
					'call':function(){
						ajaxjob = false;
						pp.removeClass('sel');
						nn.removeAttr('disabled');
						wep.shop.updateBasketBlock();
					}
				});*/
			}
			return false;
		});
	});

	/*Подробная инфа о товаре - корзина*/
	jQuery('div.proditem .prodBlock .prodBlock-buy1').click(function() {
		JSWin({'href':wep.siteJSON+'?_modul=shop&_fn=jsOrder&id='+jQuery(this).attr('data-id')});
		return false;
	});
	jQuery('div.proditem .prodBlock .prodBlock-basket').click(function() {
		if(jQuery(this).hasClass('inbasket'))
			window.location.href = '/'+wep.shop.pageBasket;
		else {
			JSWin({
				'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&product_id='+jQuery(this).attr('data-id')+'&count=1',
				'call':function(){
					wep.shop.updateBasketBlock();
				}
			});
			
			jQuery(this).addClass('inbasket');
		}
		return false;
	});
	/*jQuery('div.buybutton a').click(function() {
		var nn = jQuery(this).nextAll('input');
		if(!nn.attr('disabled')) {
			JSWin({'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&product_id='+jQuery(this).attr('data-id')+'&count='+nn.val()});
			nn.attr('disabled','disabled');
			jQuery(this).parent().addClass('sel');
		} else {
			JSWin({'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&product_id='+jQuery(this).attr('data-id')+'&count=0'});
			nn.removeAttr('disabled');
			jQuery(this).parent().removeClass('sel');
		}
		wep.shop.updateBasketBlock();
		return false;
	});*/

	/*В корзине - удаление*/
	if(jQuery('.basket-list-item').size()) {

		jQuery('.basket-list-item .dellink a').click(function() {
			var pp = jQuery(this).parent().parent();
			JSWin({
				'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&product_id='+pp.attr('data-id')+'&count=0',
				'call':function(){
					pp.slideUp().remove();
					wep.shop.updateBasketBlock();
					wep.shop.updateFullCost();
				}
			});
			return false;
		});

		jQuery('.basket-list-item .count input').change(function() {
			var pp = jQuery(this).parent().parent();
			var tp = jQuery(this).parent();
			var oldCost = tp.next().find('span').text();
			var newCost = tp.prev().find('span').text()*this.value;
			tp.next().find('span').text(newCost);
			var deff = (newCost-1*oldCost);
			jQuery('#basketitogo').text((1*jQuery('#basketitogo').text()+deff));
			JSWin({
				'href':wep.siteJSON+'?_modul=shopbasket&_fn=jsAddBasket&product_id='+pp.attr('data-id')+'&count='+this.value,
				'call':function(){
					wep.shop.updateBasketBlock();
					wep.shop.updateFullCost();
				}
			});
			return false;
		});

		jQuery('.basket-list-item input[type="checkbox"]').change(function() {
			var pp = jQuery(this).parent().parent();
			var data = {'_modul':'shopbasket', '_fn':'jsCheckedBasket', 'product_id':pp.attr('data-id'), '_checked':1};
			if(this.checked) {
				jQuery(this).parent().parent().addClass('checked');
			} else {
				jQuery(this).parent().parent().removeClass('checked');
				data['_checked'] = 0;
			}
			wep.shop.updateFullCost();
			JSWin({
				'href':wep.siteJSON,
				'data':data
			});
		});

		jQuery('#typedelivery input').change(function(ev) {
			jQuery('#typedelivery label').removeClass('select');
			$(ev.target).parent().addClass('select');
			wep.shop.updateFullCost();
		});
	}
});
