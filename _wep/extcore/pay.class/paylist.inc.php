<?
// Корзина
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '#ext#paylist';
	//if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 0;


	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', __DIR__);
		//$temp = 'ownerlist';
		//$this->_enum['pagelist'] = $this->_getCashedList($temp);
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			//'1'=>array('type'=>'list','listname'=>'ownerlist','caption'=>'Страница меню'),
		);
		return $form;
	}

	$tplphp = $this->FFTemplate($FUNCPARAM[0],__DIR__);

	_new_class('pay', $PAY);
	$DATA = $PAY->diplayList($_SESSION['user']['id']);
	$DATA['#title#'] = $Ctitle;// Заголовок контента
	$DATA['#pagemenu#'] = $this->getHref();// Адрес тек страницы
	$DATA = array($FUNCPARAM[0]=>$DATA);
	$html .= $HTML->transformPHP($DATA,$tplphp);

	return $html;
