

function insertAfter(parent, node, referenceNode) {
      parent.insertBefore(node, referenceNode.nextSibling);
}

function textareaChange(obj,max){/* Утилита для подсчёта кол сиволов в форме, автоматически создаёт необходимые поля*/
	if(!GetId(obj.name+'t2')){
		val = document.createElement('span');
		val.className = "dscr";
		val.innerHTML = 'Cимволов:<input type="text" id="'+obj.name+'t2" maxlength="4" readonly="false" class="textcount" style="text-align:right;"/>/<input type="text" id="'+obj.name+'t1" maxlength="4" readonly="false" class="textcount" value="'+max+'"/>';
		insertAfter(obj.parentNode,val,obj);
	}
	if(obj.value.length>max) obj.value=obj.value.substr(0,max);
		GetId(obj.name+'t2').value = obj.value.length;
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


/************** аналог ф в ПХП ***************/
function is_array(a) {
  return a && typeof a == 'object' && a.constructor == Array;
}

function in_array(what, where) {/*аналог ф в ПХП*/
	var a=false;
	for(var i=0; i<where.length; i++) {
		if(what == where[i]) {
			a=true;
			break;
		}
	}
	return a;
}
