<?php
/**
 * Форматор
 * Построитель форм для модулей
 * @ShowFlexForm true
 * @type Форма
 * @ico form.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */
if (!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '';
if (!isset($FUNCPARAM[1])) $FUNCPARAM[1] = array();
//$FUNCPARAM[0] - модуль
//$FUNCPARAM[1] - включить AJAX

// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
    global $_CFG;
    $this->_enum['modullist'] = array();
    foreach ($_CFG['modulprm'] as $k => $r) {
        if ($r['active'])
            $this->_enum['modullist'][$r['pid']][$k] = $r['name'];
    }

    $this->_enum['userfieldlist'] = array();
    if ($FUNCPARAM[0] and _new_class($FUNCPARAM[0], $MODUL)) {
        foreach ($MODUL->fields_form as $k => $r) {
            $this->_enum['userfieldlist'][$k] = $r['caption'];
        }
    }

    $form = array(
        '0' => array('type' => 'list', 'listname' => 'modullist', 'caption' => 'Модуль'),
        '1' => array('type' => 'list', 'listname' => 'userfieldlist', 'multiple' => FORM_MULTIPLE_JQUERY, 'caption' => 'Выводимые поля'),
    );
    return $form;
}

if (_new_class($FUNCPARAM[0], $MODUL)) {
    $DATA = array();
    if ($Ctitle != '')
        $MODUL->lang['add_name'] = ($Ctitle ? $Ctitle : '');

    $argForm = array();
    foreach ($FUNCPARAM[1] as $r) {
        if (isset($MODUL->fields_form[$r]))
            $argForm[$r] = $MODUL->fields_form[$r];
    }

    list($DATA, $this->formFlag) = $MODUL->_UpdItemModul(array('showform' => 1), $argForm);

    $html = transformPHP($DATA, '#pg#formcreat');
} else
    $html = '<error>Ошибка подключения модуля</error>';

return $html;
