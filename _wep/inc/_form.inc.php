<?php
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 0;
	//$FUNCPARAM[0] - модуль
	//$FUNCPARAM[1] - включить AJAX

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		global $_CFG;
		$this->_enum['modullist'] = array();
		foreach($_CFG['modulprm'] as $k=>$r) {
			if($r['active'])
				$this->_enum['modullist'][$r['pid']][$k] = $r['name'];
		}
		$form = array(
			'0'=>array('type'=>'list','listname'=>'modullist', 'caption'=>'Модуль'),
			'1'=>array('type'=>'checkbox', 'caption'=>'Включить AJAX?'),
		);
		return $form;
	}

	if(_new_class($FUNCPARAM[0],$MODUL)) {
		$DATA  = array();
		list($DATA['formcreat'],$flag) = $MODUL->_UpdItemModul(array('showform'=>1));
		$html = $HTML->transformPHP($DATA,'formcreat');
		if($FUNCPARAM[1]) $_tpl['onload'] .= 'JSFR("form#form_'.$MODUL->_cl.'");';
	}
	else $html = 'Ошибка подключения модуля';
	return $html;