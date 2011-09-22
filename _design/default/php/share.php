<?php

function tpl_share($data) {
	global $_tpl, $PGLIST;
	$html = array();
	if(!isset($data['wrap']))
		$wrap = array('<span style="padding:0 5px 0 0;display: inline-block;vertical-align:top;">','</span>');
	else
		$wrap = $data['wrap'];
	if(!$data['title']) {
		end($PGLIST->pageinfo['path']);
		$data['title'] = current($PGLIST->pageinfo['path']);
		if(is_array($data['title']))
			$data['title'] = $data['title']['name'];
		
	}
	$data['title'] = htmlentities($data['title'],ENT_QUOTES,'UTF-8');
	$data['desc'] = htmlentities($data['desc'],ENT_QUOTES,'UTF-8');

	if($data['gplus']) {
		$html['gplus'] = '<g:plusone size="standard" count="false"></g:plusone>';
		//<script type="text/javascript">gapi.plusone.go();</script>
		$_tpl['script']['gplus'] = array('https://apis.google.com/js/plusone.js');
/*
<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
	{lang: 'ru', parsetags: 'explicit'}
</script>
*/
	}

	if($data['tw']) {
		$param = ' class="twitter-share-button" data-count="none" data-via="unidoski"';
		//data-url
		$param .= ' data-text="'.$data['title'].'"';
		$html['tw'] = '<a href="http://twitter.com/share"'.$param.'>Tweet</a>';
		$_tpl['script']['tw'] = array('http://platform.twitter.com/widgets.js');
	}

	if($data['fb']) {
		$html['fb'] = '<div id="fb-root"></div><fb:like href="" send="false" layout="button_count" width="130" show_faces="false"></fb:like>
		';
		$_tpl['script']['fb'] = array('http://connect.facebook.net/en_US/all.js#appId=236200693080676&xfbml=1');//ru_RU
      
	}

	if($data['vk']) {
		//http://vkontakte.ru/developers.php
		$vkdata = 'type: "mini", verb: 1, width:50';
		if(isset($data['img']) and $data['img'])
			$vkdata .= ', pageImage:"'.$data['img'].'"';
		$vkdata .= ', pageTitle:"'.$data['title'].'"';
		//pageDescription
		//pageUrl
		$page_id = '';//page_id

		$html['vk'] = '<div id="vk_like"></div>';
		$_tpl['onload'] .= 'VK.Widgets.Like("vk_like", {'.$vkdata.'}'.$page_id.');';
		$_tpl['script']['vk'] = array('http://userapi.com/js/api/openapi.js?33');
		$_tpl['script']['vkinit'] = 'VK.init({apiId: 2415476, onlyWidgets: true});';
	}
	/*$data['fb'] = array(
		'url' => addslashes('http://facebook.com/sharer.php?u='.urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '&t='.$data['title']),
		'width' => 1000,
		'height' => 600,
		'css' => ($data['fb_css']?$data['fb_css']:'i-facebook-32x32'),
	);

	$data['tw'] = array(
		'url' => addslashes('http://twitter.com/share/?url='.urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '&text='.$data['title'].'&via=umobile'),
		'width' => 600,
		'height' => 600,
		'css' => ($data['tw_css']?$data['tw_css']:'i-twitter-32x32'),
	);*/

	/*$data['vk'] = array(
		'url' => addslashes('http://vkontakte.ru/share.php?url='.urlencode($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '&title='.$data['title'] . '&adescription='.$data['desc']),
		'width' => 800,
		'height' => 400,
		'css' => ($data['vk_css']?$data['vk_css']:'i-vkontakte-32x32'),
	);*/


	/*$html .= '<a onclick="window.open(\''.$data['fb']['url'].'\',\'Window1\', \'menubar=no,width='.$data['fb']['width'].',height='.$data['fb']['height'].',toolbar=no\'); return false;"  class="'.$data['fb']['css'].'" target="_blank" href="'.$data['fb']['url'].'" title="Поделиться в Facebook"></a>
	<a onclick="window.open(\''.$data['tw']['url'].'\',\'Window1\', \'menubar=no,width='.$data['tw']['width'].',height='.$data['tw']['height'].',toolbar=no\'); return false;"  class="'.$data['tw']['css'].'" target="_blank" href="'.$data['tw']['url'].'" title="Поделиться в Twitter"></a>
	<a onclick="window.open(\''.$data['vk']['url'].'\',\'Window1\', \'menubar=no,width='.$data['vk']['width'].',height='.$data['vk']['height'].',toolbar=no\'); return false;" class="'.$data['vk']['css'].'" target="_blank" href="'.$data['vk']['url'].'" title="Поделиться в Вконтакте"></a>';*/
	if(count($html)) {
		$html = $wrap[0].implode($wrap[1].$wrap[0],$html).$wrap[1];
	} 
	else 
		$html='';
	return $html;
}