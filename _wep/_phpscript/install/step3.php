<?
/**
 * Установка. Шаг №3
 *
 * установка выбранных модулей, и отключение (или даже удаление таблиц)не выбранных модулей, Создание таблиц, папок для устанавливаемых модулей.
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

//static_main::includeModulFile('modulprm');
//static_main::includeModulFile('ugroup');

if(_new_class('modulprm', $MODULPRM)) {
	//Форма установки модулей
	list($res, $DATA) = $MODULPRM->instalModulForm();
} else  {
	$res = 0;
	$DATA['messages'][] = array('ok','Ошибка инициализации модуля `modulprm`');
}


if ($res == 1) {
	$_SESSION['step'] = $_GET['step']+1;
	$DATA['messages'][] = $var_const['mess'];
	$html = $HTML->transformPHP($DATA, 'messages');
} else {
	$DATA = array('formcreat' => $DATA);
	$html = $HTML->transformPHP($DATA, 'formcreat');
}

return $html;