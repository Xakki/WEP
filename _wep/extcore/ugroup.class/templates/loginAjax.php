<?php

function tpl_loginAjax($data) {
	global $_CFG;
	$html = '<div id="loginblock" style="display:none;position:absolute;z-index:30;top:50%;left:50%;">
			<div class="layerblock">
				<div class="blockclose" onclick="$(\'#loginblock\').hide();showBG(0);">&#160;</div>
				<div class="blockhead">'.$data['#title#'].'</div><div class="hrb">&#160;</div>
				<div class="cform">
					<form action="'.$_CFG['_HREF']['siteJS'].'?_view=login" method="post" onsubmit="return JSWin({\'type\':this})">
						<div>Логин:</div><input type="text" name="login" tabindex="1"/>
						<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
						<div>Запомнить?<input type="checkbox" style="margin:0;width:30px;vertical-align:middle;border:none;" tabindex="3" name="remember" value="1"/></div>
						<input class="submit" type="submit" name="enter" value="Войти" tabindex="4"/>
					</form>
					'.($data['remindpage']?'<a href="'.$data['remindpage'].'" style="font-size:0.8em;">Забыли пароль?</a>':'').'
					<div class="messlogin"></div>
				</div>
				
			</div>
		</div>';
	return $html;
}
						