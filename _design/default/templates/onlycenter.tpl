<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
	<head>
		<title>{#title#}</title>
		<base href="{#BH#}"/>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta name="keywords" content="{#keywords#}"/> 
		<meta name="description" content="{#description#}"/>
		{#meta#}
		<link rel="SHORTCUT ICON" href="/favicon.ico"/>
		{#styles#}
		{#tplstyles#}
		{#script#}
		{#tplscript#}
		<style>body,html {width:100%;max-width:100%;}</style>
	</head>
	<body onload="{#onload#}">
		{#logs#}
		<div class="header">
			<div class="block">
				<div class="blocktext">
					{#head#}
				</div>
			</div>
		</div>
		<div class="maintext" style="margin-left: 20px;">
			<div class="block">
				<div class="blocktext">
					{#path#}
					<div class="hrb"></div>
					{#text#}
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		
		<div class="footer">
			<div class="block">
				{#foot#}
			</div>
		</div>
		<!--{#time#}-->
	</body>
</html>
