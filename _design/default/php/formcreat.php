<?php
function tpl_formcreat(&$data)
{
    // Форма может содержать сообщения
    // if(!isset($data['form']) or !count($data['form']))
    // 	return '';

    global $_tpl, $PGLIST, $_CFG;
    $texthtml = '';

    if (isset($data['options']['id']) and $data['options']['id'])
        $hasIdData = 2;
    else
        $hasIdData = 1;

    if (isset($data['path']) and count($data['path'])) {
        include_once($data['DIR'] . '/path.php');
        $texthtml .= tpl_path($data['path'], $hasIdData); // PATH
    }


    $texthtml .= '<div class="divform' . ((isset($data['css']) and $data['css']) ? ' ' . $data['css'] : '') . '"';
    if (isset($data['style']) and $data['style'])
        $texthtml .= ' style="' . $data['style'] . '"';
    $texthtml .= '>';

    if (!isset($data['DIR']))
        $data['DIR'] = dirname(__FILE__);

    if (isset($data['messages']) and count($data['messages'])) {
        include_once($data['DIR'] . '/messages.php');
        $texthtml .= tpl_messages($data['messages']); // messages
    }

    if (isset($data['form']) and count($data['form'])) {
        if (!isset($data['options'])) {
            trigger_error('Ошибка. Старый формат данных. Отсутствуют опции для формы', E_USER_WARNING);
            return '';
        }
        $attr = $data['options'];

        if (isset($attr['enctype']))
            if ($attr['enctype'] == '')
                $enctype = '';
            else
                $enctype = ' enctype="' . $attr['enctype'] . '"';
        else
            $enctype = ' enctype="multipart/form-data"';
        if (!isset($attr['action']))
            $attr['action'] = '';
        if (!isset($attr['method']) or !$attr['method'])
            $attr['method'] = 'POST';
        $texthtml .= '<form id="' . $attr['name'] . '" method="' . $attr['method'] . '"' . $enctype . ' action="' . $attr['action'] . '"';
        if (isset($attr['onsubmit']))
            $texthtml .= ' onsubmit="' . $attr['onsubmit'] . '"';
        if (isset($attr['prevhref']))
            $texthtml .= ' data-prevhref="' . $attr['prevhref'] . '"';
        if (isset($PGLIST->contentID))
            $texthtml .= ' data-cid="' . $PGLIST->contentID . '"';
        $texthtml .= '>';

        include_once($data['DIR'] . '/form.php');
        if (isset($data['formSort']) and count($data['formSort']) and is_array(current($data['formSort']))) {
            plugJQueryUI();
            $_tpl['onload'] .= '$("#' . $attr['name'] . '").tabs();';
            $texthtml .= tpl_form($data['form'], $data['formSort']);
        } else
            $texthtml .= tpl_form($data['form']);
        $texthtml .= '</form>';

        if (isset($data['flag'])) {
            if ($data['flag'] == 1) {
            } elseif ($data['flag'] == -1) {
            } else {
                plugAjaxForm();
                $_tpl['onload'] .= 'if(typeof(formParam)=="undefined") formParam = {}; wep.form.initForm(\'#' . $attr['name'] . '\', formParam);';
            }
        } else {
            setScript('wepform');
        }

    } else {

    }

    if ($data['flag'] !== FORM_STATUS_DEFAULT) // !isset($_SESSION['user']['id'])
        $_tpl['onload'] .= 'reloadCaptcha(\'captcha\');';

    if (isset($data['flag']) and isset($data['options'])) {
        $_tpl['formFlag'] = $data['flag'];
        if ($data['flag'] == -1) {
            $_tpl['onload'] = '$("#' . $data['options']['name'] . '").trigger(\'error\'); ' . $_tpl['onload'];
        }
        $_tpl['onload'] = 'clearErrorForm("#' . $data['options']['name'] . '"); ' . $_tpl['onload'];
    }

    $texthtml .= '</div>';

    return $texthtml;
}
//<!--<div class="dscr"><span style="color:#F00">*</span> - обязательно для заполнения</div>
//<div class="dscr"><span style="color:#F00">**</span> - обязательно для заполнения хотябы одно поле</div>-->

