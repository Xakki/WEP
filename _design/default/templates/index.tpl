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
		<link rel="SHORTCUT ICON" href="{$_tpl['design']}img/favicon.ico"/>
		{$_tpl['styles']}
		{$_tpl['script']}
	</head>
	<body onload="{$_tpl['onload']}">
		{$_tpl['logs']}
		<div class="header">
			<div class="block">
				<div class="blocktext">
					{$_tpl['head']}
				</div>
			</div>
		</div>
		<div class="leftblock">
			<div class="leftadd">{$_tpl['blockadd']}</div>
			<div class="leftparam">
				<div class="block">
					<div class="blocktext">{$_tpl['param']}</div>
				</div>
			</div>
		</div>
		<div class="maintext">
			<div class="block">
				<div class="blocktext">
					{$_tpl['path']}
					<div class="hrb"></div>
					{$_tpl['text']}
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		
		<div class="footer">
			<div class="block">
				{$_tpl['foot']}
				<div class="w3c">{$_tpl['counter']}</div>
			</div>
		</div>
		<!--{$_tpl['time']}-->
	</body>
</html>
