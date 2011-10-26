<?php
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '#ext#loginAjax';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '';
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = '';
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = 0;

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', dirname(__FILE__));
		$temp = 'ownerlist';
		$this->_enum['levelmenuinc'] = $this->_getCashedList($temp);
		$this->_enum['levelmenuinc'][0] = array_merge(array(
			''=>'---',
			'#0'=>'# первый уровнь адреса',
			'#1'=>'# второй уровнь адреса',
			'#2'=>'# третий уровнь адреса',
			'#3'=>'# четвертый уровнь адреса',
			'#4'=>'# пятый уровнь адреса'),
			$this->_enum['levelmenuinc'][0]);
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			'1'=>array('type'=>'list','listname'=>'levelmenuinc', 'caption'=>'Страница "Напомнить пароль"'),
			'2'=>array('type'=>'list','listname'=>'levelmenuinc', 'caption'=>'Страница "Авторизация"'),
			'3'=>array('type'=>'checkbox', 'caption'=>'Авторизация по кукам?'),
		);

		return $form;
	}

	$tplphp = $this->FFTemplate($FUNCPARAM[0],dirname(__FILE__));

	if(isset($FUNCPARAM[2]) and $FUNCPARAM[2]) { // страница исключение
		if($this->id==$FUNCPARAM[2]) return '';
		if(!$this->dataCash[$FUNCPARAM[2]]['attr'])
			$this->dataCash[$FUNCPARAM[2]]['attr'] = 'onclick="return showLoginForm(\'loginblock\')" class="ajaxlink"';
	}

	if($FUNCPARAM[1])
		$FUNCPARAM[1] = $this->getHref($FUNCPARAM[1],true);

	$result = array();
	$mess = $form = '';


	if($FUNCPARAM[3] and $result = static_main::userAuth() and $result[1]) {
		//static_main::redirect($ref);
		$mess=$result[0];
	}

	$DATA = array (
		'mess'=>$mess,
		'result'=>$result,
		'#title#'=>$rowPG['name'],
		'remindpage'=>$FUNCPARAM[1]
	);


	$DATA = array($FUNCPARAM[0]=>$DATA);
	$html = $HTML->transformPHP($DATA,$tplphp);

	return $html;

