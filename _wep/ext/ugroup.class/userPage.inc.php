<?php
/**
 * Страница пользователя
 * @ShowFlexForm true
 * @type Служебные
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

_new_class('ugroup', $UGROUP);

if (!isset($FUNCPARAM[0])) $FUNCPARAM[0] = false; // - текущий	 пользователь, цыфра - уровень адреса ID пользователя
if (!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '#ugroup#userinfo';

if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
    $this->_enum['levelpage'] = array(
        0 => 'Из сессии пользователя',
        1 => 'Из первого уровня страницы',
        2 => 'Из второго уровня страницы',
        3 => 'Из третъего уровня страницы');
    $form = array(
        '0' => array('type' => 'list', 'listname' => 'levelpage', 'caption' => 'Как брать ID пользователя?'),
        '1' => array('type' => 'list', 'listname' => array('phptemplates', 'tags' => 'userinfo'), 'caption' => 'Шаблон', 'comment' => $_CFG['lang']['tplComment']),
    );
    return $form;
}

if ($FUNCPARAM[0]) {
    $FUNCPARAM[0] = $this->pageParam[(int)substr($FUNCPARAM[0], 1) - 1];
} else
    $FUNCPARAM[0] = $_SESSION['user']['id'];

$DATA = $UGROUP->childs['users']->UserInfo($FUNCPARAM[0]);
$DATA = array(
    $FUNCPARAM[1] =>
    array(
        'data' => $DATA,
        '#title#' => $Ctitle,
    )
);
$html = transformPHP($DATA, $FUNCPARAM[1]);
//TODO : информация о пользователе

return $html;
