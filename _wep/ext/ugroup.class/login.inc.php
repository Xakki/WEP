<?php
/**
 * Авторизация
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = '#ugroup#login';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '';
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = 0;
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = '';

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			'1'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница напоминания пароля'),
			'2'=>array('type'=>'checkbox', 'caption'=>'Авторизация по кукам?'),
			'3'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница регистрации'),
		);
		return $form;
	}


	if($FUNCPARAM[1])
		$FUNCPARAM[1] = $this->getHref($FUNCPARAM[1],true);
	if($FUNCPARAM[3])
		$FUNCPARAM[3] = $this->getHref($FUNCPARAM[3],true);

	$result = array();
	if(isset($_REQUEST['ref']) and $_REQUEST['ref']!='' and mb_strpos($_SERVER['HTTP_REFERER'],$_REQUEST['ref'])===false) {
		$ref= $_REQUEST['ref'];
	}
	elseif(isset($_SERVER['HTTP_REFERER']) and $_SERVER['HTTP_REFERER']!='' and mb_strpos($_SERVER['HTTP_REFERER'],$_SERVER['REQUEST_URI'])===false) {
		$ref= $_SERVER['HTTP_REFERER'];
	}
	else 
		$ref= $_CFG['_HREF']['BH'];
	
	$mess = array();

	if(count($_POST) and isset($_POST['login'])) {
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
	elseif($FUNCPARAM[2] and $result = static_main::userAuth() and $result[1]) {
		static_main::redirect($ref);
		//$mess=$result[0];
	}

	$DATA = array(
		'mess'=>'',
		'result'=>0,
		'ref'=>$ref,
		'#title#'=>$Ctitle,
		'remindpage'=>$FUNCPARAM[1],
		'regpage'=>$FUNCPARAM[3],
		'#page#'=>$Chref,
	);

	if(count($result)) {
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

