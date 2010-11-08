
function showi(obj,id,show,hide) {
//ôóíêöèÿ äëÿ îòîáğàæåíèÿ åëåìåíòà id è ñìåíû ğèñóíêà íà îáúåêòå obj
	if(GetId(id).style.display=='block') {
		if(obj.src) 
			obj.src = hide;
		else
			obj.style.backgroundPosition = hide;
		GetId(id).style.display='none';
	}
	else {
		if(obj.src) 
			obj.src = show;
		else 
			obj.style.backgroundPosition = show;
		GetId(id).style.display='block';
	}
}


function count(o) {
	cnt=0;
	if(typeof o=='object'){
		for(var key in o)
			cnt++;
	}
	return cnt;
}

function GetId(id)
{
	return document.getElementById(id);
}
	
function dump(arr, level) {/*àíàëîã ô â ÏÕÏ print_r*/
    var dumped_text = "";
    if(!level) level = 0;

    var level_padding = "    ";

    if(typeof(arr) == 'object') {
        for(var item in arr) {
            var value = arr[item];
 
            if(typeof(value) == 'object') {
                dumped_text += level_padding + "’" + item + "’ …\n";
                if(level>0) dumped_text += dump(value,level-1);
            }
            else {
                dumped_text += level_padding + "’" + item + "’ => \"" + value + "\"\n";
            }
        }
    }
    else {
        dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
    }
    return dumped_text;
}


function OffsetX(evt)
{
	if (evt.offsetX)
	{
		return evt.offsetX;
	}
	return evt.layerX;
}

function OffsetY(evt)
{
	if (evt.offsetY)
	{
		return evt.offsetY;
	}
	return evt.layerY;
}

function WindowEvent(evt)
{
	if (evt == null)
	{
		return window.event;
	}
	return evt;
}


/*àíàëîã ô â ÏÕÏ */
function is_array(a) {
  return a && typeof a == 'object' && a.constructor == Array;
}

function in_array(what, where) {/*àíàëîã ô â ÏÕÏ*/
	var a=false;
	for(var i=0; i<where.length; i++) {
		if(what == where[i]) {
			a=true;
			break;
		}
	}
	return a;
}


function IsComplete(elem)
{
	if (elem.readyState)
	{
		return elem.readyState == "complete";
	}
	else
	{
		return elem.complete;
	}
}

function KeyCode(evt)
{
	if (evt.keyCode)
	{
		return evt.keyCode;
	}
	return evt.which;
}


function insertAfter(parent, node, referenceNode) {
      parent.insertBefore(node, referenceNode.nextSibling);
}

function fckOpen(nm) {
	var txth;
	if($('#tr_'+nm+' td').text()=='') {
		var htm=$('#tr_'+nm+' script').text(); 
		//htm = htm.replace('\'/g','"');
		eval(htm);
		eval("txth=FCKTEXT_"+nm+";");
		$('#tr_'+nm+' td').html(txth);
	}
	//setTimeout(function(){$('#tr_'+nm).slideToggle('fast')}, 400);
	$('#tr_'+nm).toggle();
}