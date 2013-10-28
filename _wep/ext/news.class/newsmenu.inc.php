<?php
/**
 * Меню новостей
 * Вывод категорий новостей
 * @ShowFlexForm true
 * @type Новости
 * @ico menu.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

// сначала задаем значения по умолчанию
if (!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = 'ndate'; //группировка [ndate, active]
if (!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '#pg#menu'; //php template


// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
	$form = array(
		0 => array('type' => 'text', 'caption' => 'Группировка'),
		1 => array('type' => 'list', 'listname' => array('phptemplates', 'tags' => 'pgmenu'), 'caption' => 'Шаблон', 'comment' => $_CFG['lang']['tplComment']),
	);
	return $form;
}

if (!_new_class('news', $MODUL)) {
	$html = '<div style="color:red;">' . date('H:i:s') . ' : Модуль news не установлен</div>';
} else {
	$DATA = array();
	$DATA[$FUNCPARAM[1]] = $NEWS->fMenu($FUNCPARAM[0]);
	$html = transformPHP($DATA, $FUNCPARAM[1]);
}
return $html;
