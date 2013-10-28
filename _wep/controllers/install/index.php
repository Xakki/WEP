<?php


$TEMP_CFG = array();
$TEMP_CFG['wep']['access'] = $_CFG['wep']['access'] = 0; // авторизация только по главному паролю
$TEMP_CFG['wep']['sessiontype'] = $_CFG['wep']['sessiontype'] = 0; // запускаем сессию стандартно
$TEMP_CFG['site']['bug_hunter'] = $_CFG['site']['bug_hunter'] = array(); // откл запись в баг
$TEMP_CFG['sql']['log'] = $_CFG['sql']['log'] = 0;
$TEMP_CFG['wep']['debugmode'] = $_CFG['wep']['debugmode'] = 2;
error_reporting(-1);
header('HTTP/1.1 503 Service Unavailable');
isBackend(true);

session_go();

$_tpl['title'] = 'Установка WEP';

if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] === 0) {

	if (!isset($_SESSION['step']))
		$_SESSION['step'] = 1;

	setTemplate('install');

	$stp = array(
		1 => array('name' => 'Шаг первый', 'css' => '', 'comment' => 'Подключение к БД и настройка дополнительных параметров'),
		2 => array('name' => 'Шаг второй', 'css' => '', 'comment' => 'Проверка структуры сайта'),
		3 => array('name' => 'Шаг третий', 'css' => '', 'comment' => 'Установка модулей и удаление.'),
		999 => array('name' => 'Завершение', 'css' => '', 'comment' => '')
	);
	if (!isset($_GET['step']))
		$_GET['step'] = 1;
	else
		$_GET['step'] = (int)$_GET['step'];

	$file = $_CFG['_PATH']['wep_controllers'] . '/install/step' . $_GET['step'] . '.php';
	if (file_exists($file)) {
		$var_const = array(
			'mess' => array('name' => 'ok', 'value' => 'Пора перейти к <a href="' . ADMIN_BH . '/install/?step=' . ($_GET['step'] + 1) . '">следующему шагу №' . ($_GET['step'] + 1) . '</a>'),
			'sbmt' => 'Сохранить и перейти на следующий шаг'
		);
		if ($_SESSION['step'] < $_GET['step'])
			$_tpl['text'] = 'Как ты попал сюда? Вернитесь на <a href="' . ADMIN_BH . '/install/?step=' . $_SESSION['step'] . '">Шаг №' . $_SESSION['step'] . '</a>.';
		else
			$_tpl['text'] = require($file);
	} elseif ($_SESSION['step'] > 3 and $_GET['step'] == $_SESSION['step']) {
		$_tpl['text'] = '<h2>Установка завершена</h2><br/>
			<a href="/index.html">Перейти на сайт</a><br/>
			<a href="' . ADMIN_BH . '/login">Перейти в админку</a>';
	} else {
		$_tpl['text'] = '<h2>Ошибка.</h2><br/>
			<a href="' . ADMIN_BH . '/install">Перейти на начало установки</a><br/>
			<a href="' . ADMIN_BH . '/login">Перейти в админку</a>';
	}

	$_tpl['step'] = '';
	if (isset($stp[$_GET['step']]))
		$stp[$_GET['step']]['css'] = ' selstep';
	foreach ($stp as $k => $r) {
		$_tpl['step'] .= '<a ';
		if ($k <= $_GET['step'])
			$_tpl['step'] .= ' href="' . ADMIN_BH . '/install/?step=' . $k . '"';
		$_tpl['step'] .= 'class="stepitem' . $r['css'] . '"><div class="name">' . $r['name'] . '</div></a>';
	}
	if (isset($stp[$_GET['step']]['comment']))
		$_tpl['step'] .= '<div class="stepcomment">' . $stp[$_GET['step']]['comment'] . '</div>';
	$_tpl['onload'] = '';
	/* 	$_tpl['ref'] = $ref;
	  $_tpl['action'] = ADMIN_BH.'/login'.(isset($_GET['install'])?'?install':'');
	  if($result[0]) $result[0] = '<div style="color:red;">'.$result[0].'</div>';
	  elseif(isset($_GET['install'])) $result[0] = '<div style="color:red;">Установка недостающих данных</div>';
	  $_tpl['mess'] = '<div class="messhead">'.$result[0].'</div>'; */
} else {
	$_REQUEST['ref'] = $_SERVER['REQUEST_URI'];

	if ($_NEED_INSTALL)
		$_GET['mess'] = array('alert', 'Фаил конфигурации сайта не обнаружен! Если хотите запустить сайт, необходимо авторизоваться с дефолтным логином и паролем и пройдти процедуру установки сайта.');
	else
		$_GET['mess'] = array('notice', 'Введите ROOT логин и пароль для запуска установки.');

	include($_CFG['_PATH']['wep_controllers'] . 'login.php');
	exit();
}