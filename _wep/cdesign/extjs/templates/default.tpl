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
		<script type="text/javascript" src="_design/_script/extjs/adapter/ext/ext-base.js"></script>
		<script type="text/javascript" src="_design/_script/extjs/ext-all-debug.js"></script>
		<script type="text/javascript" src="{$_tpl['design']}script/main.js"></script>
		{$_tpl['script']}
		<link type="text/css" href="_design/_script/extjs/resources/css/ext-all-notheme.css" rel="stylesheet"/>
		<link type="text/css" href="_design/_script/extjs/resources/css/xtheme-gray.css" rel="stylesheet"/>
		<link type="text/css" href="{$_tpl['design']}style/style.css" rel="stylesheet"/>

		
		{$_tpl['styles']}
	</head>
	<body onload="{$_tpl['onload']}">
		<div class="leftblock">
			<a class="logo" title="Главная" href="_wep"></a>
			<div style="overflow:auto; height:80%; padding-bottom:30px;">
			<div class="name_block"><span class="triangle_dowm"></span>WEB</div>
			<div class="block">
			{$_tpl['sysconf']}
			</div>
			<div class="name_block"><span class="triangle_dowm"></span>Модули</div>
			<div class="block">
			{$_tpl['modulslist']}
			</div>
			<div class="name_block"><span class="triangle_dowm"></span>Модули</div>
			<div class="block">
			{$_tpl['modulslist']}
			</div>
			<div class="name_block"><span class="triangle_dowm"></span>Модули</div>
			<div class="block">
			{$_tpl['modulslist']}
			</div>
			</div>
		</div>
		<div class="maintext">
			<div class="block">
				<div class="toolbar">{$_tpl['uname']}</div>	
				<div class="path">Путь</div>
				<div id="editform"></div>
				<div class="content" id="modulsforms">{$_tpl['modulsforms']}</div>
			</div>
			<div style="clear:both"></div>
		</div>
		<div class="footer">
			<div class="block">
			{$_tpl['contact']}
			<div class="clear"></div>
			{$_tpl['time']}
			</div>
		</div>
		<div id="debug_view" style="">{$_tpl['logs']}</div>
		<div class="debug_view_img">{$_tpl['debug']}<img src="{$_tpl['design']}img/debug_view.png" onclick="fShowHide('debug_view');" alt="DEBUG"/></div>
	</body>
</html>
