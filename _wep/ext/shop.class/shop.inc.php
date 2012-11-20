<?php
/**
 * Каталог товаров
 * @ShowFlexForm true
 * @type Shop
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

// сначала задаем значения по умолчанию
if (!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '')
	$FUNCPARAM[0] = '#shop#productList';
if (!isset($FUNCPARAM[1]))
	$FUNCPARAM[1] = '#shop#shopMain';
if (!isset($FUNCPARAM[2]))
	$FUNCPARAM[2] = '0';
if (!isset($FUNCPARAM[3]))
	$FUNCPARAM[3] = 1;
if (!isset($FUNCPARAM[4]))
	$FUNCPARAM[4] = 10;
if (!isset($FUNCPARAM[5]))
	$FUNCPARAM[5] = 1;
if (!isset($FUNCPARAM[6]))
	$FUNCPARAM[6] = 't1.mf_timecr';
if (!isset($FUNCPARAM[7]))
	$FUNCPARAM[7] = 1;
if(!isset($FUNCPARAM[8]))
	$FUNCPARAM[8] = '#shop#productItem';
if(!isset($FUNCPARAM[9]))
	$FUNCPARAM[9] = '#shop#productLike';

// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
	$this->_enum['shopsubdir'] = array('AJAX подкаталог', 'Выводить все подкаталоги в поиске', 'Выводить подкаталоги сверху по шаблону');
	$form = array(
		'0' => array('type' => 'list', 'listname' => array(array('phptemplates', 'tags'=>'shopprodlist'), 'tags'=>'shopprodlist'), 'caption' => 'Шаблон `Список товаров`'),
		'1' => array('type' => 'list', 'listname' =>array('phptemplates', 'tags'=>'shopmenu'), 'caption' => 'Шаблон подкаталога'),
		'2' => array('type' => 'list', 'listname' => array('class'=>'shop','is_tree'=>true), 'caption' => 'Начало каталога'),
		'3' => array('type' => 'list', 'listname' =>'shopsubdir', 'caption' => 'Поиск:подуровневый каталог'),
		'4' => array('type' => 'int', 'caption' => 'Limit'),
		'5' => array('type' => 'checkbox', 'caption' => 'Постраничная навигация'),
		'6' => array('type' => 'text', 'caption' => 'Сортировка'),
		'7' => array('type' => 'checkbox', 'caption' => 'Выводить список товаров подкатегории, если есть подкатегории'),
		'8' => array('type' => 'list', 'listname' => array('phptemplates', 'tags'=>'shopproditem'), 'caption' => 'Шаблон `Страница товара`'),
		'9' => array('type' => 'list', 'listname' => array('phptemplates', 'tags'=>'shoplike'), 'caption' => 'Шаблон `Сопутствующие товары`'),
		// ,'style'=>($FUNCPARAM[3]!=2?'display:none;':'')
		// , 'onchange'=>'if(this.value==2) $("#tr_flexform_4").slideDown("slow"); else $("#tr_flexform_4").slideUp("slow"); '
	);
	return $form;
}

	if(!_new_class('shop',$SHOP)) return false;
	$PRODUCT = &$SHOP->childs['product'];

	$html='';
	$cntPP = count($this->pageParam);
	if((!isset($_GET['shop']) or !$_GET['shop']) and $cntPP>0) {
		$SHOP->simplefCache();
		if(isset($SHOP->data_path[$this->pageParam[0]]))
			$_GET['shop'] = $SHOP->data_path[$this->pageParam[0]];
		if(isset($this->pageParam[1])) {
			$PRODUCT->id = (int)$_GET['id'];
		}
	}

	$SHOP->simplefCache();
	if(!count($SHOP->data2))
		return '';

	if($PRODUCT->id) {
		$DATA = array('#item#'=>$PRODUCT->fItem($PRODUCT->id));
		$DATA['#page#'] = $Chref;
		$DATA['#shopconfig#'] = $SHOP->config;
		if($SHOP->basketEnabled)
			$DATA['#basket#'] = $SHOP->fBasketData();
		$DATA['#prodItem#'] = &$PRODUCT->config['prodItem'];
		if(count($DATA['#item#']) and _new_class('shopsale',$SHOPSALE)) {
			$SHOPSALE->getData($DATA['#item#']);
		}
		$html .= $HTML->transformPHP($DATA,$FUNCPARAM[8]);
		if(isset($PRODUCT->data[$PRODUCT->id]) and count($PRODUCT->data[$PRODUCT->id])) {
			array_pop($PGLIST->pageinfo['path']);
			$fPATH = $SHOP->getPath($PRODUCT->data[$PRODUCT->id][$PRODUCT->owner_name],$Chref,$FUNCPARAM[2]);// прописываем путь
			if(count($fPATH)) {
				$this->pageinfo['path']=$this->pageinfo['path']+$fPATH;
			}
			$this->pageinfo['path'][]['name'] = $PRODUCT->data[$PRODUCT->id]['name'];

			/*$PRODUCT->data[$PRODUCT->id]['shops'] = array_reverse($PRODUCT->data[$PRODUCT->id]['shops']);
			$temp = $this->pageinfo['path'];$tcnt = count($temp);
			$this->pageinfo['path'] = array();
			$c=1;
			foreach($temp as $tk=>$tr) {
				if($c<($tcnt-1))
					$this->pageinfo['path'][$tk] = $tr;
				elseif($c==$tcnt) {
					$this->pageinfo['path'][$tk] = $tr;
					$this->pageinfo['path'][$tk]['name'] = $PRODUCT->data[$PRODUCT->id]['name'];
				}
				else {
					foreach($PRODUCT->data[$PRODUCT->id]['shops'] as $rr)
						$this->pageinfo['path'][$Chref.'/'.$SHOP->data2[$rr['id']]['path'].'/'.$PGLIST->getHref($tk)] = $rr['name'];
				}
				$c++;
			}*/
		} else {
			return 404;
		}
		if($FUNCPARAM[9]) {
			$DATA = $PRODUCT->childs['product_like']->fLike($PRODUCT->id);
			$DATA['#page#'] = $Chref;
			$DATA['#shopconfig#'] = $SHOP->config;
			$html .= $HTML->transformPHP($DATA,$FUNCPARAM[9]);
		}
	}
	elseif(isset($_GET['shop']) and $rid = (int)$_GET['shop']) {

		// Путь рубрики
		//$href = '';
		array_pop($PGLIST->pageinfo['path']);
		$fPATH = $SHOP->getPath($rid,$Chref,$FUNCPARAM[2]);// прописываем путь
		if(count($fPATH)) {
			$PGLIST->pageinfo['path']=$PGLIST->pageinfo['path']+$fPATH;
			//end($fPATH);
			//$href = key($fPATH);
		}

		$formparam = array();
		$formparam = $PRODUCT->productFindForm($rid,$FUNCPARAM[3],$Chref);// форма поиска

		if(count($formparam)) {

			$subCatHtml = '';
			if($FUNCPARAM[3]==2 and isset($SHOP->data[$rid]) and count($SHOP->data[$rid])) {
				$DATA2 = array();
				$DATA2['#item#'] = $SHOP->fDisplay($rid);
				$DATA2['#page#'] = $Chref;
				if($SHOP->basketEnabled) 
					$DATA2['#basket#'] = $SHOP->fBasketData();
				//$DATA2['#title#'] = $Ctitle;
				$subCatHtml = $HTML->transformPHP($DATA2,$FUNCPARAM[1]);
			}

			$searchHtml = '';
			if(count($formparam)) {
				//<div class="blockhead searchslide shhide" onclick="slideBlock(this,\'#form_tools_paramselect\');">Поиск</div><div class="hrb"></div>
				$searchHtml = $HTML->transformPHP($formparam,'#pg#filter').'<br/>';
				if(isset($_GET['sbmt']) and $_GET['sbmt']=='Поиск')
					$_tpl['onload'] .= 'jQuery(\'div.searchslide\').click();';//$("#form_tools_paramselect").hide(); 
				//$_tpl['onload'] .= "$('#tr_shopl').insertBefore('.searchslide');";
				//$_tpl['onload'] .= "JSFR('#form_tools_paramselect');";
			}

			if($FUNCPARAM[5]) {
				$PRODUCT->messages_on_page = $FUNCPARAM[4];
				$FUNCPARAM[4] = 0;
			}
			if($FUNCPARAM[7])
				$FUNCPARAM[7] = 'list';
			else
				$FUNCPARAM[7] = 'listn';

			$DATA = $PRODUCT->fList($rid, $_GET, $FUNCPARAM[7], $FUNCPARAM[6], $FUNCPARAM[4]);
			// $rid,$filter,$rss=0,$order='t1.mf_timecr',$limit=0
			
			$html .= $subCatHtml;

			if(count($DATA) or !$subCatHtml) {
				if(strpos($_SERVER['REQUEST_URI'],'?')=== false)
					$req = '?shop='.$rid;
				else
					$req = strstr($_SERVER['REQUEST_URI'],'?');
				//$ppath = parse_url($_SERVER['REQUEST_URI']);//'.$ppath['path'].'
				$DATA['req'] = $req;
				$DATA['#page#'] = $Chref;

				if($SHOP->basketEnabled)
					$DATA['#basket#'] = $SHOP->fBasketData();

				if(count($DATA['#item#']) and _new_class('shopsale',$SHOPSALE)) {
					$SHOPSALE->getData($DATA['#item#'], $rid);
				}

				if($subCatHtml or isset($DATA['#item#']) or isset($DATA['#filter#']))
					$html .= $searchHtml;
				//$DATA['#fields#'] = &$PRODUCT->fields;
				$html .= $HTML->transformPHP($DATA, $FUNCPARAM[0]);
			}
		}
	} 
	else {
		if(isset($SHOP->data[$FUNCPARAM[2]]) and count($SHOP->data[$FUNCPARAM[2]])) {
			$DATA2 = array();
			$DATA2['#item#'] = $SHOP->fDisplay($FUNCPARAM[2]);
			$DATA2['#page#'] = $Chref;
			if($SHOP->basketEnabled)
				$DATA2['#basket#'] = $SHOP->fBasketData();
			//$DATA2['#title#'] = $Ctitle;
			$html .= $HTML->transformPHP($DATA2,$FUNCPARAM[1]);
		}
	}

	return $html;
