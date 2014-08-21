<?php
/**
 * Авторизация
 * @ShowFlexForm true
 * @type Форма
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

// сначала задаем значения по умолчанию
if (!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = '#ugroup#login';
if (!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 0;
if (!isset($FUNCPARAM[2])) $FUNCPARAM[2] = 0;
if (!isset($FUNCPARAM[3])) $FUNCPARAM[3] = '';
if (!isset($FUNCPARAM[4])) $FUNCPARAM[4] = 'E-mail';

// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
	$form = array(
		'0' => array('type' => 'list', 'listname' => array('phptemplates', 'tags' => 'login'), 'caption' => 'Шаблон', 'comment' => $_CFG['lang']['tplComment']),
		'1' => array('type' => 'list', 'listname' => 'ownerlist', 'caption' => 'Страница напоминания пароля'),
		'2' => array('type' => 'list', 'listname' => 'ownerlist', 'caption' => 'Страница регистрации'),
		'3' => array('type' => 'checkbox', 'caption' => 'Авторизация по кукам?'),
		'4' => array('type' => 'text', 'caption' => 'Название поля логина'),
	);
	return $form;
}

$result = array('', 0);
$mess = array();

if ($FUNCPARAM[1])
	$FUNCPARAM[1] = $this->getHref($FUNCPARAM[1], true);
if ($FUNCPARAM[2])
	$FUNCPARAM[2] = $this->getHref($FUNCPARAM[2], true);

if (isset($_REQUEST['ref']) and $_REQUEST['ref'] != '' and mb_strpos($_SERVER['HTTP_REFERER'], $_REQUEST['ref']) === false) {
	$ref = $_REQUEST['ref'];
}
elseif (isset($_SERVER['HTTP_REFERER']) and $_SERVER['HTTP_REFERER'] != '' and mb_strpos($_SERVER['HTTP_REFERER'], $_SERVER['REQUEST_URI']) === false) {
	$ref = $_SERVER['HTTP_REFERER'];
}
else
	$ref = MY_BH;

if (count($_POST) and isset($_POST['login'])) {
	$result = static_main::userAuth($_POST['login'], $_POST['pass']);
	if ($result[1] > 0) {
		//static_main::redirect($ref);
		//$mess=$result[0];
	}
}
elseif (isset($_REQUEST['exit']) && $_REQUEST['exit'] == "ok") {
	static_main::userExit();
	$result = array(static_main::m('exitok'), 1);
}
elseif ($FUNCPARAM[3] and $result = static_main::userAuth() and $result[1] > 0) {
	static_main::redirect($ref);
	//$mess=$result[0];
}
$this->formFlag = $result[1];

$DATA = array(
	'mess' => '',
	'result' => 0,
	'ref' => $ref,
	'#title#' => $Ctitle,
	'remindpage' => $FUNCPARAM[1],
	'regpage' => $FUNCPARAM[2],
	'#page#' => 'http://' . $_SERVER['HTTP_HOST'] . '/' . $Chref . '.html',
	'#fn_login#' => $FUNCPARAM[4],
);

if (count($result) and $result[0]) {
	$mess['messages'] = array();
	$mess['messages'][0][1] = $result[0];
	if ($result[1]) {
		$mess['messages'][0][0] = 'ok';
		$DATA['result'] = 1;
		$this->pageinfo['template'] = 'waction';
		$_tpl['REQUEST_URI'] = $ref;
		$_tpl['onload'] .= '$(\'.ajaxload .blockclose\').click(function(){window.location.href=\'' . $ref . '\';}); setTimeout(function() {window.location.href=\'' . $ref . '\';}, 4000);';
	}
	else {
		$mess['messages'][0][0] = 'error';
		$DATA['result'] = -1;
	}
	$DATA['mess'] = transformPHP($mess, '#pg#messages');
}

$html = transformPHP($DATA, $FUNCPARAM[0]);

return $html;

