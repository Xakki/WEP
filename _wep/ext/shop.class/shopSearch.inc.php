<?php
/**
 * Поиск товаров
 * @ShowFlexForm true
 * @type Shop
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0])
		$FUNCPARAM[0] = '#shop#productList';
	if(!isset($FUNCPARAM[1])) 
		$FUNCPARAM[1] = 't1.mf_timecr'; // сортировка
	if(!isset($FUNCPARAM[2])) 
		$FUNCPARAM[2] = '10'; // LIMIT
	if (!isset($FUNCPARAM[3]))
		$FUNCPARAM[3] = 0;

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0' => array('type' => 'list', 'listname' => array('phptemplates', 'tags'=>'shopprodlist'), 'caption' => 'Шаблон'),
			'1' => array('type' => 'text', 'caption' => 'сортировка'),
			'2' => array('type' => 'int', 'caption' => 'LIMIT'),
			'3'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница каталога'),
		);
		return $form;
	}
	if(!$FUNCPARAM[3])  return '<h2>ОШИБКА! Необходимо указать страницу каталога<h2>';
	if(!_new_class('shop',$SHOP)) return false;

	$SHOP->simplefCache();
	$SHOP->childs['product']->messages_on_page = $FUNCPARAM[2];
	$DATA = $SHOP->childs['product']->fList(0,$_GET,0,$FUNCPARAM[1]);

	if($SHOP->basketEnabled)
		$DATA['#basket#'] = $SHOP->fBasketData();

	if(count($DATA['#item#']) and _new_class('shopsale',$SHOPSALE)) {
		$SHOPSALE->getData($DATA['#item#']);
	}

	$DATA['#page#'] = $this->getHref($FUNCPARAM[3]);
	if(strpos($_SERVER['REQUEST_URI'],'?')=== false)
		$req = '?shop='.$rid;
	else
		$req = strstr($_SERVER['REQUEST_URI'],'?');
	//$ppath = parse_url($_SERVER['REQUEST_URI']);//'.$ppath['path'].'
	$DATA['req'] = $req;
	$DATA['atarget'] = '_blank';
	return $HTML->transformPHP($DATA,$FUNCPARAM[0]);