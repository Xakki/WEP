<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
	<head>
		<title>WebEngineOnPHP - {$_SERVER['SERVER_NAME']}</title> 
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="WEP"/> 
		<meta name="description" content="CMS"/>
		<link rel="SHORTCUT ICON" href="{$_tpl['design']}img/icons.gif"/>
		<style type="text/css">
			html, body {text-align:center;vertical-align:middle;height:100%;}
			.cform {
				width:200px;height:160px;
				margin:200px auto 0;}
			.cform form div {
				font-size:11px;text-align:left;
			}
			.cform input {
				border:2px #63A6CC solid;
				width:100%;
				margin:0 0 5px 0;padding:1px 0;
				text-align:center;
			}
			.cform .submit {
				margin:7px 0 0 0px;
			}
			.messhead {
				font-size:19px;
				color:#228B22;
				font-weight:bold;
			}
			.messelem {font-weight:bold;font-size:15px;text-align:center;color:gray;}
			.messelem a {
				font-size:14px;
			}
		</style>
	</head>
	<body onload="">{$_tpl['logs']}
	<div style="height:100%;">
		<div class="cform">
			<form action="login.php" method="post">
				<input type="hidden" name="ref" value="{$_tpl['ref']}"/>
				<div>Логин:</div><input type="text" name="login" tabindex="1"/>
				<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
				<div>Запомнить?<input type="checkbox" style="border:medium none; width:30px;" tabindex="3" name="remember" value="1"/></div>
				<input class="submit" type="submit" name="enter" value="Войти" tabindex="3"/>
			</form>
			<a href="/remind.html">Забыли пароль?</a>
			
		</div>
		{$_tpl['mess']}
	</div>
	{$_tpl['footer']}
		
	</body>
</html>
