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
		{$_tpl['script']}
		<script type="text/javascript" src="{$_tpl['design']}script/script.js"></script>
		{$_tpl['styles']}
		<link type="text/css" href="{$_tpl['design']}style/style.css" rel="stylesheet"/>
<!--[if lte IE 7]>
<style type="text/css">
	/* bug fixes for IE7 and lower - DO NOT CHANGE */
	.nav .fly {width:99%;} /* make each flyout 99% of the prevous flyout */
	a:active {} /* requires a blank style for :active to stop it being buggy */
</style>
<![endif]-->
	</head>
	<body onload="{$_tpl['onload']}">
		<div id='wepmain'>
			<div id="adminmenu">{$_tpl['adminmenu']}</div>
			<div id="modulsforms">{$_tpl['modulsforms']} {$_tpl['logs']}</div>
			<div class='spacer'></div>
		</div>
		<div id="cmsinfo">
			<div class="infname"><a href="http://xakki.ru">WebEngineOnPHP</a></div>
			<div class="infc">{$_tpl['contact']}</div>
			<div id="inftime">{$_tpl['time']}</div>
		</div>
		<div id="debug_view" style=""></div>
		
		<div class="debug_view_img">{$_tpl['debug']}<img src="{$_tpl['design']}img/debug_view.png" onclick="fShowHide('debug_view');" alt="DEBUG"/></div>
	</body>
</html>
