<?php

/**
 * Установка. Шаг №2
 *
 * проверка структуры уже установленных модулей
 * @author Xakki
 * @version 0.1
 * @return Вывод HTML кода процесса установки шага №2
 */

ini_set("max_execution_time", "100000");
set_time_limit(100000);

if (!isset($var_const))
    $var_const = array(
        'mess' => array(),
        'sbmt' => 'Сохранить'
    );
//$_CFG['modulprm'] = array();
$temp = null;
_new_class('modulprm', $MODUL, $temp, true);
//Форма установки модулей
list($res, $DATA) = static_tools::_toolsCheckmodul($MODUL);
if ($res == 1) {
    if (!isset($_GET['step'])) $_GET['step'] = 2;
    $_SESSION['step'] = $_GET['step'] + 1;
    if (count($var_const['mess']))
        $DATA['messages'][] = $var_const['mess'];
    $html = transformPHP($DATA, 'messages');
} else {
    $DATA = array('formcreat' => $DATA);
    $html = transformPHP($DATA, 'formcreat');
}

return $html;