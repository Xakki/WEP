<?php
/**
 * Меню страниц NEW
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */

// сначала задаем значения по умолчанию
if (!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '')
	$FUNCPARAM[0] = '#pg#menu';
if (!isset($FUNCPARAM[1]))
	$FUNCPARAM[1] = array();


// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
	$form = array(
		0 => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон'),
		1 => array('type' => 'list', 'multiple'=>2, 'listname' => 'ownerlist', 'caption' => 'Элементы меню'),
		
	);
	return $form;
}

$DATA = array('#item#' => $PGLIST->getPGMap($FUNCPARAM[1]));
$DATA['#title#'] = $Ctitle;
$html .= $HTML->transformPHP($DATA, $FUNCPARAM[0]);

return $html;
