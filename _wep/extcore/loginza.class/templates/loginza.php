<?php

function tpl_loginza($data)
{
	$html = '<iframe src="http://loginza.ru/api/widget?overlay=loginza&'.$data['src'].'" style="'.$data['style'].'" scrolling="no" frameborder="no" id="loginzaiframe"></iframe>';

	return $html;
}
						