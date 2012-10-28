<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
	<head>
		<title> Личный кабинет - {$_SERVER['SERVER_NAME']}</title> 
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="WEP"/> 
		<meta name="description" content="CMS"/>
		<link rel="SHORTCUT ICON" href="{$_tpl['design']}img/icons.gif"/>
		<script type="text/javascript" src="/script/jquery.js"></script>
		<script type="text/javascript" src="/script/jquery.form.js"></script>
		<!--<script type="text/javascript" src="/script/jquery.fancybox.js"></script><link rel="stylesheet" href="/style/jquery.fancybox.css" type="text/css"/>-->
		<script type="text/javascript" src="/script/utils.js"></script>
		{$_tpl['script']}
		{$_tpl['styles']}
	</head>
	<body onload="{$_tpl['onload']}">
		<div id='wepmain'>
			<div id="sysconf">{$_tpl['sysconf']}</div>
			<div id="modulslist">{$_tpl['modulslist']}</div>
			<div id="modulsforms">{$_tpl['modulsforms']}</div>
			<div class='spacer'></div>
		</div>
		<div id="cmsinfo">
			<div class="infname"><a href="http://xakki.ru">WebEngineOnPHP</a></div>
			<div class="infc">{$_tpl['contact']}</div>
			<div id="inftime">{$_tpl['time']}</div>
		</div>
		<div id="debug_view" style="display:none;">{$_tpl['logs']}</div>
		<img src="img/debug_view.png" class="debug_view_img" onclick="fShowDebug('debug_view');" alt="DEBUG"/>
		
	</body>
</html>
