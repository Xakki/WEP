<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
	<head>
		<title>{$_tpl['title']}</title>
		<base href="{$_CFG['_HREF']['BH']}"/>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="{$_tpl['keywords']}"/> 
		<meta name="description" content="{$_tpl['description']}"/>
		{$_tpl['meta']}
		<link rel="SHORTCUT ICON" href="{$_tpl['design']}img/favicon.ico"/>
		{$_tpl['styles']}
		{$_tpl['script']}
	</head>
	<body onload="{$_tpl['onload']}">
		<div class="body">
			{$_tpl['logs']}
			<header>
					<div class="free_space">{$_tpl['head']}</div>
					<menu>{$_tpl['headMenu']}</menu>
			</header>
			<content style="margin-left:0;">
				{$_tpl['path']}
				{$_tpl['text']}
			</content>
			
			<footer>
				{$_tpl['footer']}
			</footer>
			<!--{$_tpl['time']}-->
		</div>
		<div class="cloudbg cloud1"></div>
		<div class="cloudbg cloud2"></div>
		<div class="cloudbg cloud3"></div>
		<div class="cloudbg cloud4"></div>
	</body>
</html>
