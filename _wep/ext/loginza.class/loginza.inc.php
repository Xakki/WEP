<?php
/**
 * Авторизация LOGINZA (NEW)
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = '#loginza#loginza';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '';
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = '';
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = 1;
	if(!isset($FUNCPARAM[4])) $FUNCPARAM[4] = 'E-mail';
	if(!isset($FUNCPARAM[5])) $FUNCPARAM[5] = 'yandex,google,rambler,mailruapi,myopenid,openid,loginza'; //openid провайдеры
	if(!isset($FUNCPARAM[6])) $FUNCPARAM[6] = 1; // - авторизация, 1 -регистрация
	if(!isset($FUNCPARAM[7])) $FUNCPARAM[7] = ''; //стиль
	// рисуем форму для админки чтобы удобно задавать параметры

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			'1'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница напоминания пароля'),
			'2'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница регистрации'),
			'3'=>array('type'=>'checkbox', 'caption'=>'Авторизация по кукам?'),
			'4'=>array('type'=>'text', 'caption'=>'Название поля логина'),
			'5'=>array('type'=>'text', 'caption'=>'Провайдеры', 'comment'=>'yandex,google,rambler,mailruapi,myopenid,openid,loginza'),
			'6'=>array('type'=>'checkbox', 'caption'=>'Регистрировать через Loginza по умолчанию?'),
			'7'=>array('type'=>'text', 'caption'=>'Cтиль'),
		);
		return $form;
	}

	/***SET data property***/
	$result = array();
	$mess = array();
	if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='' and mb_strpos($_SERVER['HTTP_REFERER'],$_REQUEST['ref'])===false) {
		$ref= $_REQUEST['ref'];
	}
	elseif(isset($_SERVER['HTTP_REFERER']) and $_SERVER['HTTP_REFERER']!='' and mb_strpos($_SERVER['HTTP_REFERER'],$_SERVER['REQUEST_URI'])===false) {
		$ref= $_SERVER['HTTP_REFERER'];
	}
	else 
		$ref= $_CFG['_HREF']['BH'];
	
	if($FUNCPARAM[1])
		$FUNCPARAM[1] = $this->getHref($FUNCPARAM[1],true);
	if($FUNCPARAM[2])
		$FUNCPARAM[2] = $this->getHref($FUNCPARAM[2],true);

	$HPATH = 'http://'.$_SERVER['HTTP_HOST'].'/'.$Chref.'.html';
 

	/***OPERATION***/
	// LOGINZA 
	if(isset($_POST['token']) and $_POST['token']) {
		if(isset($_SESSION['loginza'])) unset($_SESSION['loginza']);
		_new_class('loginza',$LOGINZA);
		list($flag,$mess) =  $LOGINZA->loginzaAuth($FUNCPARAM[0]);
		if(!$flag and isset($_SESSION['loginza']) and count($_SESSION['loginza'])) {
			$mess[] = array('name'=>'alert', 'value'=>'Авторизация через данного OpenID провайдера не возможна, поскольку вы не зарегистрированы на нашем сайте. Если вы уже регистрировались, то авторизация должна соответствовать методу регистрации.');
			$mess[] = array('name'=>'ok', 'value'=>'Зарегистрировать Вас прямо сейчас?');
			$mess[] = array('name'=>'ok', 'value'=>'<a href="'.$HPATH.'?regme=yes" class="ok">ДА</a>  <a href="'.$HPATH.'" class="error">НЕТ</a>');
		}
		$mess = array('messages'=>$mess);
		global $_tpl;
		$_tpl['onload'] .= 'fShowload(1,jQuery("#LoginzaMess").html(),0,0,"window.location.href=window.location.href;");';
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
		$mess = array('messages'=>$mess);
		unset($_SESSION['loginza']);
		global $_tpl;
		$_tpl['onload'] .= 'fShowload(1,jQuery("#LoginzaMess").html(),0,0,"window.location.href=window.location.href;");';
		return '<div id="LoginzaMess">'.$HTML->transformPHP($mess,'messages').'</div>';
	}
	elseif(count($_POST) and isset($_POST['login'])) {
		$result = static_main::userAuth($_POST['login'],$_POST['pass']);
		if($result[1]) {
			//static_main::redirect($ref);
			//$mess=$result[0];
		}
	}
	elseif(isset($_REQUEST['exit']) && $_REQUEST['exit']=="ok") {
		static_main::userExit();
		$result = array(static_main::m('exitok'),1);
	}
	elseif($FUNCPARAM[3] and $result = static_main::userAuth() and $result[1]) {
		static_main::redirect($ref);
		//$mess=$result[0];
	}

	if(isset($_SESSION['loginza'])) unset($_SESSION['loginza']); // Очистка

	$DATA = array(
		'mess'=>'',
		'result'=>0,
		'ref'=>$ref,
		'remindpage'=>$FUNCPARAM[1],
		'regpage'=>$FUNCPARAM[2],
		'#providers_set#'=>$FUNCPARAM[5],
		'#page#'=>'http://'.$_SERVER['HTTP_HOST'].'/'.$Chref.'.html',
		'#fn_login#' => $FUNCPARAM[4],
	);

	if(count($result) and $result[0]) {
		$mess['messages'][0][1] = $result[0];
		if($result[1]) {
			$mess['messages'][0][0] = 'ok';
			$DATA['result'] = 1;
			$this->pageinfo['template'] = 'waction';
		}
		else {
			$mess['messages'][0][0] = 'error';
			$DATA['result'] = -1;
		}
		$DATA['mess'] = $HTML->transformPHP($mess,'#pg#messages');
	}

	$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);

	return $html;

