<?
	global $BOARD, $RUBRIC;
	$html='';
	if(!$BOARD) _new_class('board',$BOARD);
	if(!$RUBRIC) _new_class('rubric',$RUBRIC);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;
	$cntPP = count($_GET['page']);
	if((!isset($_GET['rubric']) or !$_GET['rubric']) and $cntPP>1) {
		$RUBRIC->simpleRubricCache();
		if(isset($RUBRIC->data_path[$_GET['page'][($cntPP-2)]]))
			$rid = $_GET['rubric'] = $RUBRIC->data_path[$_GET['page'][$cntPP-2]];
	}
	if(isset($_GET['rubric']) and $rid = (int)$_GET['rubric']) {
		unset($PGLIST->pageinfo['path'][$this->id]);
		$RUBRIC->simpleRubricCache();
		if(!count($RUBRIC->data2)) 
			return '';
		$RUBRIC->getPath($rid);// прописываем путь
		//$_tpl['title'] = $PGLIST->get_caption();

		$formparam = array();
		$formparam['filter'] = $BOARD->boardFindForm($rid);// форма поиска

		if(count($formparam)) {
			$_tpl['param'] .='<div class="blockhead">Параметры поиска</div><div class="hrb"></div>'.$HTML->transformPHP($formparam,'filter').'<br/>';
			//$_tpl['onload'] .= "JSFR('#form_tools_paramselect');";
			if(strpos($_SERVER['REQUEST_URI'],'?')=== false)
				$req = '?rubric='.$rid;
			else
				$req = strstr($_SERVER['REQUEST_URI'],'?');
			$ppath = parse_url($_SERVER['REQUEST_URI']);
			$html = $HTML->transform('<main>'.$BOARD->fListDisplay($rid,$_GET).' <req><![CDATA['.$req.']]></req><pg>'.$PGLIST->getHref().'.html</pg></main>','boardlist');
		}//'.$ppath['path'].'
	} else {
		$html = $HTML->transform($RUBRIC->MainRubricDisplay(),'rubricmain');
	}

	return $html;
