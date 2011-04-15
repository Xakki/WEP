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


_new_class('modulprm', $MODUL);

//Создание таблицы modulprm
static_tools::_installTable($MODUL);

//Форма установки модулей
list($res, $DATA) = $MODUL->instalModulForm();
if ($res == 1) {
	$mess[] = array('name' => 'ok', 'value' => 'Пора перейти к <a href="install.php?step=' . ($_GET['step'] + 1) . '">следующему шагу №' . ($_GET['step'] + 1) . '</a>');
	$html = $HTML->transformPHP($DATA, 'messages');
} else {
	$DATA = array('formcreat' => $DATA);
	$html = $HTML->transformPHP($DATA, 'formcreat');
}

return $html;
?>