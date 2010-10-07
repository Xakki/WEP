var lochref = window.location.href;
var i = lochref.indexOf('#', 0);
if(i >= 0) {
	lochref = lochref.substring(0, i);
}
var tmp;
function pagenum(total,order) {
	if(!order) order = false;
	if($('.ppagenum').size()>0 && total>10) {
		pg = 1;
		reg = /_p([0-9]*)/;
		tmp = reg.exec(lochref);
		if(tmp)
			pg =  tmp[1];
		else if(!tmp && order)
			pg = total;
		if(!tmp)
			reg = /(.*)\.html(.*)/g;
		else
			reg = /(.*)_p[0-9]*\.html(.*)/g;
		tmp = reg.exec(lochref);
		//alert(dump(tmp));
		$('.pagenum').css('display','none');
		$('.ppagenum').css('display','block').paginator({pagesTotal:total, 
			pagesSpan:8,
			returnOrder:order,
			pageCurrent:pg, 
			baseUrl: function (page){
				loc = tmp[1];
				if((!order && page!=1) || (order && page!=total))
					loc += '_p'+page;
				loc += '.html'+tmp[2];
				window.location.href = loc;
			},
		});
	}
}

function pagenum_default(total,pageCur,cl,order) {
	if(!order) order = false;
	if($('.ppagenum').size()>0 && total>20) {
		pg = 1;
		reg = new RegExp('\&*'+cl+'_pn=([0-9]*)', 'i');
		tmp = reg.exec(lochref);
		if(tmp)
			pg =  tmp[2];
		else if(!tmp && order)
			pg = total;
		
		loc = lochref.replace(reg, '');
		var param = '';
		if(/\?/.exec(loc)) {
			if(loc.substr(-1,1)!='&' && loc.substr(-1,1)!='?')
				param += '&';
		}
		else
			param += '?';
		param += cl+'_pn=';
		//alert(dump(loc));
		$('.pagenum').css('display','none');
		$('.ppagenum').css('display','block').paginator({pagesTotal:total, 
			pagesSpan:8, 
			returnOrder:order,
			pageCurrent:pageCur, 
			baseUrl: function (page){
				if((!order && page!=1) || (order && page!=total))
					loc += param+page;
				window.location.href = loc;
			},
		});
		//returnOrder: true
	}
}

/*
  jQuery paginator plugin v 1.0
  - based on Paginator 3000  
  - coded by Radik special for Syber Engine
  - (c) Лаборатория разработки web сайтов
  
  Exampel:
    $('.class').paginator(options);
	 options = {
	   pagesTotal  : 100, //all pages count
	   pagesSpan   : 10,  //display pages count
	   pageCurrent : 50,  //current page number
	   baseUrl     : './page/', //may be link "http://www.youwebsite.ru/index.php?page="
	                              or function 
								  function (page_number){
								  	
								  }
	   returnOrder : false, //if true display return order pages false display normal order pages
	   lang        : {next  : "Следующая", //language next
	                  last  : "Последняя", //language last
	    		      prior : "Предыдущая",//language prior
					  first : "Первая",    //language first
					  arrowRight : String.fromCharCode(8594), //language left arrow 
					  arrowLeft  : String.fromCharCode(8592)} //language right arrow
     };
*/

