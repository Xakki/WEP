<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
	<head>
		<title>WebEngineOnPHP - {$_SERVER['SERVER_NAME']}</title>
		<base href="{$_CFG['_HREF']['BH']}"/>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="WEP"/> 
		<meta name="description" content="CMS"/>
		<link rel="SHORTCUT ICON" href="{$_tpl['design']}img/favicon.ico"/>
		<style type="text/css">
			html, body {text-align:center;vertical-align:middle;height:100%;margin:0;}
		</style>
		<link rel="stylesheet" href="{$_CFG['_HREF']['BH']}_design/_style/login.css" type="text/css">
	</head>
	<body onload="">{$_tpl['logs']}
	<div style="position:relative;top:40%;">
		{$_tpl['mess']}
		<div class="cform">
			<form action="{$_tpl['action']}" method="post">
				<input type="hidden" name="ref" value="{$_tpl['ref']}"/>
				<div>Логин:</div><input type="text" name="login" tabindex="1"/>
				<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
				<div>Запомнить?<input type="checkbox" style="border:medium none; width:30px;" tabindex="3" name="remember" value="1"/></div>
				<input class="submit" type="submit" name="enter" value="Войти" tabindex="3"/>
			</form>
			<a href="remind.html">Забыли пароль?</a>
			
		</div>
	</div>
	{$_tpl['footer']}
		
	</body>
</html>
