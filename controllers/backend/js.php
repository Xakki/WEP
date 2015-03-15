<?php

$DATA = array();

if ($_CFG['robot']) {
    $_tpl['text'] = static_main::m('deniedrobot');
    exit(static_main::m('deniedrobot'));
} elseif (!isset($_COOKIE[$_CFG['session']['name']])) {
    $_tpl['text'] = static_main::m('denieda');
    exit(static_main::m('denieda'));
}

$result = static_main::userAuth(); // запскает сессию и проверяет авторизацию
if (!$result[1]) {
    //header('login?ref='.base64encode($_SERVER['REQUEST_URI']));
    $_tpl['text'] = 'Вы не авторизованы , либо доступ закрыт.';
    exit($_tpl['text']);
}

if (isset($_COOKIE['cdesign']) and $_COOKIE['cdesign'])
    $_design = $_COOKIE['cdesign'];
elseif ($_SESSION['user']['design'])
    $_design = $_SESSION['user']['design'];
else
    $_design = $_CFG['wep']['design'];


if ($_CFG['wep']['access'] and (!isset($_SESSION['user']['id']) or $_SESSION['user']['level'] >= 5)) {
    $_tpl['text'] = static_main::m('denied');
    exit(static_main::m('denied'));
    //$_tpl['onload']='window.location="login?mess=Недостаточно прав доступа."';
} elseif (!$_GET['_modul']) { // or !$_SESSION['user']['wep']
    $_tpl['text'] = static_main::m('errdata');
    exit(static_main::m('errdata'));
    //$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Параметры заданны неверно!</div>\',1);fSwin1();';
}

if (!_new_class($_GET['_modul'], $MODUL))
    exit(' Модуль ' . $_GET['_modul'] . ' не установлен');
//$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Модуль '.$_GET['_modul'].' не установлен</div>\',1);fSwin1();';

if (!static_main::_prmModul($_GET['_modul'], array(1, 2))) // Проверка доступа к модулю
    exit('Доступ к модулю ' . $_GET['_modul'] . ' запрещён администратором');
//$_tpl['onload']='fLog(\'<div style="color:red;">'.date('H:i:s').' : Доступ к модулю '.$_GET['_modul'].' запрещён администратором</div>\',1);fSwin1();';


if (isset($_GET['_oid']) and $_GET['_oid'] != '') $MODUL->owner_id = $_GET['_oid'];
if (isset($_GET['_pid']) and $_GET['_pid'] != '') $MODUL->parent_id = $_GET['_pid'];
if (isset($_GET['_id']) and $_GET['_id'] != '') $MODUL->id = $_GET['_id'];


include($_CFG['_PATH']['cdesign'] . $_design . '/inc/js.php');
