<?php
/**
 * Смена пользователя [для администраторов]
 * @ShowFlexForm true
 * @type Служебные
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = '#ugroup#userChange';
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0'=>array('type'=>'list', 'listname'=>array('phptemplates', 'tags'=>'userswap'), 'caption'=>'Шаблон Списка', 'comment'=>$_CFG['lang']['tplComment']),
		);
		return $form;
	}
	
	$html = 'Доступ только администраторам';

	if(isset($_SESSION['superuser']) or $_SESSION['user']['level']==0) {
		_new_class('ugroup', $UGROUP);

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
			$DATA = $UGROUP->childs['users']->_query('*','WHERE active=1 and id!='.$noid.' and pass!="" ORDER BY owner_id, name','id');
			$DATA2 = $UGROUP->_query('*','WHERE active=1 and level<10','id');
			$DATA = array($FUNCPARAM[0]=>array('list'=>$DATA,'owner'=>$DATA2,'href'=>$this->getHref(),'userpic'=>$UGROUP->config['userpic']));
			$html .= transformPHP($DATA,$FUNCPARAM[0]);
		}
	}

	return $html;
