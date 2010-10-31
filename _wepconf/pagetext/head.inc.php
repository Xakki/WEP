<?
	global $CITY;
	$html ='<a class="sitename" href="/index.html">'.$PGLIST->config['sitename'].' - '.$CITY->name.'</a><div class="sitedesc">'.$PGLIST->config['description'].'</div>';

	if(!_prmUserCheck()) {
		$_tpl['logs'] .= '<div id="loginblock" style="display:none;position:absolute;z-index:30;top:50%;left:50%;">
			<div class="layerblock">
				<div class="blockclose" onclick="$(\'#loginblock\').hide();showBG(0);"></div>
				<div class="blockhead">Авторизация</div><div class="hrb" style="width:200px;">&#160;</div>
				<div class="cform"><form action="'.$_CFG['_HREF']['siteJS'].'?_view=login" method="post" onsubmit="return JSFRWin(this)">
					<div>Логин:</div><input type="text" name="login" tabindex="1"/>
					<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
					<div>Запомнить?<input type="checkbox" style="margin:0;width:30px;vertical-align:middle;" tabindex="3" name="remember" value="1"/></div>
					<div></div><input class="submit" type="submit" name="enter" value="Войти" tabindex="4"/>
				</form>
				<a href="/remind.html" style="font-size:0.8em;">Забыли пароль?</a>
				</div>
				<div class="messlogin"></div>
			</div>
		</div>';
	}
	else {
		$html .='<div class="headauth" style="float:right;">';
		$html .= $_SESSION['user']['name'];
		$html .='</div>';
	}

	$html .='<div class="headauth">';
	$DATA = array('menu'=>$PGLIST->getMap(3,1));
	$html .= $HTML->transformPHP($DATA,'menu');
	$html .='</div>';

	return $html;
?>