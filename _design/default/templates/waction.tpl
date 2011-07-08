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
		<style type="text/css">
			html, body {text-align:center;vertical-align:middle;height:100%;}
			.cform {
				background:none repeat scroll 0 0 #F6F6F6;
				width:300px;height:160px;
				margin:200px auto 0;
				border-color:#E2E2E2;
				border-style:solid;
				border-width:1px;
				-moz-border-radius:4px;-webkit-border-radius:4px;border-radius:4px;
			}
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
	<body onload="setTimeout(function() {window.location.href=location.href;},5000)">
		{$_tpl['logs']}
		<div style="height:100%;">
			<div class="cform">
				{$_tpl['text']}
				<span>Через 5 секунд страница автоматический <a href="{$_SERVER['REQUEST_URI']}">перезагрузится</a></span>
			</div>
		</div>
	</body>
</html>
