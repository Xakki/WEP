<?

/**
 * Установка. Шаг №3
 *
 * проверка структуры уже установленных модулей
 * @author Xakki
 * @version 0.1
 * @return Вывод HTML кода процесса установки шага №2
 */
global $HTML;
if($_SESSION['step']<$_GET['step'])
	return 'Как ты попал сюда? Вернитесь на <a href="'.$_CFG['PATH']['wepname'].'/install.php?step=' . $_SESSION['step'] . '">Шаг №'.$_SESSION['step'].'</a>.';

_new_class('modulprm', $MODUL);

//Форма установки модулей
list($res, $DATA) = static_tools::_toolsCheckmodul($MODUL);
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