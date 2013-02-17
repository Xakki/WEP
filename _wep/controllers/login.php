<?php
	$result = array('','');
	$mess = array();

	$ref= $_CFG['PATH']['admin'];
	if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='') {
		if(substr($_REQUEST['ref'],0,1)!='/' and !strstr($_REQUEST['ref'],'.'))
			$ref = base64decode($_REQUEST['ref']);
		else
			$ref = $_REQUEST['ref'];
		if(strstr($ref,'login') or strstr($ref,'install'))
			$ref = $_CFG['PATH']['admin'];
	}
	elseif(isset($_SERVER['HTTP_REFERER']) and $_SERVER['HTTP_REFERER']!='' and !strstr($_SERVER['HTTP_REFERER'],'login'))
		$ref= $_SERVER['HTTP_REFERER'];

	$messBlock = 'popMess';
	if(isset($_GET['recover']))
	{
		$mess[] = array('alert', 'На стадии разработки');
		$_tpl['flipped'] = 'flipped';
		$messBlock = 'popMessFlip';
	}
	else
	{
		if(count($_POST) and isset($_POST['login'])) {
			static_main::userExit();
			$result = static_main::userAuth($_POST['login'],$_POST['pass']);
			if($result[1]) 
			{
				static_main::redirect($ref);//STOP
			}
			$mess[] = array('error', $result[0]);
		}
		elseif(isset($_COOKIE['remember']) and $result = static_main::userAuth() and $result[1]) {
			static_main::redirect($ref);//STOP
		}
		if(isset($_GET['mess']))
			$mess[] = $_GET['mess']; // static_main::m()
	}

	setTemplate('login');

	$_tpl['forgot'] = 'Забыли?';
	$_tpl['loginLabel'] = 'Логин / Email';
	$_tpl['passLabel'] = 'Пароль';
	$_tpl['rememberLabel'] = 'Запомнить на 20 дней';
	$_tpl['loginSubmit'] = 'Войти';

	$_tpl['forgotLabel'] = 'Ваш Email';
	$_tpl['forgotSubmit'] = 'Восстановить';

	$_tpl['ref'] = $ref;
	//$_tpl['actionLogin'] = $_CFG['PATH']['admin'].'login'.(isset($_GET['install'])?'?install':'');
	//$_tpl['actionRecover'] = $_CFG['PATH']['admin'].'login?recover=true';
	$_tpl['actionLogin'] = $_SERVER['REQUEST_URI'];
	$_tpl['actionRecover'] = $_SERVER['REQUEST_URI'].'?recover=true';

	if(count($mess))
	{
		$_tpl[$messBlock] = transformPHP($mess,'messages');
	}
	