<?php
/**
 * Каталог товаров
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */

// сначала задаем значения по умолчанию
if (!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '')
	$FUNCPARAM[0] = '#shop#productList';
if (!isset($FUNCPARAM[1]))
	$FUNCPARAM[1] = 0;

// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
	$form = array(
		'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон'),
		'1'=>array('type'=>'list','listname'=>'content','caption'=>'Страница, которая будет отображаться по дефолту'),
	);
	return $form;
}

	if(!_new_class('shop',$SHOP)) return false;

	$html='';
	$cntPP = count($this->pageParam);
	if((!isset($_GET['shop']) or !$_GET['shop']) and $cntPP>0) {
		$SHOP->simplefCache();
		if(isset($SHOP->data_path[$this->pageParam[($cntPP-1)]]))
			$rid = $_GET['shop'] = $SHOP->data_path[$this->pageParam[$cntPP-1]];
	}
	if(isset($_GET['shop']) and $rid = (int)$_GET['shop']) {
		array_pop($PGLIST->pageinfo['path']);
		$SHOP->simplefCache();
		if(!count($SHOP->data2)) 
			return '';


		// Путь рубрики
		$href = '';
		$fPATH = $SHOP->getPath($rid,$Chref);// прописываем путь
		if(count($fPATH)) {
			$PGLIST->pageinfo['path']=$PGLIST->pageinfo['path']+$fPATH;
			end($fPATH);
			$href = key($fPATH);
		}

		$formparam = array();
		$formparam['filter'] = $SHOP->childs['product']->productFindForm($rid,1,$Chref);// форма поиска

		if(count($formparam)) {
			//print_r('<pre>');print_r($formparam['filter']);
			if(count($formparam['filter']))
				$html .='<div class="blockhead searchslide" onclick="slideBlock(this,\'#form_tools_paramselect\');">Поиск</div><div class="hrb"></div>'.$HTML->transformPHP($formparam,'#pg#filter').'<br/>';
			if(isset($_GET['sbmt']) and $_GET['sbmt']=='Поиск')
				$_tpl['onload'] .= 'jQuery(\'div.searchslide\').click();';
			//$_tpl['onload'] .= "JSFR('#form_tools_paramselect');";
			if(strpos($_SERVER['REQUEST_URI'],'?')=== false)
				$req = '?shop='.$rid;
			else
				$req = strstr($_SERVER['REQUEST_URI'],'?');
			$ppath = parse_url($_SERVER['REQUEST_URI']);

			$DATA = $SHOP->childs['product']->fList($rid,$_GET);
			$DATA['req'] = $req;
			$DATA['pg'] = $Chref;
			$html .= $HTML->transformPHP($DATA,$FUNCPARAM[0]);
		}//'.$ppath['path'].'
	} else {
		$html = $this->display_inc($FUNCPARAM[1]);
	}

	return $html;
