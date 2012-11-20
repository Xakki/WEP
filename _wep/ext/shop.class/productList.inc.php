<?php
/**
 * Список товаров по каталогу
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
		$FUNCPARAM[1] = ''; // rubric
	if(!isset($FUNCPARAM[2])) 
		$FUNCPARAM[2] = 0; // rss
	if(!isset($FUNCPARAM[3])) 
		$FUNCPARAM[3] = 't1.mf_timecr'; // сортировка
	if(!isset($FUNCPARAM[4])) 
		$FUNCPARAM[4] = '10'; // LIMIT
	if (!isset($FUNCPARAM[5]))
		$FUNCPARAM[5] = 0;

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_enum['sortprod'] = array(
			't1.mf_timecr'=>'По новизне',
			't1.name'=>'По названию',
			't1.cost'=>'По цене',
			'RAND()'=>'Случайный выбор (Кеширование*)',
		);
		$form = array(
			'0' => array('type' => 'list', 'listname' => array('phptemplates', 'tags'=>'shopprodlist'), 'caption' => 'Шаблон'),
			'1' => array('type' => 'list', 'listname' => array('class'=>'shop','is_tree'=>true), 'caption' => 'Рубрика'),
			'2' => array('type' => 'checkbox', 'caption' => 'RSS'),
			'3' => array('type' => 'list', 'listname' => 'sortprod', 'multiple'=>2, 'caption' => 'Cортировка', 'comment'=>'Порядок вывода товаров'),
			'4' => array('type' => 'int', 'caption' => 'LIMIT'),
			'5'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница каталога'),
		);
		return $form;
	}
	if(!$FUNCPARAM[5])  return '<h2>ОШИБКА! Необходимо указать страницу каталога<h2>';
	if(!_new_class('shop',$SHOP)) return false;
	$filter = array();

	$SHOP->simplefCache();
	if(is_array($FUNCPARAM[3])) $FUNCPARAM[3] = implode(', ',$FUNCPARAM[3]);
	$DATA = $SHOP->childs['product']->fList($FUNCPARAM[1],$filter,$FUNCPARAM[2],$FUNCPARAM[3],$FUNCPARAM[4]);
	$DATA['#page#'] = $this->getHref($FUNCPARAM[5]);
	return $HTML->transformPHP($DATA,$FUNCPARAM[0]);