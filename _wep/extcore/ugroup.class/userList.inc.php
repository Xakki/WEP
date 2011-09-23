<?php

	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = '#ext#userlist';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '#ext#userinfo';

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', dirname(__FILE__));
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон Списка'),
			'1'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон Инфы о пользователе'),
		);
		return $form;
	}

	_new_class('ugroup', $UGROUP);

	$tplphp = $this->FFTemplate($FUNCPARAM[0],dirname(__FILE__));
	$tplphp1 = $this->FFTemplate($FUNCPARAM[1],dirname(__FILE__));

	$html = '';
	if(isset($this->pageParam[1])) {
		$DATA = $UGROUP->childs['users']->_query('t2.*,t1.*',' t1 LEFT JOIN '.$UGROUP->childs['users']->childs['extuserscontact']->tablename.' t2 ON t1.id=t2.owner_id WHERE t1.active=1 and t1.id='.(int)$this->pageParam[1]);
		$this->pageinfo['path'][''] = $DATA[0]['name'];
		$DATA = array($FUNCPARAM[1]=>array('data'=>$DATA[0],'href'=>$this->getHref()));
		$html = $HTML->transformPHP($DATA,$tplphp1);
	} else {
		$DATA = $UGROUP->childs['users']->_query('*','WHERE active=1 ORDER BY name');
		$DATA = array($FUNCPARAM[0]=>array('list'=>$DATA,'href'=>$this->getHref(),'userpic'=>$UGROUP->config['userpic']));
		$html = $HTML->transformPHP($DATA,$tplphp);
	}
	//TODO : список пользователей

	return $html;
