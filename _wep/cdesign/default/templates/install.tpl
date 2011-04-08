<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
	<head>
		<title>WebEngineOnPHP - {$_SERVER['SERVER_NAME']}</title>
		<base href="{$_CFG['_HREF']['BH']}"/>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="WEP"/> 
		<meta name="description" content="CMS"/>
		<link rel="SHORTCUT ICON" href="{$_tpl['design']}img/favicon.ico"/>
		<script type="text/javascript" src="_design/_script/jquery.js"></script>
		<script type="text/javascript" src="_design/_script/include.js"></script>
		<script type="text/javascript" src="{$_tpl['design']}script/script.js"></script>
		{$_tpl['script']}
		<link type="text/css" href="{$_tpl['design']}style/style.css" rel="stylesheet"/>
		{$_tpl['styles']}
	</head>
	<body onload="{$_tpl['onload']}">
		<div id='wepmain'>
			<div id="sysconf">{$_tpl['title']}</div>
			<div id="modulslist">{$_tpl['info']}</div>
			<div id="modulsforms">{$_tpl['text']}</div>
			<div class='spacer'></div>
		</div>
		<div id="cmsinfo">
			<div class="infname"><a href="http://xakki.ru">WebEngineOnPHP</a></div>
			<div class="infc">{$_tpl['contact']}</div>
			<div id="inftime">{$_tpl['time']}</div>
		</div>
		<div id="debug_view" style="">{$_tpl['logs']}</div>
		
		<div class="debug_view_img">{$_tpl['debug']}<img src="{$_tpl['design']}img/debug_view.png" onclick="fShowHide('debug_view');" alt="DEBUG"/></div>
	</body>
</html>
