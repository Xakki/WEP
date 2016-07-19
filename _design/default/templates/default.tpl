<!DOCTYPE html>
<html>
<head>
    {#meta#}
    {#styles#}
    {#script#}
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
    <div id="modulsforms">{#text#}</div>
    <div class='spacer'></div>
</div>
<div id="cmsinfo">
    <div class="infname">
        <a href="http://xakki.ru/portfolio/wep.html">WebEngineOnPHP {#wep_ver#}</a>
    </div>
    <div id="inftime">{#time#}</div>
    <div class="infc">{#contact#}</div>
</div>

{#logs#}

<div class="PopUp">
    <div class="PopUpBackgound CloseTarget"></div>
    <div class="PopUpLoading">Загружаю...</div>
    <div class="PopUpContent"></div>
</div>
</body>
</html>
