<?php
/**HELP
$FUNCPARAM[0] - 0 - авторизация, 1 -регистрация
$FUNCPARAM[1] - openid провайдеры [yandex,google,rambler,mailruapi,myopenid,openid,loginza]
$FUNCPARAM[2] - страница редиректа для логинзы
$FUNCPARAM[3] - шаблон
$FUNCPARAM[4] - стиль
HELP**/
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = 0;
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 'yandex,google,rambler,mailruapi,myopenid,openid,loginza';
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = '';
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] =  '#ext#loginza';
	if(!isset($FUNCPARAM[4])) $FUNCPARAM[4] = '';

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', dirname(__FILE__));
		$temp = 'ownerlist';
		$this->_enum['levelmenuinc'] = $this->_getCashedList($temp);

		$form = array(
			'0'=>array('type'=>'checkbox', 'caption'=>'Регистрировать по умолчанию?'),
			'1'=>array('type'=>'text', 'caption'=>'Провайдеры'),
			'2'=>array('type'=>'text', 'caption'=>'Token URL','comment'=>'Оставьте пустым, для текущей страницы'),
			'3'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			'4'=>array('type'=>'text', 'caption'=>'Cтиль'),
		);
		return $form;
	}
	$FUNCPARAM[2] = $this->getHref($FUNCPARAM[2],true);
	$tplphp = $this->FFTemplate($FUNCPARAM[3],dirname(__FILE__));


	if(isset($_POST['token']) and $_POST['token']) {
		if(isset($_SESSION['loginza'])) unset($_SESSION['loginza']);
		_new_class('loginza',$LOGINZA);
		list($flag,$mess) =  $LOGINZA->loginzaAuth($FUNCPARAM[0]);
		if(!$flag and isset($_SESSION['loginza']) and count($_SESSION['loginza'])) {
			$mess[] = array('name'=>'alert', 'value'=>'Авторизация через данного OpenID провайдера не возможна, поскольку вы не зарегистрированы на нашем сайте. Если вы уже регистрировались, то авторизация должна соответствовать методу регистрации.');
			$mess[] = array('name'=>'ok', 'value'=>'Зарегистрировать Вас прямо сейчас?');
			$mess[] = array('name'=>'ok', 'value'=>'<a href="/'.$FUNCPARAM[2].'?regme=yes" class="ok">ДА</a>  <a href="/'.$FUNCPARAM[2].'" class="error">НЕТ</a>');
		}
		$mess = array('messages'=>$mess);
		global $_tpl;
		$_tpl['onload'] .= 'fShowload(1,jQuery("#LoginzaMess").html(),0,0,"window.location.href=window.location.href;");';
		return '<div id="LoginzaMess">'.$HTML->transformPHP($mess,'messages').'</div>';
	}
	elseif(isset($_GET['regme'])) {
		session_go(1);
		if(isset($_SESSION['loginza']) and count($_SESSION['loginza'])) {
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
	}
	if(isset($_SESSION['loginza'])) unset($_SESSION['loginza']);
	$_tpl['script']['loginza'] = array('http://loginza.ru/js/widget.js');
	$DATA = array(
		'src'=>'token_url='.urlencode($_CFG['_HREF']['BH'].$FUNCPARAM[2]).'&providers_set='.$FUNCPARAM[1].'',
		'style'=>$FUNCPARAM[4]);
	$DATA = array($FUNCPARAM[3]=>$DATA);
	$html = $HTML->transformPHP($DATA,$tplphp);
	return $html;

