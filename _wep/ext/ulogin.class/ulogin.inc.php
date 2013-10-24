<?php
/**
 * Login + ULOGIN
 * Авторизация + сервис ULOGIN
 * @ShowFlexForm true
 * @type Форма
 * @ico login.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

if ($file = $this->getIncFile('1:ugroup.class/login') and $file)
    $importInc = include($file);

// сначала задаем значения по умолчанию
if (!isset($FUNCPARAM[5])) $FUNCPARAM[5] = 'yandex,google,rambler,mailruapi,myopenid,openid,ulogin'; //openid провайдеры
if (!isset($FUNCPARAM[6])) $FUNCPARAM[6] = 0; // - авторизация, или ID группы регистрации
if (!isset($FUNCPARAM[7])) $FUNCPARAM[7] = ''; //стиль
// рисуем форму для админки чтобы удобно задавать параметры

if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
    $form = $importInc;
    $form['5'] = array('type' => 'text', 'caption' => 'Провайдеры', 'comment' => 'yandex,google,rambler,mailruapi,myopenid,openid,ulogin');
    $form['6'] = array('type' => 'list', 'listname' => array('class' => 'ugroup', 'zeroname' => 'Откл. регистрацию'), 'caption' => 'Регистрировать в указанную группу');
    //$form['6'] = array('type'=>'checkbox', 'caption'=>'Регистрировать через Loginza по умолчанию?');
    $form['7'] = array('type' => 'text', 'caption' => 'Cтиль');

    return $form;
}

$HPATH = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $Chref . '.html';

/***OPERATION***/
// ULOGIN
if (isset($_POST['token']) and $_POST['token']) {
    if (isset($_SESSION['ulogin'])) unset($_SESSION['ulogin']);
    _new_class('ulogin', $ULOGIN);
    list($flag, $mess) = $ULOGIN->uloginAuth($FUNCPARAM[6]);
    if (!$flag and isset($_SESSION['ulogin']) and count($_SESSION['ulogin'])) {
        $mess[] = array('name' => 'alert', 'value' => 'Авторизация через данного OpenID провайдера не возможна, поскольку вы не зарегистрированы на нашем сайте. Если вы уже регистрировались, то авторизация должна соответствовать методу регистрации.');
        $mess[] = array('name' => 'ok', 'value' => 'Зарегистрировать Вас прямо сейчас?');
        $mess[] = array('name' => 'ok', 'value' => '<a href="' . $HPATH . '?regme=yes" class="ok">ДА</a>  <a href="' . $HPATH . '" class="error">НЕТ</a>');
    }

    //$_tpl['onload'] .= 'fShowload(1,jQuery("#LoginzaMess").html(),0,0,"window.location.href=window.location.href;");';
    return '<div id="LoginzaMess">' . transformPHP($mess, '#pg#messages') . '</div>';
} // ULOGIN registration
elseif (isset($_GET['regme']) and isset($_SESSION['ulogin']) and count($_SESSION['ulogin'])) {
    session_go(1);
    _new_class('ulogin', $ULOGIN);
    list($flag, $mess) = $ULOGIN->uloginReg($_SESSION['ulogin']);
    if ($flag) {
        $mess[] = static_main::am('ok', 'authok', false, $ULOGIN);
        _new_class('ugroup', $UGROUP);
        $USERS = $UGROUP->childs['users'];
        $USERS->setUserSession($USERS->id);
        static_main::_prmModulLoad();
    }

    //$_tpl['onload'] .= 'fShowload(1,jQuery("#LoginzaMess").html(),0,0,"window.location.href=window.location.href;");';
    return '<div id="LoginzaMess">' . transformPHP($mess, '#pg#messages') . '</div>';
}

if (isset($_SESSION['ulogin'])) unset($_SESSION['ulogin']); // Очистка

if ($this->pageinfo['template'] != 'waction') { // Не выводим хтмл , если едет успешная авторизация
    $html = '<div class="uloginForm" style="' . $FUNCPARAM[7] . '">
			<div class="uloginIframe">
				<div class="uloginInfo">Вы можете авторизоваться с помощью следующих сервисов</div>
				<iframe style="height:220px;" src="http://ulogin.ru/api/widget?overlay=ulogin&token_url=' . rawurlencode($_SERVER['HTTP_PROTO'] . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI']) . '&providers_set=' . $FUNCPARAM[5] . '" scrolling="auto" frameborder="no"></iframe>
			</div>
			' . $importInc . '
		</div>';

    setScript('http://ulogin.ru/js/widget.js');
}

return $html;

