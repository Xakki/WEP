<?
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '#ext#loginAjax';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '';
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = '';

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', __DIR__);
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
			'0'=>array('type'=>'list','listname'=>'levelmenuinc', 'caption'=>'Страница "Напомнить пароль"'),
			'2'=>array('type'=>'list','listname'=>'levelmenuinc', 'caption'=>'Страница исключение'),
		);

		return $form;
	}

	$tplphp = $this->FFTemplate($FUNCPARAM[0],__DIR__);

	if(isset($FUNCPARAM[2])) { // страница исключение
		if($this->id==$FUNCPARAM[2]) return '';
	}

	if($FUNCPARAM[1])
		$FUNCPARAM[1] = $this->getHref($FUNCPARAM[1],true);

	$result = array();
	$mess = $form = '';

	if(isset($_COOKIE['remember']) and !static_main::_prmUserCheck() and $result = static_main::userAuth() and $result[1]) {
		//@header("Location: ".$ref);
		//die();
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

