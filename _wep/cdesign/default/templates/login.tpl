<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
	<head>
		<title>WEP - {#BH#}</title>
		<base href="{#BH#}"/>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="WEP"/> 
		<meta name="description" content="CMS"/>
		<link rel="SHORTCUT ICON" href="/favicon.ico"/>
		<style type="text/css">
			html, body {text-align:center;vertical-align:middle;height:100%;margin:0;}
		</style>
		<link rel="stylesheet" href="{#BH#}/_design/_style/login.css" type="text/css">
	</head>
	<body onload="">{#logs#}
	<div style="position:relative;top:40%;">
		<div><a href="/index.html">HOME</a></div>
		{#mess#}
		<div class="cform">
			<form action="{#action#}" method="post">
				<input type="hidden" name="ref" value="{#ref#}"/>
				<div>{#login#}:</div><input type="text" name="login" tabindex="1"/>
				<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
				<label style="display:block;">Запомнить?<input type="checkbox" style="border:medium none; width:30px;" tabindex="3" name="remember" value="1"/></label>
				<input class="submit" type="submit" name="enter" value="Войти" tabindex="3"/>
			</form>
			
		</div>
	</div>
	{#footer#}
		
	</body>
</html>