jQuery(function($){$.fn.paginator=function(s){var options={pagesTotal:1,pagesSpan:10,pageCurrent:50,baseUrl:'./page/',returnOrder:false,lang:{next:"Следующая",last:"Последняя",prior:"Предыдущая",first:"Первая",arrowRight:String.fromCharCode(8594),arrowLeft:String.fromCharCode(8592)}};$.extend(options,s);options.pagesSpan=options.pagesSpan<options.pagesTotal?options.pagesSpan:options.pagesTotal;options.pageCurrent=options.pagesTotal<options.pageCurrent?options.pagesTotal:options.pageCurrent;var html={holder:null,table:null,trPages:null,trScrollBar:null,tdsPages:null,scrollBar:null,scrollThumb:null,pageCurrentMark:null};function prepareHtml(el){html.holder=el;$(html.holder).html(makePagesTableHtml());html.table=$(html.holder).find('table:last');html.trPages=$(html.table).find('tr:first');html.tdsPages=$(html.trPages).find('td');html.scrollBar=$(html.holder).find('div.scroll_bar');html.scrollThumb=$(html.holder).find('div.scroll_thumb');html.pageCurrentMark=$(html.holder).find('div.current_page_mark');if(options.pagesTotal==options.pagesSpan){$(html.holder).addClass('fulsize');};};function makePagesTableHtml(){var tdWidth=(100/(options.pagesSpan+2))+'%';var isFunc=$.isFunction(options.baseUrl);var next_page=(parseInt(options.pageCurrent)<parseInt(options.pagesTotal))?parseInt(options.pageCurrent)+1:options.pagesTotal;var next='<a href="';isFunc?next+='javascript:void(0)':next+=options.baseUrl+next_page;next+='" rel="'+next_page+'">%next%</a>';var last='<a href="';isFunc?last+='javascript:void(0)':last+=options.baseUrl+(options.pagesTotal);last+='" rel="'+options.pagesTotal+'">%last%</a>';var prior_page=(parseInt(options.pageCurrent)>1)?parseInt(options.pageCurrent)-1:1;var prior='<a href="';isFunc?prior+='javascript:void(0)':prior+=options.baseUrl+prior_page;prior+='" rel="'+prior_page+'">%prior%</a>';var first='<a href="';isFunc?first+='javascript:void(0)':first+=options.baseUrl+1;first+='" rel="'+1+'">%first%</a>';if(options.returnOrder){var top_left=options.lang.arrowLeft+' '+options.lang.next;var bottom_left=options.lang.last;var top_right=options.lang.prior+' '+options.lang.arrowRight;var bottom_right=options.lang.first;if(options.pageCurrent!==options.pagesTotal){var top_left=next.replace(/%next%/,top_left);var bottom_left=last.replace(/%last%/,bottom_left);};if(options.pageCurrent!==1){var top_right=prior.replace(/%prior%/,top_right);var bottom_right=first.replace(/%first%/,bottom_right);};}else{var bottom_right=options.lang.last;var top_right=options.lang.next+' '+options.lang.arrowRight;var top_left=options.lang.arrowLeft+' '+options.lang.prior;var bottom_left=options.lang.first;if(options.pageCurrent!==options.pagesTotal){var top_right=next.replace(/%next%/,top_right);var bottom_right=last.replace(/%last%/,bottom_right);};if(options.pageCurrent!==1){var top_left=prior.replace(/%prior%/,top_left);var bottom_left=first.replace(/%first%/,bottom_left);};};var html=''+'<table width="100%">'+'<tr>'+'<td class="left top">'+top_left+'</td>'+'<td class="spaser"></td>'+'<td rowspan="2" align="center">'+'<table>'+'<tr>'
for(var i=1;i<=options.pagesSpan;i++){html+='<td width="'+tdWidth+'"></td>';}
html+=''+'</tr>'+'<tr>'+'<td colspan="'+options.pagesSpan+'">'+'<div class="scroll_bar">'+'<div class="scroll_trough"></div>'+'<div class="scroll_thumb">'+'<div class="scroll_knob"></div>'+'</div>'+'<div class="current_page_mark"></div>'+'</div>'+'</td>'+'</tr>'+'</table>'+'</td>'+'<td class="spaser"></td>'+'<td class="right top">'+top_right+'</td>'+'</tr>'+'<tr>'+'<td class="left bottom">'+bottom_left+'</td>'+'<td class="spaser"></td>'+'<td class="spaser"></td>'+'<td class="right bottom">'+bottom_right+'</td>'+'</tr>'+'</table>';return html;};function initScrollThumb(){html.scrollThumb.widthMin='8';html.scrollThumb.widthPercent=options.pagesSpan/options.pagesTotal*100;html.scrollThumb.xPosPageCurrent=(options.pageCurrent-Math.round(options.pagesSpan/2))/options.pagesTotal*$(html.table).width();if(options.returnOrder){html.scrollThumb.xPosPageCurrent=$(html.table).width()-(html.scrollThumb.xPosPageCurrent+Math.round(options.pagesSpan/2)/options.pagesTotal*$(html.table).width());};html.scrollThumb.xPos=html.scrollThumb.xPosPageCurrent;html.scrollThumb.xPosMin=0;html.scrollThumb.xPosMax;html.scrollThumb.widthActual;setScrollThumbWidth();};function setScrollThumbWidth(){$(html.scrollThumb).css({width:html.scrollThumb.widthPercent+"%"});html.scrollThumb.widthActual=$(html.scrollThumb).width();if(html.scrollThumb.widthActual<html.scrollThumb.widthMin)
$(html.scrollThumb).css('width',html.scrollThumb.widthMin+'px');html.scrollThumb.xPosMax=$(html.table).width-html.scrollThumb.widthActual;};function moveScrollThumb(){$(html.scrollThumb).css({left:html.scrollThumb.xPos+"px"});}
function initPageCurrentMark(){html.pageCurrentMark.widthMin='3';html.pageCurrentMark.widthPercent=100/options.pagesTotal;html.pageCurrentMark.widthActual;setPageCurrentPointWidth();movePageCurrentPoint();};function setPageCurrentPointWidth(){$(html.pageCurrentMark).css({width:html.pageCurrentMark.widthPercent+'%'});html.pageCurrentMark.widthActual=$(html.pageCurrentMark).width();if(html.pageCurrentMark.widthActual<html.pageCurrentMark.widthMin)
$(html.pageCurrentMark).css("width",html.pageCurrentMark.widthMin+'px');};function movePageCurrentPoint(){var pos=0;if(html.pageCurrentMark.widthActual<$(html.pageCurrentMark).width()){pos=(options.pageCurrent-1)/options.pagesTotal*$(html.table).width()-$(html.pageCurrentMark).width()/2;}else{pos=(options.pageCurrent-1)/options.pagesTotal*$(html.table).width();};if(options.returnOrder)pos=$(html.table).width()-pos-$(html.pageCurrentMark).width();$(html.pageCurrentMark).css({left:pos+'px'});};function initEvents(){moveScrollThumb();options.returnOrder?drawReturn():drawPages();$(html.scrollThumb).bind('mousedown',function(e){var dx=e.pageX-html.scrollThumb.xPos;$(document).bind('mousemove',function(e){html.scrollThumb.xPos=e.pageX-dx;moveScrollThumb();options.returnOrder?drawReturn():drawPages();});$(document).bind('mouseup',function(){$(document).unbind('mousemove');enableSelection();});disableSelection();});if($.isFunction(options.baseUrl)){$(html.holder).find('a[rel!=""]').bind('click',function(e){var n=parseInt($(this).attr('rel'));options.baseUrl(n);});};$(window).resize(function(){setPageCurrentPointWidth();movePageCurrentPoint();setScrollThumbWidth();});};function drawPages(){var percentFromLeft=html.scrollThumb.xPos/$(html.table).width();var cellFirstValue=Math.round(percentFromLeft*options.pagesTotal);var data="";if(cellFirstValue<1){cellFirstValue=1;html.scrollThumb.xPos=0;moveScrollThumb();}else if(cellFirstValue>=options.pagesTotal-options.pagesSpan){cellFirstValue=options.pagesTotal-options.pagesSpan+1;html.scrollThumb.xPos=$(html.table).width()-$(html.scrollThumb).width();moveScrollThumb();};var isFunc=$.isFunction(options.baseUrl);for(var i=0;i<html.tdsPages.length;i++){var cellCurrentValue=cellFirstValue+i;if(cellCurrentValue==options.pageCurrent){data='<span> <strong>'+cellCurrentValue+'</strong> </span>';}else{data='<span> <a href="';isFunc?data+='javascript:void(0)':data+=options.baseUrl+cellCurrentValue;data+='">'+cellCurrentValue+'</a> </span>';};$(html.tdsPages[i]).html(data);if(isFunc){$(html.tdsPages[i]).find('a').bind('click',function(){options.baseUrl($(this).text());});}};};function drawReturn(){var percentFromLeft=html.scrollThumb.xPos/$(html.table).width();var cellFirstValue=options.pagesTotal-Math.round(percentFromLeft*options.pagesTotal);var data="";if(cellFirstValue<options.pagesSpan){cellFirstValue=options.pagesSpan;html.scrollThumb.xPos=$(html.table).width()-$(html.scrollThumb).width();moveScrollThumb();}else if(cellFirstValue>=options.pagesTotal){cellFirstValue=options.pagesTotal;html.scrollThumb.xPos=0;moveScrollThumb();};var isFunc=$.isFunction(options.baseUrl);for(var i=0;i<html.tdsPages.length;i++){var cellCurrentValue=cellFirstValue-i;if(cellCurrentValue==options.pageCurrent){data='<span> <strong>'+cellCurrentValue+'</strong> </span>';}else{data='<span> <a href="';isFunc?data+='javascript:void(0)':data+=options.baseUrl+cellCurrentValue;data+='">'+cellCurrentValue+'</a> </span>';};$(html.tdsPages[i]).html(data);if(isFunc){$(html.tdsPages[i]).find('a').bind('click',function(){options.baseUrl($(this).text());});}};};function enableSelection(){document.onselectstart=function(){return true;};};function disableSelection(){document.onselectstart=function(){return false;};$(html.scrollThumb).focus();};prepareHtml(this);initScrollThumb();initPageCurrentMark();initEvents();};});