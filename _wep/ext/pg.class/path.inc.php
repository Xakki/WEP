<?php
/**
 * Хлебные крошки
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */

if (!isset($FUNCPARAM[0]))
	$FUNCPARAM[0] = '#pg#pathPage'; // Шаблон
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
	$form = array(
		'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон'),
	);
	return $form;
}

/* PATH */
$DATA = array($FUNCPARAM[0] => $PGLIST->get_path());

if (count($DATA[$FUNCPARAM[0]]) > 1) {
	end($DATA[$FUNCPARAM[0]]);
	$temp = current($DATA[$FUNCPARAM[0]]);
	if(!$_tpl['description'])
		$_tpl['description'] = $temp['name'];
}

$html = $HTML->transformPHP($DATA, $FUNCPARAM[0]);
/* 	PATH */
return $html;
