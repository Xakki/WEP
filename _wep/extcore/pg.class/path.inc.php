<?
	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = 'pathPage'; // Шаблон
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', __DIR__);

		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
		);
		return $form;
	}

	$tplphp = $this->FFTemplate($FUNCPARAM[0],__DIR__);

/*PATH*/
	$DATA = array($FUNCPARAM[0]=>$PGLIST->get_path());
	$html = $HTML->transformPHP($DATA, $tplphp);
/*	PATH */
	return $html;
