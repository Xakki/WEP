<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
	<head>
		<title>WebEngineOnPHP - {$_SERVER['SERVER_NAME']}</title>
		{$_tpl['meta']}
		{$_tpl['styles']}
		{$_tpl['script']}
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
			<div id="modulsforms">{$_tpl['modulsforms']}</div>
			<div class='spacer'></div>
		</div>
		<div id="cmsinfo">
			<div class="infname">
				<a href="http://xakki.ru">WebEngineOnPHP {$_tpl['wep_ver']}</a>
			</div>
			<div id="inftime">{$_tpl['time']}</div>
			<div class="infc">{$_tpl['contact']}</div>
		</div>
		<div id="debug_view" style="">{$_tpl['logs']}</div>
		
		<div class="debug_view_img">{$_tpl['debug']}<img src="{$_tpl['design']}img/debug_view.png" onclick="fShowHide('debug_view');" alt="DEBUG"/></div>
	</body>
</html>
