<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
	<head>
		<title>{$_tpl['title']}</title>
		<base href="{$_tpl['BH']}"/>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="{$_tpl['keywords']}"/> 
		<meta name="description" content="{$_tpl['description']}"/>
		<link rel="SHORTCUT ICON" href="favicon.ico"/>
		{$_tpl['styles']}
		{$_tpl['script']}
	</head>
	<body onload="{$_tpl['onload']}">
		{$_tpl['logs']}
		<div class="header">
			{$_tpl['head']}
		</div>
		<div class="leftblock">
			{$_tpl['left_column']}
		</div>
		<div class="center" style="margin:0 0 0 200px;">
			{$_tpl['path']}
			<div class="text">
				{$_tpl['text']}
			</div>
		</div>
		<div class="clear"></div>
		
		<div class="footer">
			<div class="block">
				{$_tpl['foot']}
			</div>
		</div>
		<!--{$_tpl['time']}-->
	</body>
</html>
