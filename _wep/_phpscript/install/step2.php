<?

global $HTML;

/**
 * Установка. Шаг №2
 *
 * установка выбранных модулей, и отключение (или даже удаление таблиц)не выбранных модулей, Создание таблиц, папок для устанавливаемых модулей.
 * @author Xakki
 * @version 0.1
 * @return Вывод HTML кода процесса установки шага №2
 */
if($_SESSION['step']<$_GET['step'])
	return 'Как ты попал сюда? Вернитесь на <a href="'.$_CFG['PATH']['wepname'].'/install.php?step=' . $_SESSION['step'] . '">Шаг №'.$_SESSION['step'].'</a>.';
if(_new_class('modulprm', $MODUL)) {
	//Создание таблицы modulprm
	static_tools::_installTable($MODUL);
	//Форма установки модулей
	list($res, $DATA) = $MODUL->instalModulForm();
} else  {
	$res = 0;
	$DATA['messages'][] = array('name' => 'ok', 'value' => 'Ошибка инициализации модуля `modulprm`');
}


if ($res == 1) {
	$_SESSION['step'] = $_GET['step']+1;
	$DATA['messages'][] = array('name' => 'ok', 'value' => 'Пора перейти к <a href="'.$_CFG['PATH']['wepname'].'/install.php?step=' . ($_GET['step'] + 1) . '">следующему шагу №' . ($_GET['step'] + 1) . '</a>');
	$html = $HTML->transformPHP($DATA, 'messages');
} else {
	$DATA = array('formcreat' => $DATA);
	$html = $HTML->transformPHP($DATA, 'formcreat');
}

return $html;
?>