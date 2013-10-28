<?php
/**
 * Товар дня
 * @ShowFlexForm true
 * @type Shop
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

if (!isset($FUNCPARAM[0]) or !$FUNCPARAM[0])
	$FUNCPARAM[0] = '0';
if (!isset($FUNCPARAM[1]))
	$FUNCPARAM[1] = '#shopsale#productEvent';

// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content

	$form = array(
		'0' => array('type' => 'list', 'listname' => 'ownerlist', 'caption' => 'Страница каталога', 'mask' => array('min' => 1)),
		'1' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон'),
	);
	return $form;
}

if (!_new_class('shop', $SHOP)) return false;
if (!_new_class('shopsale', $SHOPSALE)) return false;

$DATA = array();
$DATA['#item#'] = $SHOPSALE->getTodaySale($SHOP->childs['product']);
$DATA['#page#'] = $this->getHref($FUNCPARAM[0]);
$DATA['#text#'] = $rowPG['pg'];
$DATA['#title#'] = $rowPG['name'];
$DATA['#shopconfig#'] = $SHOP->config;
return transformPHP($DATA, $FUNCPARAM[1]);