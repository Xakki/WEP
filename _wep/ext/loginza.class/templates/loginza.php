<?php

function tpl_loginza($data) {
	global $_CFG,$_tpl;
	/*
	<div class="blockhead">'.$data['#title#'].'</div>
		<div class="hrb">&#160;</div>
		*/
	$html = '<div class="loginza">
		<iframe src="http://loginza.ru/api/widget?overlay=loginza&token_url='.rawurlencode('http://'.$SERVER['HTTP_HOST'].'/'.$data['#page#'].'.html').'&providers_set=yandex,google,rambler,mailruapi,myopenid,openid,loginza" scrolling="no" frameborder="no"></iframe>
		<div class="cform">
			<form action="'.$_CFG['_HREF']['siteAJAX'].'?_view=login" method="post" onsubmit="return JSWin({\'type\':this})">
				<div>Логин:</div><input type="text" name="login" tabindex="1"/>
				<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
				<label style="display:block;">Запомнить?<input type="checkbox" style="margin:0;width:30px;vertical-align:middle;border:none;" tabindex="3" name="remember" value="1"/></label>
				<input class="submit" type="submit" name="enter" value="Войти" tabindex="4"/>
			</form>
			'.($data['remindpage']?'<a href="'.$data['remindpage'].'">Забыли пароль?</a>':'').'
			<div class="messlogin"></div>
		</div>
	</div>';
	$_tpl['script']['wep']=1;
	$_tpl['styles']['style']=1;
	$_tpl['styles']['login']=1;
	////vkontakte,facebook,twitter,
	return $html;
}
						