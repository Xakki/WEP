<?
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = 'formcreat';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '';


	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', dirname(__FILE__));
		$this->_enum['pagelist'] = array(
			0=>'Обратная связь',
			2=>'--');
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			'1'=>array('type'=>'text','caption'=>'Кому'),
			//'2'=>array('type'=>'list','listname'=>'typemlist','caption'=>'Тип письма'),
		);
		return $form;
	}
	if(!$Ctitle) $Ctitle = 'Отправка письма службе поддержки';

	$tplphp = $this->FFTemplate($FUNCPARAM[0],dirname(__FILE__));

	global $MAIL;
	if(!$MAIL) _new_class('mail', $MAIL);

	$DATA = array();
	list($DATA[$FUNCPARAM[0]],$flag) = $MAIL->mailForm($FUNCPARAM[1]);
	if(isset($DATA[$FUNCPARAM[0]]['form']['_info']))
		$DATA[$FUNCPARAM[0]]['form']['_info']['caption'] = $Ctitle;

	if($flag==1) {
		$HTML->_templates = "waction";
		$html = $HTML->transformPHP($DATA[$FUNCPARAM[0]],'messages');
	}
	else
		$html = $HTML->transformPHP($DATA,$tplphp);
	return $html;

