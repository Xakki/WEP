<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
	<head>
		<title>{$_tpl['title']}</title>
		<base href="{$_CFG['_HREF']['BH']}"/>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="WEP"/> 
		<meta name="description" content="CMS"/>
		<link rel="SHORTCUT ICON" href="{$_tpl['design']}img/favicon.ico"/>
		<script type="text/javascript" src="_design/_script/jquery.js"></script>
		<script type="text/javascript" src="_design/_script/include.js"></script>
		<script type="text/javascript" src="_design/_script/utils.js"></script>
		<script type="text/javascript" src="_design/_script/form.js"></script>
		<link type="text/css" href="_design/_style/form.css" rel="stylesheet"/>
		<link type="text/css" href="_design/_style/install.css" rel="stylesheet"/>
	</head>
	<body onload="{$_tpl['onload']}">
		<div class='wepmain'>
			<div class="title">{$_tpl['title']}</div>
			<div class="step">{$_tpl['step']}</div>
			<div class="debug_view">{$_tpl['logs']}</div>
			<div class="text">{$_tpl['text']}</div>
			<div class="cmsinfo">
				<div class="infname"><a href="http://xakki.ru">WebEngineOnPHP</a></div>
				<div class="infc">{$_tpl['contact']}</div>
				<div id="inftime">{$_tpl['time']}</div>
			</div>
		</div>
	</body>
</html>
