<?
	global $BOARD, $RUBRIC;
	$html='';
	if(!$BOARD) $BOARD = new board_class($SQL);
	if(!$RUBRIC) $RUBRIC = new rubric_class($SQL);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;

	if(isset($_GET['id']) and $id = (int)$_GET['id']) {
		$html = $HTML->transform($BOARD->fDisplay($id),'boarditems');
		if(count($BOARD->data) and isset($BOARD->childs['comments']) and $BOARD->data[$id]['on_comm']) {

			$DATA2 = $DATA = array();
			$BOARD->id = $id;
			$parent_id = 0;
			if(isset($_REQUEST['commanswer']))
				$parent_id= (int)$_REQUEST['commanswer'];

			$parentcomm = $BOARD->childs['comments']->displayData($id,$parent_id);// запрос данных
			$DATA2['comments']['data'] = &$BOARD->childs['comments']->data;
			$DATA2['comments']['vote'] = $BOARD->childs['comments']->config['vote'];

			
			if($parentcomm)
				$BOARD->childs['comments']->parent_id = $parent_id;
			//$this->fields_form['text']['caption'] = 'Текст комментария';
			$BOARD->childs['comments']->locallang['default']['_submit'] = 'Написать комментарий';
			list($DATA['formcreat'],$flag) = $BOARD->childs['comments']->_UpdItemModul(array('ajax'=>1));
			$DATA['formcreat']['css'] = 'form_comments';
			$_tpl['onload'] .= '$(\'#form_comments\').attr(\'action\',\''.$_CFG['_HREF']['siteJS'].'?_view=add&amp;_modul=comments\');JSFR(\'#form_comments\');';
			if($flag==1) {
				$this->pageinfo['template'] = "waction";
				$html = $HTML->transformPHP($DATA['formcreat'],'messages');
			}
			else{
				if(isset($DATA['formcreat']['form']['_info']['caption']) and $parentcomm)
					$DATA['formcreat']['form']['sbmt']['value'] = 'Ответ на комментарий `'._substr($parentcomm,0,25).'...`';
				$html .= $HTML->transformPHP($DATA2,'comments').$HTML->transformPHP($DATA,'formcreat');
			}
		}
	}else
		$html = '<h2>'.$_CFG['_MESS']['errdata'].'</h2>';
	return $html;
?>