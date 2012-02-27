<?php
/**
 * Список товаров по каталогу
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */

// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = '#shop#productList';
	if(!isset($FUNCPARAM[1]) or !$FUNCPARAM[1]) $FUNCPARAM[1] = ''; // rubric
	$filter = array();
	if(!isset($FUNCPARAM[2]) or !$FUNCPARAM[2]) $FUNCPARAM[2] = 0; // rss
	if(!isset($FUNCPARAM[3]) or !$FUNCPARAM[3]) $FUNCPARAM[3] = 't1.mf_timecr'; // сортировка
	if(!isset($FUNCPARAM[4]) or !$FUNCPARAM[4]) $FUNCPARAM[4] = '10'; // LIMIT

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон'),
			'1' => array('type' => 'list', 'listname' => array('class'=>'shop','is_tree'=>true), 'caption' => 'Рубрика'),
			'2' => array('type' => 'checkbox', 'caption' => 'RSS'),
			'3' => array('type' => 'text', 'caption' => 'сортировка'),
			'4' => array('type' => 'int', 'caption' => 'LIMIT'),
		);
		return $form;
	}

	if(!_new_class('shop',$SHOP)) return false;

	$SHOP->simplefCache();
	$DATA = $SHOP->childs['product']->fList($FUNCPARAM[1],$filter,$FUNCPARAM[2],$FUNCPARAM[3],$FUNCPARAM[4]);
	$DATA = array($FUNCPARAM[0]=>$DATA);
	return $HTML->transformPHP($DATA,$FUNCPARAM[0]);