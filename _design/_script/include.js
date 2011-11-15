
/*Проверять загружен ли или нет*/
/**
 * $.include - script inclusion jQuery plugin
 * Based on idea from http://www.gnucitizen.org/projects/jquery-include/
 * @author Tobiasz Cudnik
 * @link http://meta20.net/.include_script_inclusion_jQuery_plugin
 * @license MIT
 */
// overload jquery's onDomReady
var jslist = new Array();
var csslist = new Array();
if ( jQuery.browser.mozilla || jQuery.browser.opera ) {
	document.removeEventListener( "DOMContentLoaded", jQuery.ready, false );
	document.addEventListener( "DOMContentLoaded", function(){ jQuery.ready(); }, false );
}
jQuery.event.remove( window, "load", jQuery.ready );
jQuery.event.add( window, "load", function(){ jQuery.ready(); } );
jQuery.extend({
	includeStates: {},
	include: function(url, callback, dependency) {
		url = absPath(url);
		if(jQuery.isEmptyObject(jslist)) {// проверка на уникальность подключаемого стиля
			jslist[url] = 1;
			var flag = 0;
			jQuery('script[src!=""]').each(function(){
				var href = absPath(this.src);
				jslist[href] = 1;
				if(href==url) flag = 1;
			});
			if(flag == 1) {
				jslist[url]=2;
				if ( callback ) callback.call(script);
				return true;
			}
		} else {
			if(jslist[url]) {
				jslist[url]++;
				if ( callback ) callback.call(script);
				return true;
			}
			else jslist[url] = 1;
		}

		if ( typeof callback != 'function' && ! dependency ) {
			dependency = callback;
			callback = null;
		}
		url = url.replace('\n', '');
		jQuery.includeStates[url] = false;
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.onload = function () {
			jQuery.includeStates[url] = true;
			if ( callback )
				callback.call(script);
		};
		script.onreadystatechange = function () {
			if ( this.readyState != "complete" && this.readyState != "loaded" ) return;
			jQuery.includeStates[url] = true;
			if ( callback )
				callback.call(script);
		};
		script.src = url;
		if ( dependency ) {
			if ( dependency.constructor != Array )
				dependency = [dependency];
			setTimeout(function(){
				var valid = true;
				$.each(dependency, function(k, v){
					if (!v ) {//if (!v() ) {
						valid = false;
						return false;
					}
				})
				if ( valid )
					document.getElementsByTagName('head')[0].appendChild(script);
				else
					setTimeout(arguments.callee, 10);
			}, 10);
		}
		else
			document.getElementsByTagName('head')[0].appendChild(script);
		return function(){
			return jQuery.includeStates[url];
		}
	},
	includeCSS: function(url, callback, dependency) {
		url = absPath(url);
		if(jQuery.isEmptyObject(csslist)) {// проверка на уникальность подключаемого стиля
			csslist[url] = 1;
			var flag = 0;
			jQuery('link[href!=""]').each(function(){
				var href = absPath(this.href);
				csslist[href] = 1;
				if(href==url) flag = 1;
			});
			if(flag == 1) {csslist[url]=2;return true;}
		} else {
			if(csslist[url]) {csslist[url]++;return true;}
			else csslist[url] = 1;
		}
		//******
		if ( typeof callback != 'function' && ! dependency ) {
			dependency = callback;
			callback = null;
		}
		url = url.replace('\n', '');
		jQuery.includeStates[url] = false;
		var style = document.createElement('link');
		style.type = 'text/css';
		style.rel = 'stylesheet';
		style.onload = function () {
			jQuery.includeStates[url] = true;
			if ( callback )
				callback.call(style);
		};
		style.onreadystatechange = function () {
			if ( this.readyState != "complete" && this.readyState != "loaded" ) return;
			jQuery.includeStates[url] = true;
			if ( callback )
				callback.call(style);
		};
		style.href = url;
		if ( dependency ) {
			if ( dependency.constructor != Array )
				dependency = [dependency];
			setTimeout(function(){
				var valid = true;
				$.each(dependency, function(k, v){
					if (!v ) {//if (!v() ) {
						valid = false;
						return false;
					}
				})
				if ( valid )
					document.getElementsByTagName('head')[0].appendChild(style);
				else
					setTimeout(arguments.callee, 10);
			}, 10);
		}
		else
			document.getElementsByTagName('head')[0].appendChild(style);
		return function(){
			return jQuery.includeStates[url];
		}
	},
	readyOld: jQuery.ready,
	ready: function () {
		if (jQuery.isReady) return;
		imReady = true;
		$.each(jQuery.includeStates, function(url, state) {
			if (! state)
				return imReady = false;
		});
		if (imReady) {
			jQuery.readyOld.apply(jQuery, arguments);
		} else {
			setTimeout(arguments.callee, 10);
		}
	}
});
var baseHref = '';
function absPath(url) {

	if(url.substr(0,7)!='http://') {
		if(baseHref=='') {
			baseHref = jQuery('base').attr('href');
			if(baseHref=='')
				baseHref = 'http://'+window.location.host;
			else {
				baseHref = trim(baseHref,'/');
			}
		}
		url = baseHref+'/'+trim(url,'/');
	}else {
		var i = url.indexOf('../');
		while(i>-1){
			url = url.replace(RegExp("[^\/]+\/\.\.\/","g"), '');
			i = url.indexOf('../');
		}
	}
	return url;
}

function trim( str, charlist ) {	// Strip whitespace (or other characters) from the beginning and end of a string
	charlist = !charlist ? ' \s\xA0' : charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\$1');
	var re = new RegExp('^[' + charlist + ']+|[' + charlist + ']+$', 'g');
	return str.replace(re, '');
}

/*
var dtt = new Date();

var dtt2 = new Date();
console.log((dtt2.getTime()-dtt.getTime()));
*/