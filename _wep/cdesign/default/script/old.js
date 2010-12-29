
/*if (navigator.appName == 'Netscape') {
	addEventListener(Event.KEYDOWN);
}*/


	function fnShowProps(obj, objName,t){
		var result = "";
		for (var i in obj) // обращение к свойствам объекта по индексу
			result += objName + "." + i + " = " + obj[i] + "<br />\n";
		if(t==1) document.write(result);
		else alert(result);
	}

	

function serialize( mixed_value ) {
	// http://kevin.vanzonneveld.net
	// +   original by: Arpad Ray (mailto:arpad@php.net)
	// +   improved by: Dino
	// +   bugfixed by: Andrej Pavlovic
	// +   bugfixed by: Garagoth
	// %          note: We feel the main purpose of this function should be to ease the transport of data between php & js
	// %          note: Aiming for PHP-compatibility, we have to translate objects to arrays
	// *     example 1: serialize(['Kevin', 'van', 'Zonneveld']);
	// *     returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
	// *     example 2: serialize({firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'});
	// *     returns 2: 'a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}'
 
	var _getType = function( inp ) {
		var type = typeof inp, match;
		var key;
		if (type == 'object' && !inp) {
			return 'null';
		}
		if (type == "object") {
			if (!inp.constructor) {
				return 'object';
			}
			var cons = inp.constructor.toString();
			if (match = cons.match(/(\w+)\(/)) {
				cons = match[1].toLowerCase();
			}
			var types = ["boolean", "number", "string", "array"];
			for (key in types) {
				if (cons == types[key]) {
					type = types[key];
					break;
				}
			}
		}
		return type;
	};
	var type = _getType(mixed_value);
	var val, ktype = '';
 
	switch (type) {
		case "function": 
			val = ""; 
			break;
		case "undefined":
			val = "N";
			break;
		case "boolean":
			val = "b:" + (mixed_value ? "1" : "0");
			break;
		case "number":
			val = (Math.round(mixed_value) == mixed_value ? "i" : "d") + ":" + mixed_value;
			break;
		case "string":
			val = "s:" + mixed_value.length + ":\"" + mixed_value + "\"";
			break;
		case "array":
		case "object":
			val = "a";
			/*
			if (type == "object") {
				var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);
				if (objname == undefined) {
					return;
				}
				objname[1] = serialize(objname[1]);
				val = "O" + objname[1].substring(1, objname[1].length - 1);
			}
			*/
			var count = 0;
			var vals = "";
			var okey;
			var key;
			for (key in mixed_value) {
				ktype = _getType(mixed_value[key]);
				if (ktype == "function") { 
					continue; 
				}
 
				okey = (key.match(/^[0-9]+$/) ? parseInt(key) : key);
				vals += serialize(okey) +
						serialize(mixed_value[key]);
				count++;
			}
			val += ":" + count + ":{" + vals + "}";
			break;
	}
	if (type != "object" && type != "array") val += ";";
	return val;
}


	function _selid(obj) {
		$('div.tiname').attr({'class':'tiname'});
		if(obj)
			$('#'+obj+' > div.tiname').attr({'class':'tiname thover'});
		return 1;
	}

	function addFileInput(obj,cnt) {
		var temp =$(obj.previousSibling).clone();
		if($("input[name="+temp.attr('name')+"]").length>=cnt) {
			return 1;
		}
		temp.attr({'value':''});
		$(obj).before('<img src="/admin/design/default/img/del.png" alt="DEL" style="cursor:pointer;" onclick="delFileInput(this)"/><br/>');
		$(obj).before(temp);
		return 1;
	}

	function delFileInput(obj) {
		$(obj.previousSibling.previousSibling).remove();
		$(obj.previousSibling).remove();
		$(obj).remove();
		return 1;
	}

	
function df(n) {
	if(confirm('Вы действительно хотите безвозвратно удалить "'+n+'" ?')) {
		return true;
	}
	return false;
}
	
function CreateElem(name, attrs, style, text)
{
	var e = document.createElement(name);
	if (attrs) {
		for (key in attrs) {
			if (key == 'class') {
				e.className = attrs[key];
			} else if (key == 'id') {
				e.id = attrs[key];
			} else {
				e.setAttribute(key, attrs[key]);
			}
		}
	}
	if (style) {
		for (key in style) {
			e.style[key] = style[key];
		}
	}
	if (text) {
		e.appendChild(document.createTextNode(text));
	}
	return e;
}


/*
_mask =  new Object();
function checkKey(ev,filter) {
var keys = keys_return(ev);
if (in_array(keys,_mask[filter])) 
	return true;
return false;
}
*/
