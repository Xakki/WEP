<?
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = 10;
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '#ext#boardnew';

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', __DIR__);
		$form = array(
			'0'=>array('type'=>'int', 'caption'=>'Limit'),
			'1'=>array('type'=>'list','listname'=>'phptemplates', 'caption'=>'Шаблон'),
		);
		return $form;
	}
	$tplphp = $this->FFTemplate($FUNCPARAM[1],__DIR__);

	_new_class('board',$BOARD);
	_new_class('rubric',$RUBRIC);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;

	$RUBRIC->simpleRubricCache();


	$DATA = array($FUNCPARAM[1]=>$BOARD->fNewDisplay($FUNCPARAM[0]));
	return '<div class="blockhead">Новые объявления</div><div class="hrb"></div>'.$HTML->transformPHP($DATA,$tplphp);
