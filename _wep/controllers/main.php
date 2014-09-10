<?php

if ($_NEED_INSTALL) {
	require_once($_CFG['_PATH']['wep_controllers'] . 'install/index.php');
    return true;
}

$URI = $_GET['pageParam'];

if (strpos($URI, WEP_ADMIN)===0) {
    $is_admin = true;
}
else {
    $is_admin = false;
}
//ini_set("max_execution_time", "10");
//set_time_limit(10);

if ($_CFG['site']['worktime'] and !canShowAllInfo() and !$is_admin) {
	static_main::downSite(); // Exit()
}

/**********************************************/

if (substr($URI, -5) == '.html') {
    $URI = substr($URI, 0, -5);
}

if (strpos($URI, '_')!==false) {
    if (preg_match("/(.*)_p([0-9]+)/i", $URI, $regs)) {
        $URI =  $regs[1];
        $_GET['_pn'] = $regs[2];
    }
    if (preg_match("/(.*)_([0-9]+)/i", $URI, $regs)) {
        $URI =  $regs[1];
        $_GET['id'] = $regs[2];
    }
}
if (file_exists($_CFG['_PATH']['controllers'] . 'route.php'))
    require_once($_CFG['_PATH']['controllers'] . 'route.php');

if (isset($_COOKIE['_showerror'])) {
    print_r('<pre>');
    print_r($_GET);
    print_r($_SERVER);
    print_r($_COOKIE);
    print_r($URI);
}
$_REQUEST['pageParam'] = $_GET['pageParam'] = $URI;

/**********************************************/

// вход в админку
if ($is_admin) {
    $_REQUEST['pageParam'] = $_GET['pageParam'] = substr($URI, strlen(WEP_ADMIN) );
    require_once($_CFG['_PATH']['backend'] . 'index.php');
    return true;
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
	if (is_array($_REQUEST['pageParam']))
        $_REQUEST['pageParam'] = implode('/', $_REQUEST['pageParam']);
	$_REQUEST['pageParam'] = preg_split('/\//', $_REQUEST['pageParam'], 0, PREG_SPLIT_NO_EMPTY);
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