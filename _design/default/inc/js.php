<?php

$DATA = array();
if ($_GET['_view'] == 'list') {
    $MODUL->_clp = '_view=list&amp;_modul=' . $MODUL->_cl . '&amp;';
    $param = array('ajax' => 1, 'firstpath' => ADMIN_BH . '?_view=list&');
    list($DATA, $flag) = $MODUL->super_inc($param, $_GET['_type']);
    $mess = '';
    if (isset($DATA['formcreat']) and count($DATA['formcreat'])) {
        if ($flag == 1) {
            //end($HTML->path);prev($HTML->path);
            $_tpl['text'] = transformPHP($DATA['formcreat'], 'messages');
        } else {
            //$DATA['formcreat']['path'] = $HTML->path;
            $_tpl['text'] = transformPHP($DATA, 'formcreat');
        }
    } elseif (isset($DATA['static']) and $DATA['static']) {
        $_tpl['text'] = '';
        if (isset($DATA['messages']) and count($DATA['messages']))
            $_tpl['text'] .= transformPHP($DATA, 'messages');
        $_tpl['text'] .= $DATA['static'];
    } elseif (isset($DATA['formtools']) and count($DATA['formtools'])) {
        if (isset($DATA['formtools'][1]['form'])) {
            $DATA['formtools'] = $DATA['formtools'][1]; //
            trigger_error('WTF is it?', E_USER_WARNING);
        }

        if (isset($DATA['formtools']['reloadPage']) and $DATA['formtools']['reloadPage'])
            $_tpl['onload'] .= 'wep.fShowloadReload();';

        $_tpl['text'] = transformPHP($DATA, 'formtools');

    } elseif ($flag != 3) {
        //end($HTML->path);
        $_tpl['text'] = transformPHP($DATA['superlist'], 'messages');
        $_tpl['onload'] = 'wep.load_href("' . str_replace('&amp;', '&', key($HTML->path)) . '");';
    } else {
        $DATA['superlist']['path'] = $HTML->path;
        $_tpl['text'] = transformPHP($DATA, 'superlist');
    }
} elseif ($_GET['_view'] == 'contentIncParam') {
    $CT = & $MODUL->childs['content'];
    $CT->fields_form = array();
    $_POST['funcparam'] = htmlspecialchars_decode($_POST['funcparam']);
    if ($form = $CT->getContentIncParam($_POST, true) and count($form)) {
        if ($CT->kFields2FormFields($form)) {
            $data['form'] = & $form;
            $_tpl['text'] = transformPHP($data, 'form');
        }
        $_tpl['onload'] .= 'jQuery(\'#tr_funcparam\').hide();';
    } else {
        $_tpl['onload'] .= 'jQuery(\'#tr_funcparam\').show();';
    }
} else
    $_tpl['onload'] = 'fLog(\'<div style="color:red;">' . date('H:i:s') . ' : Параметры заданны неверно!</div>\',1);';


