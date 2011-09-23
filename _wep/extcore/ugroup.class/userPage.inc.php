<?php
	_new_class('ugroup', $UGROUP);

	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = false;// - текущий	 пользователь, цыфра - уровень адреса ID пользователя
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '#ext#userinfo';

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', dirname(__FILE__));
		$temp = 'ownerlist';
		$this->_enum['levelpage'] = array(
			0=>'Из сессии пользователя',
			1=>'Из первого уровня страницы',
			2=>'Из второго уровня страницы',
			3=>'Из третъего уровня страницы');
		$form = array(
			'0'=>array('type'=>'list','listname'=>'levelpage', 'caption'=>'Как брать ID пользователя?'),
			'1'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
		);
		return $form;
	}

	$tplphp = $this->FFTemplate($FUNCPARAM[1],dirname(__FILE__));

	if($FUNCPARAM[0]) {
		$FUNCPARAM[0] = $_GET['page'][(int)substr($FUNCPARAM[0],1)-1];
	}else
		$FUNCPARAM[0] = $_SESSION['user']['id'];

	$DATA = $UGROUP->childs['users']->UserInfo($FUNCPARAM[0]);
	$DATA = array(
		$FUNCPARAM[1] =>
			array(
				'data'=>$DATA,
			)
		);
	$html = $HTML->transformPHP($DATA,$tplphp);
	//TODO : информация о пользователе

	return $html;
