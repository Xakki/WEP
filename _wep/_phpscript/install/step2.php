<?php

/**
 * Установка. Шаг №2
 *
 * проверка структуры уже установленных модулей
 * @author Xakki
 * @version 0.1
 * @return Вывод HTML кода процесса установки шага №2
 */
global $HTML;
if(!isset($var_const))
	$var_const = array(
		'mess'=>array(),
		'sbmt'=>'Сохранить'
	);
//$_CFG['modulprm'] = array();

_new_class('modulprm', $MODUL);
//Форма установки модулей
list($res, $DATA) = static_tools::_toolsCheckmodul($MODUL);
if ($res == 1) {
	if(!isset($_GET['step'])) $_GET['step']= 2;
	$_SESSION['step'] = $_GET['step']+1;
	if(count($var_const['mess']))
		$DATA['messages'][] = $var_const['mess'];
	$html = $HTML->transformPHP($DATA, 'messages');
} else {
	$DATA = array('formcreat' => $DATA);
	$html = $HTML->transformPHP($DATA, 'formcreat');
}

return $html;