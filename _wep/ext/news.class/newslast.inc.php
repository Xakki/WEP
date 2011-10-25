<?php
	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = '#ext#newslast'; // Шаблон
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 4; // Лимит
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = 0; // Страница с новостями

	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_getCashedList('phptemplates', dirname(__FILE__));

		$form = array(
			'0'=>array('type'=>'list', 'listname'=>'phptemplates', 'caption'=>'Шаблон'),
			'1'=>array('type'=>'int', 'caption'=>'Лимит'),
			'2'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница Новостей'),
		);
		return $form;
	}

	$tplphp = $this->FFTemplate($FUNCPARAM[0],dirname(__FILE__));

	$html = '';
	//$FUNCPARAM[0] - limit
	//$FUNCPARAM[1] - php template

		if(_new_class('news',$NEWS)) {
			$DATA = $NEWS->fLastNews($FUNCPARAM[1]);
			$DATA = array(
				'#item#'=>$DATA,
				'#Ctitle#'=>$Ctitle);
			if($FUNCPARAM[2])
				$DATA['#page#'] = $this->getHref($FUNCPARAM[2]);
			$DATA = array($FUNCPARAM[0]=>$DATA);
			$html = $HTML->transformPHP($DATA,$tplphp);
		}
	return $html;
