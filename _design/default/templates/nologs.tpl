<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
	<title>WEP - {#BH#}</title>
	<base href="{#BH#}"/>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
	<meta http-equiv="Pragma" content="no-cache"/>
	<meta name="keywords" content="WEP"/>
	<meta name="description" content="CMS"/>
	<link rel="SHORTCUT ICON" href="/{#THEME#}img/favicon.ico"/>
	{#meta#}
	{#script#}
	{#styles#}
	<!--[if lte IE 7]>
	<style type="text/css">
		/* bug fixes for IE7 and lower - DO NOT CHANGE */
		.nav .fly {
			width: 99%;
		}

		/* make each flyout 99% of the prevous flyout */
		a:active {
		}

		/* requires a blank style for :active to stop it being buggy */
	</style>
	<![endif]-->
</head>
<body onload="{#onload#}">
<div id='wepmain'>
	<div id="adminmenu">{#adminmenu#}</div>
	<div id="modulsforms">{#text#} {#logs#}</div>
	<div class='spacer'></div>
</div>
<div id="cmsinfo">
	<div class="infname"><a href="http://xakki.ru">WebEngineOnPHP {#wep_ver#}</a></div>
	<div id="inftime">{#time#}</div>
	<div class="infc">{#contact#}</div>
</div>
<div id="debug_view" style=""></div>

<div class="debug_view_img">{#debug#}<img src="/{#THEME#}img/debug_view.png" onclick="fShowHide('debug_view');"
                                          alt="DEBUG"/></div>
</body>
</html>
