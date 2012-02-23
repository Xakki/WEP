<?php
	if(!$FUNCPARAM[0]) $FUNCPARAM[0] = 'catalog';
	global $CATALOG;
	$html='';
	if(!$CATALOG) _new_class('catalog',$CATALOG);
	$cntPP = count($this->pageParam);
	if((!isset($_GET['catalog']) or !$_GET['catalog']) and $cntPP>0) {
		$CATALOG->simpleCatalogCache();
		if(isset($CATALOG->data_path[$this->pageParam[($cntPP-1)]]))
			$rid = $_GET['catalog'] = $CATALOG->data_path[$this->pageParam[$cntPP-1]];
	}
	if(isset($_GET['catalog']) and $rid = (int)$_GET['catalog']) {
		array_pop($PGLIST->pageinfo['path']);
		$CATALOG->simpleCatalogCache();
		if(!count($CATALOG->data2)) 
			return '';
		$CATALOG->getPath($rid);// прописываем путь
		//$_tpl['title'] = $PGLIST->get_caption();

		$formparam = array();
		$formparam['filter'] = $CATALOG->childs['product']->productFindForm($rid);// форма поиска

		if(count($formparam)) {
			$html .='<div class="blockhead searchslide" onclick="slideBlock(this,\'#form_tools_paramselect\');">Поиск</div><div class="hrb"></div>'.$HTML->transformPHP($formparam,'filter').'<br/>';
			if(isset($_GET['sbmt']) and $_GET['sbmt']=='Поиск')
				$_tpl['onload'] .= 'jQuery(\'div.searchslide\').click();';
			//$_tpl['onload'] .= "JSFR('#form_tools_paramselect');";
			if(strpos($_SERVER['REQUEST_URI'],'?')=== false)
				$req = '?catalog='.$rid;
			else
				$req = strstr($_SERVER['REQUEST_URI'],'?');
			$ppath = parse_url($_SERVER['REQUEST_URI']);

			$DATA = array($FUNCPARAM[0]=> $CATALOG->childs['product']->fListDisplay($rid,$_GET));
			$DATA[$FUNCPARAM[0]]['req'] = $req;
			$DATA[$FUNCPARAM[0]]['pg'] = $PGLIST->getHref();
			$html .= $HTML->transformPHP($DATA,$FUNCPARAM[0]);
		}//'.$ppath['path'].'
	} else {
		$menuF = function() use ($HTML) {
			$FUNCPARAM[0] = 'catalogMain';
			$FUNCPARAM[1] = 'catalog';
			return include(__DIR__.'/catalogMenu.inc.php');
		};
		$html = $menuF();
	}

	return $html;
