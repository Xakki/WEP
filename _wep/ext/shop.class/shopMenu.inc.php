<?php
/**
 * Меню каталога
 * @ShowFlexForm true
 * @type Shop
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

if (!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '')
	$FUNCPARAM[0] = '#shop#shopMain';
if (!isset($FUNCPARAM[1]))
	$FUNCPARAM[1] = '0';
if (!isset($FUNCPARAM[2]))
	$FUNCPARAM[2] = '0';

// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
	$form = array(
		'0' => array('type' => 'list', 'listname' => array('phptemplates', 'tags' => 'shopmenu'), 'caption' => 'Шаблон', 'comment' => $_CFG['lang']['tplComment']),
		'1' => array('type' => 'list', 'listname' => 'ownerlist', 'caption' => 'На какую страницу указывать'),
		'2' => array('type' => 'list', 'listname' => array('class' => 'shop', 'is_tree' => true), 'caption' => 'Начало каталога'),
	);
	return $form;
}

if (!_new_class('shop', $MODUL)) return false;

$html = '';

$MODUL->fCache();

if (isset($PGLIST->pageParam[0]) and $PGLIST->pageParam[0] and isset($MODUL->data_path[$PGLIST->pageParam[0]]))
	$select = $MODUL->data_path[$PGLIST->pageParam[0]];
else
	$select = 0;

$DATA = array();
$DATA['#item#'] = $MODUL->fDisplay($FUNCPARAM[2], $select);
$DATA['#page#'] = $this->getHref($FUNCPARAM[1]);
$DATA['#title#'] = $Ctitle;

$html = transformPHP($DATA, $FUNCPARAM[0]);

return $html;
