<?
	global $BOARD, $RUBRIC;
	$html='';
	if(!$BOARD) $BOARD = new board_class($SQL);
	if(!$RUBRIC) $RUBRIC = new rubric_class($SQL);
	if(!$BOARD->RUBRIC) $BOARD->RUBRIC = &$RUBRIC;

	if(isset($_GET['rubric']) and $rid = (int)$_GET['rubric']) {
		unset($PGLIST->pageinfo['path']['list']);
		$RUBRIC->simpleRubricCache();
		if(!count($RUBRIC->data2)) 
			return '';
		$RUBRIC->getPath($rid);// прописываем путь

		$formparam = array();
		$formparam['filter'] = $BOARD->boardFindForm($rid);// форма поиска

		if(count($formparam)) {
			$_tpl['param'] .='<div class="blockhead">Параметры</div><div class="hrb"></div>'.$HTML->transformPHP($formparam,'filter');
			//$_tpl['onload'] .= "JSFR('#paramselect');";
			if(strpos($_SERVER['REQUEST_URI'],'?')=== false)
				$req = '?rubric='.$rid;
			else
				$req = strstr($_SERVER['REQUEST_URI'],'?');
			$html = $HTML->transform('<main>'.$BOARD->fListDisplay($rid,$_GET).' <req><![CDATA['.$req.']]></req></main>','boardlist');
		}
	}

	return $html;
?>