<?
// Корзина
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '#ext#paymove';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 0;


	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', dirname(__FILE__));
		//$temp = 'ownerlist';
		//$this->_enum['pagelist'] = $this->_getCashedList($temp);
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			'1'=>array('type'=>'checkbox','caption'=>'Запрос подчинённых юзеров'),
		);
		return $form;
	}
	if($_SESSION['user']['parent_id'])
		return $this->getMess('denied');
	
	$tplphp = $this->FFTemplate($FUNCPARAM[0],dirname(__FILE__));

	_new_class('pay', $PAY);
	$DATA = array();
	$param = array();

	if($FUNCPARAM[1])
		$param['cls'] = ' and t1.parent_id='.$_SESSION['user']['id'];
	$DATA['#pay#'] = $PAY->payMove($param);
	$DATA['#title#'] = $Ctitle;// Заголовок контента
	$DATA['#pagemenu#'] = $this->getHref();// Адрес тек страницы
	$DATA = array($FUNCPARAM[0]=>$DATA);
	$html .= $HTML->transformPHP($DATA,$tplphp);

	return $html;
