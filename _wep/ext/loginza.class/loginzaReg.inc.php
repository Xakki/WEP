<?php
/**
 * Авторизация LOGINZA Регистрация
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */
	if($file = $this->getIncFile('1:ugroup.class/regme') and $file)
		$importInc = include($file);

	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = 'yandex,google,rambler,mailruapi,myopenid,openid,loginza'; //openid провайдеры
	if(!isset($FUNCPARAM[4])) $FUNCPARAM[4] = 1; // - авторизация, 1 -регистрация
	if(!isset($FUNCPARAM[5])) $FUNCPARAM[5] = ''; //стиль
	// рисуем форму для админки чтобы удобно задавать параметры

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = $importInc;
		$form[3] = array('type'=>'text', 'caption'=>'Провайдеры', 'comment'=>'yandex,google,rambler,mailruapi,myopenid,openid,loginza');
		$form[4] = array('type'=>'checkbox', 'caption'=>'Регистрировать через Loginza по умолчанию?');
		$form[5] = array('type'=>'text', 'caption'=>'Cтиль');
		return $form;
	}

	$HPATH = 'http://'.$_SERVER['HTTP_HOST'].'/'.$Chref.'.html';
 
	/***OPERATION***/
	// LOGINZA 
	if(isset($_POST['token']) and $_POST['token']) {
		if(isset($_SESSION['loginza'])) unset($_SESSION['loginza']);
		_new_class('loginza',$LOGINZA);
		list($flag,$mess) =  $LOGINZA->loginzaAuth($FUNCPARAM[4]);
		if(!$flag and isset($_SESSION['loginza']) and count($_SESSION['loginza'])) {
			$mess[] = array('name'=>'alert', 'value'=>'Авторизация через данного OpenID провайдера не возможна, поскольку вы не зарегистрированы на нашем сайте. Если вы уже регистрировались, то авторизация должна соответствовать методу регистрации.');
			$mess[] = array('name'=>'ok', 'value'=>'Зарегистрировать Вас прямо сейчас?');
			$mess[] = array('name'=>'ok', 'value'=>'<a href="'.$HPATH.'?regme=yes" class="ok">ДА</a>  <a href="'.$HPATH.'" class="error">НЕТ</a>');
		}

		//$_tpl['onload'] .= 'fShowload(1,jQuery("#LoginzaMess").html(),0,0,"window.location.href=window.location.href;");';
		return '<div id="LoginzaMess">'.$HTML->transformPHP($mess,'messages').'</div>';
	}
	// LOGINZA registration
	elseif(isset($_GET['regme']) and isset($_SESSION['loginza']) and count($_SESSION['loginza'])) {
		session_go(1);
		_new_class('loginza',$LOGINZA);
		list($flag,$mess) = $LOGINZA->loginzaReg($_SESSION['loginza']);
		if($flag) {
			$mess[] = static_main::am('ok','authok',false,$LOGINZA);
			_new_class('ugroup',$UGROUP);
			$USERS = $UGROUP->childs['users'];
			$USERS->setUserSession($USERS->id);
			static_main::_prmModulLoad();
		}

		//$_tpl['onload'] .= 'fShowload(1,jQuery("#LoginzaMess").html(),0,0,"window.location.href=window.location.href;");';
		return '<div id="LoginzaMess">'.$HTML->transformPHP($mess,'messages').'</div>';
	}

	if(isset($_SESSION['loginza'])) unset($_SESSION['loginza']); // Очистка

	$html = '<div class="loginzaForm" style="'.$FUNCPARAM[5].'">
			<iframe src="http://loginza.ru/api/widget?overlay=loginza&token_url='.rawurlencode('http://'.$_SERVER['HTTP_HOST'].'/'.$Chref.'.html').'&providers_set='.$FUNCPARAM[3].'" scrolling="no" frameborder="no"></iframe>
			'.$importInc.'
		</div>';

	$_tpl['script']['loginza'] = array('http://loginza.ru/js/widget.js');

	return $html;

