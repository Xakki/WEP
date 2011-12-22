<?php

function tpl_loginAjax($data) {
	global $_CFG,$_tpl;
	$html = '<div id="loginblock" style="display:none;position:fixed;z-index:30;top:30%;left:30%;">
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
					'.($data['remindpage']?'<a href="'.$data['remindpage'].'">Забыли пароль?</a>':'').'
					<div class="messlogin"></div>
				</div>
			</div>
		</div>';
	$_tpl['script']['wep']=1;
	$_tpl['styles']['style']=1;
	$_tpl['styles']['login']=1;
	$_tpl['script']['showLoginForm'] = '
		function showLoginForm(id) {
		if(jQuery(\'#loginzaiframe\').size()) return true;
		showBG(0,1);
		if(!jQuery(\'div.layerblock iframe\').size())
			jQuery(\'div.layerblock div.cform\').before(\'<iframe src="http://loginza.ru/api/widget?overlay=loginza&token_url=\'+encodeURIComponent(\'http://\'+window.location.hostname+\'/login.html\')+\'&providers_set=yandex,google,rambler,mailruapi,myopenid,openid,loginza" style="width:330px;height:190px;float:left;" scrolling="no" frameborder="no"></iframe><span class="spanor"> или </span>\');
		//vkontakte,facebook,twitter,
		jQuery(\'#\'+id).show();
		jQuery(\'#\'+id+\' .layerblock\').show();
		fMessPos(0,\' #\'+id);
		jQuery.include(\'http://loginza.ru/js/widget.js\');
		return false;
	}';
	return $html;
}
						