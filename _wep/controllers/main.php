<?php

if ($_NEED_INSTALL) {
	require_once($_CFG['_PATH']['wep_controllers'] . 'install/index.php');
    return true;
}

if (strpos($_SERVER['REQUEST_URI'], WEP_CONFIG)===0) {
    $_GET['pageParam'] = substr($_SERVER['REQUEST_URI'], strlen(WEP_CONFIG));
	require_once($_CFG['_PATH']['backend'] . 'index.php');
    return true;
}

//ini_set("max_execution_time", "10");
//set_time_limit(10);

if ($_CFG['site']['worktime'] and !canShowAllInfo()) {
	static_main::downSite(); // Exit()
}

if (isset($_GET['_php']) and $_GET['_php'] == '_js') {
	if (file_exists($_CFG['_PATH']['controllers'] . '_js.php'))
		require_once($_CFG['_PATH']['controllers'] . '_js.php');
	else
		require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/_js.php');
    return true;
}
elseif (isset($_GET['_php']) and $_GET['_php'] == 'rss') {
	if (file_exists($_CFG['_PATH']['controllers'] . 'rss.php'))
		require_once($_CFG['_PATH']['controllers'] . 'rss.php');
	elseif (file_exists($_CFG['_PATH']['wep_controllers'] . 'frontend/rss.php'))
		require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/rss.php');
	else
		echo 'no RSS';
    return true;
}
elseif (isset($_GET['_php']) and $_GET['_php'] == 'sitemap') {
	$_COOKIE['_showerror'] = 0;
	$SITEMAP = TRUE;
	_new_class('pg', $PGLIST);
	$_tpl['text'] = $PGLIST->creatSiteMaps();
    return true;
}
elseif (strpos($_SERVER['REQUEST_URI'], '.xml')!==false) {
    $php = $_GET['_php'] = mb_substr($_SERVER['REQUEST_URI'], 0, -4);
	if (file_exists($_CFG['_PATH']['controllers'] . $_GET['_php'] . '.xml.php'))
		require_once($_CFG['_PATH']['controllers'] . $_GET['_php'] . '.xml.php');
	elseif (file_exists($_CFG['_PATH']['wep_controllers'] . 'frontend/' . $_GET['_php'] . '.xml.php'))
		require_once($_CFG['_PATH']['wep_controllers'] . 'frontend/' . $_GET['_php'] . '.xml.php');
	else
		echo 'Ашипка!';
    return true;
}
elseif (isset($_GET['_php']) and $_GET['_php'] == 'config') {
	setNeverShowAllInfo();
	//Применяется для CKFinder для авторизации по сессии
	session_go();
	return true;
}


//*****************

if (_new_class('pg', $PGLIST)) {
	if (!isset($_REQUEST['pageParam']))
		$_REQUEST['pageParam'] = "index";
	if (is_array($_REQUEST['pageParam'])) $_REQUEST['pageParam'] = implode('/', $_REQUEST['pageParam']);
	$_REQUEST['pageParam'] = explode('/', trim($_REQUEST['pageParam'], '/'));

	//if($_SESSION['_showallinfo']) {print('main1 = '.(getmicrotime()-$main1time).'<hr/>');$main2time = getmicrotime();}
	if ($PGLIST->config['auto_auth']) {
		static_main::userAuth();
	}

	$PGLIST->display();

	//if($_SESSION['_showallinfo']) print('main = '.(getmicrotime()-$main2time).'<hr/>'); // для отладки

	/*
		if(!isset($_SESSION['showIEwarning'])) $_SESSION['showIEwarning']=0;
		if(_fTestIE('MSIE 6') and $_SESSION['showIEwarning']<3) {
			$_SESSION['showIEwarning']++;
			//$_tpl['meta'] .='<!--[if IE 6]><script type="text/javascript"></script><![endif]-->';
		}
	*/
}
else
	static_main::downSite('Система ещё не установлена', 'Модуль "Страницы" не установлен или отключен.');