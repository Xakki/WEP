<?php
	global $_tpl;
	_new_class('city',$CITY);
	//$this->config['sitename'] .= ' - '.$CITY->name;
	if($CITY->id) {
		if($CITY->parent_id or $CITY->center)
			$rtext = ' в городе '.$CITY->name;
		else
			$rtext = ' в '.$CITY->name;
	}
	else
		$rtext = ' по всей России';
	$this->config['description'] = str_replace('#1#',$rtext,$this->config['description']);
	$this->pageinfo['description'] = str_replace('#1#',$rtext,$this->pageinfo['description']);
	//$html ='<a class="sitename" href="/index.html"><h1>'.$this->config['sitename'].' - '.$CITY->name.'</h1></a>
	//<h2 class="sitedesc">'.$this->config['description'].'</h2>';
	$html ='<a class="sitename" href="/index.html">Бесплатная доска объявлений '.$rtext.'</a><h2 class="sitedesc">'.$this->config['description'].'</h2>';
	/*if(!static_main::_prmUserCheck()) {
		if($this->id!='login')
		$_tpl['logs'] .= '<div id="loginblock" style="display:none;position:absolute;z-index:30;top:50%;left:50%;">
			<div class="layerblock">
				<div class="blockclose" onclick="$(\'#loginblock\').hide();showBG(0);">&#160;</div>
				<div class="blockhead">Авторизация</div><div class="hrb">&#160;</div>
				<div class="cform">
					<form action="'.$_CFG['_HREF']['siteJS'].'?_view=login" method="post" onsubmit="return JSWin({\'type\':this})">
						<div>Логин:</div><input type="text" name="login" tabindex="1"/>
						<div>Пароль:</div><input type="password" name="pass" tabindex="2"/>
						<div>Запомнить?<input type="checkbox" style="margin:0;width:30px;vertical-align:middle;border:none;" tabindex="3" name="remember" value="1"/></div>
						<input class="submit" type="submit" name="enter" value="Войти" tabindex="4"/>
					</form>
					<a href="/remind.html" style="font-size:0.8em;">Забыли пароль?</a>
					<div class="messlogin"></div>
				</div>
			</div>
		</div>';
		$this->pageinfo['styles']['login'] = 1;
	}*/
	if(static_main::_prmUserCheck()) {
		$html .='<div class="headauthname">';
		$html .= '<a href="/profile.html">'.$_SESSION['user']['name'].'</a>';
		$html .='</div>';
	}

	$html .='<div class="headauth">';
	$DATA = array('#item#'=>$PGLIST->getMap(3,1));
	$DATA = array('menu'=>$DATA);
	$html .= $HTML->transformPHP($DATA,'menu');
	if($CITY->id==213)
		$html .= '<a href="/_redirect.php?url='.base64_encode('http://www.ufa.ru/board.html').'" target="_blank" style="color:Crimson;">Доска объявлений Уфы</a>';
	$html .= '</div>';

	return $html;
