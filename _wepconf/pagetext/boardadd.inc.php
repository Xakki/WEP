<?
	global $BOARD, $RUBRIC;
	$html='';
	if(!$BOARD) $BOARD = new board_class($SQL);
	if(!$RUBRIC) $RUBRIC = new rubric_class($SQL);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;

	$DATA  = array();
	$_POST['rubric']=(int)$_REQUEST['rubric'];
	list($DATA['formcreat'],$flag) = $BOARD->_UpdItemModul(array('showform'=>1));

	$_tpl['onload'] .= '$(\'form[name=form_board]\').attr(\'action\',\''.$_CFG['_HREF']['siteJS'].'?_view=add\');JSFR(\'form[name=form_board]\');';
	if($flag==1) {
		$this->pageinfo['template'] = "waction";
		$html = $HTML->transformPHP($DATA['formcreat'],'messages');
	}
	else 
		$html = $HTML->transformPHP($DATA,'formcreat');
	return $html;
?>