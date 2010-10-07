<?
	$FUNCPARAM = explode('&',$FUNCPARAM);
	//$FUNCPARAM[0] - модуль
	//$FUNCPARAM[1] - включить AJAX
	if(_new_class($FUNCPARAM[0],$MODUL)) {
		$DATA  = array();
		$_POST['rubric']=(int)$_REQUEST['rubric'];
		list($DATA['formcreat'],$flag) = $MODUL->_UpdItemModul(array('showform'=>1));
		$html = $HTML->transformPHP($DATA,'formcreat');
		if($FUNCPARAM[1]) $_tpl['onload'] .= "JSFR('form#form_board');";
	}
	else $html = 'Ошибка подключения модуля';
	return $html;
?>