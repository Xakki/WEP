<?php

	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = '#ext#userChange';

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', dirname(__FILE__));
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон Списка'),
		);
		return $form;
	}
	
	$html = 'Доступ только администраторам';

	if(isset($_SESSION['superuser']) or $_SESSION['user']['level']==0) {
		_new_class('ugroup', $UGROUP);

		$tplphp = $this->FFTemplate($FUNCPARAM[0],dirname(__FILE__));
		$tplphp1 = $this->FFTemplate($FUNCPARAM[1],dirname(__FILE__));

		$html = '';
		if(isset($this->pageParam[0])) {
			$this->pageinfo['path'][''] = 'Переключение на пользователя ';
			$id = (int)$this->pageParam[0];
			if($id==0 and isset($_SESSION['superuser'])) {
				if($UGROUP->childs['users']->setUserSession($_SESSION['superuser'])) {
					$html .= 'Вы успешно переключились на пользователя '.$_SESSION['user']['name'];
				} else
					$html .= 'Ошибка. Выбранный пользователь не существует.';
				unset($_SESSION['superuser']);
			} else {
				if(!isset($_SESSION['superuser']))
					$_SESSION['superuser'] = $_SESSION['user']['id'];
				if($UGROUP->childs['users']->setUserSession($id)) {
					$html .= 'Вы успешно переключились на пользователя '.$_SESSION['user']['name'];
				} else
					$html .= 'Ошибка. Выбранный пользователь не существует.';
			}
		} else {
			if(isset($_SESSION['superuser'])) {
				$html .= '<h3><a href="'.$this->getHref().'/0.html">Переключиться на свои аккаунт</a></h3>';
				$noid = $_SESSION['superuser']['id'];
			} else
				$noid = $_SESSION['user']['id'];
			$DATA = $UGROUP->childs['users']->_query('*','WHERE active=1 and id!='.$noid.' ORDER BY name','id');
			$DATA2 = $UGROUP->_query('*','WHERE active=1 ORDER BY name','id');
			$DATA = array($FUNCPARAM[0]=>array('list'=>$DATA,'owner'=>$DATA2,'href'=>$this->getHref(),'userpic'=>$UGROUP->config['userpic']));
			$html .= $HTML->transformPHP($DATA,$tplphp);
		}
	}

	return $html;