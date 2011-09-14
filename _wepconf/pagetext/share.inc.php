<?
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = 'share';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = false;
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = false;
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = false;
	if(!isset($FUNCPARAM[4])) $FUNCPARAM[4] = false;

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', __DIR__);
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates', 'caption'=>'Шаблон'),
			'1'=>array('type'=>'checkbox', 'caption'=>'Google+'),
			'2'=>array('type'=>'checkbox', 'caption'=>'Twitter'),
			'3'=>array('type'=>'checkbox', 'caption'=>'Facebook'),
			'4'=>array('type'=>'checkbox', 'caption'=>'Вконтакте'),
		);
		return $form;
	}
	$tplphp = $this->FFTemplate($FUNCPARAM[0],__DIR__);

	$DATA = array($FUNCPARAM[0]=>array(
		'title'=>'',
		'desc'=>'',
		//'img'=>$firstimg,
		'gplus'=>$FUNCPARAM[1],'tw'=>$FUNCPARAM[2],'fb'=>$FUNCPARAM[3],'vk'=>$FUNCPARAM[4]));
	$html = $HTML->transformPHP($DATA,$tplphp);
	return $html;
