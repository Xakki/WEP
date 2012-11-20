<?php
/**
 * Статическое меню страниц
 * Строго задются страницы отображаемые в меню
 * @ShowFlexForm true
 * @type Элементы страниц
 * @ico form.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

// сначала задаем значения по умолчанию
if (!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '')
	$FUNCPARAM[0] = '#pg#menu';
if (!isset($FUNCPARAM[1]))
	$FUNCPARAM[1] = array();


// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
	$form = array(
		0 => array('type' => 'list', 'listname' => array('phptemplates', 'tags'=>'pgmenu'), 'caption' => 'Шаблон', 'comment'=>$_CFG['lang']['tplComment']),
		1 => array('type' => 'list', 'multiple'=>2, 'listname' => 'ownerlist', 'caption' => 'Элементы меню'),
		
	);
	return $form;
}

$DATA = array('#item#' => $PGLIST->getPGMap($FUNCPARAM[1]));
$DATA['#title#'] = $Ctitle;
$html .= $HTML->transformPHP($DATA, $FUNCPARAM[0]);

return $html;
