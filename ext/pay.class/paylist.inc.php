<?php
/**
 * Список платежей и счетов
 * Платежи и пополнения счетов где участвует пользователь просматривающий эту страницу
 * @ShowFlexForm true
 * @type Pay
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

// Корзина
// сначала задаем значения по умолчанию
if (!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '#pay#paylist';
if (!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '#pay#statusForm';
if (!isset($FUNCPARAM[2])) $FUNCPARAM[2] = 0;


// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
    global $_CFG;
    $form = array(
        0 => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон списка', 'comment' => $_CFG['lang']['tplComment']),
        1 => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон статуса счета', 'comment' => $_CFG['lang']['tplComment']),
        2 => array('type' => 'checkbox', 'caption' => 'Показывать имена мользователей участвующих в транзакции'),
        //'1'=>array('type'=>'list','listname'=>'ownerlist','caption'=>'Страница меню'),
    );
    return $form;
}

_new_class('pay', $PAY);

if (isset($_GET['payinfo']) and $_GET['payinfo']) {
    $DATA = $PAY->statusForm($_GET['payinfo'], true);
    if (isset($DATA['showStatus']))
        $PGLIST->pageinfo['path'][$Chref . '&payinfo=' . $_GET['payinfo']] = $DATA['showStatus']['name'];
    else
        $PGLIST->pageinfo['path'][$Chref . '&payinfo=' . $_GET['payinfo']] = 'Счёт №' . $_GET['payinfo'];
    $PGLIST->formFlag = $DATA['#resFlag#'];
    if ($FUNCPARAM[1])
        $DATA['tpl'] = $FUNCPARAM[1];
} else {
    $DATA = $PAY->getPayList(null, $_SESSION['user']['id']);
    if ($FUNCPARAM[0])
        $DATA['tpl'] = $FUNCPARAM[0];
}
$DATA['#title#'] = $Ctitle; // Заголовок контента
$DATA['#page#'] = $Chref; // Адрес тек страницы
$DATA['#showUser#'] = (bool)$FUNCPARAM[1];
if (isset($_SESSION['user']['level']) and $_SESSION['user']['level'] == 0)
    $DATA['#showUser#'] = true;

$html .= transformPHP($DATA, $DATA['tpl']);

return $html;
