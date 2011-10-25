<?php
	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = '#ext#news'; // Шаблон

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', dirname(__FILE__));

		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
		);
		return $form;
	}

	$tplphp = $this->FFTemplate($FUNCPARAM[0],dirname(__FILE__));

	$html = '';


	if(_new_class('news',$NEWS)) {
		$DATA = $NEWS->fNews();
		$DATA['#page#'] = $this->getHref();
		$DATA = array($FUNCPARAM[0]=>$DATA);
		$html = $HTML->transformPHP($DATA,$tplphp);
	}
	return $html;
