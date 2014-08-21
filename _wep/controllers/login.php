<?php
$result = array('', '');
if (!isset($mess))
	$mess = array();
if (!isset($setRedirect))
	$setRedirect = false;

// редирект
if ($setRedirect) {
	$ref = ADMIN_BH;
	if (isset($_REQUEST['ref']) and $_REQUEST['ref'] != '') {
		if (substr($_REQUEST['ref'], 0, 1) != '/' and !strstr($_REQUEST['ref'], '.'))
			$ref = base64decode($_REQUEST['ref']);
		else
			$ref = $_REQUEST['ref'];
		if (strstr($ref, 'login') or strstr($ref, 'install'))
			$ref = ADMIN_BH;
	}
	elseif (isset($_SERVER['HTTP_REFERER']) and $_SERVER['HTTP_REFERER'] != '' and !strstr($_SERVER['HTTP_REFERER'], 'login'))
		$ref = $_SERVER['HTTP_REFERER'];
	$_tpl['ref'] = $ref;
}

$messBlock = 'popMess';
if (isset($_GET['recover'])) {
	$mess[] = static_main::am('alert', 'На стадии разработки');
	$_tpl['flipped'] = 'flipped';
	$messBlock = 'popMessFlip';
}
else {
	if (count($_POST) and isset($_POST['login'])) {
		static_main::userExit();
		$result = static_main::userAuth($_POST['login'], $_POST['pass']);
		if ($result[1]) {
			if ($setRedirect)
				static_main::redirect($ref); //STOP
			else
				return $result;
		}

	}
	elseif (isset($_COOKIE['remember'])) {
		$result = static_main::userAuth();
		if ($result[1]) {
			if ($setRedirect)
				static_main::redirect($ref); //STOP
			else
				return $result;
		}
	}

	if ($result[0])
		$mess[] = static_main::am(($result[1] == 1 ? 'ok' : 'error'), $result[0]);
	//if(isset($_GET['mess']) and is_array($_GET['mess']))
	//	$mess[] = $_GET['mess']; // static_main::m()
}

if (isset($_SESSION['user']['wep']) && !$_SESSION['user']['wep'])
	$mess[] = static_main::am('error', 'Доступ в админку закрыт для Вас!');

setTemplate('login');

$_tpl['forgot'] = 'Забыли?';
$_tpl['loginLabel'] = 'Логин / Email';
$_tpl['passLabel'] = 'Пароль';
$_tpl['rememberLabel'] = 'Запомнить на 20 дней';
$_tpl['loginSubmit'] = 'Войти';

$_tpl['forgotLabel'] = 'Ваш Email';
$_tpl['forgotSubmit'] = 'Восстановить';

//$_tpl['actionLogin'] = ADMIN_BH.'login'.(isset($_GET['install'])?'?install':'');
//$_tpl['actionRecover'] = ADMIN_BH.'login?recover=true';
$_tpl['actionLogin'] = $_SERVER['REQUEST_URI'];
$_tpl['actionRecover'] = $_SERVER['REQUEST_URI'] . '?recover=true';

if (count($mess)) {
	$_tpl[$messBlock] = transformPHP($mess, 'messages');

}
return false;