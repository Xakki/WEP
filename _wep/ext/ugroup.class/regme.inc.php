<?php
/**
 * Регистрация пользователя
 * @ShowFlexForm true
 * @type Форма
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

_new_class('ugroup', $UGROUP);

if (!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = '#pg#formcreat';
if (!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '';
if (!isset($FUNCPARAM[2])) $FUNCPARAM[2] = array('email' => 'email', $UGROUP->childs['users']->fn_pass => $UGROUP->childs['users']->fn_pass);

// рисуем форму для админки чтобы удобно задавать параметры
if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
    //$temp = 'ownerlist';
    $form = array(
        '0' => array('type' => 'list', 'listname' => array('phptemplates', 'tags' => 'form'), 'caption' => 'Шаблон', 'comment' => $_CFG['lang']['tplComment']),
        '1' => array('type' => 'list', 'listname' => array('class' => 'ugroup'), 'caption' => 'Регистрировать в указанную группу'),
        '2' => array('type' => 'list', 'listname' => 'userfieldlist', 'multiple' => FORM_MULTIPLE_JQUERY, 'caption' => 'Выводимые поля'),
    );
    $this->_enum['userfieldlist'] = array();
    foreach ($UGROUP->childs['users']->fields_form as $k => $r) {
        $this->_enum['userfieldlist'][$k] = $r['caption'];
    }
    return $form;
}

$DATA = array();
if (isset($_GET['confirm'])) {
    list($DATA, $flag) = $UGROUP->regConfirm();
    $html = '<a href="/index.html">Обновите страницу</a>';
    $_tpl['logs'] .= '<div id="ajaxload" style="display: block; top: 20%; left: 35%; height: 290px;">
		<div class="layerblock">
			<div onclick="window.location=\'/index.html\'" class="blockclose"></div>
				<div class="blockhead"><a href="/index.html">' . ($flag ? 'Вы успешно авторизованы. ' : '') . 'Обновите страницу</a></div>
					<div class="hrb">&nbsp;</div>
					' . transformPHP($DATA, '#pg#messages') . '
					<div class="clear">&nbsp;</div>
				</div>
			</div>
		</div>
		<div class="ajaxbg" style="opacity: 0.5; display: block;">&nbsp;</div>';
    $_tpl['onload'] .= 'fMessPos();';
} else {
    $param = array();
    if ((int)$FUNCPARAM[1])
        $param['owner_id'] = (int)$FUNCPARAM[1];
    $argForm = array();

    foreach ($FUNCPARAM[2] as $r) {
        if (isset($UGROUP->childs['users']->fields_form[$r]))
            $argForm[$r] = $UGROUP->childs['users']->fields_form[$r];
    }
    $argForm[$UGROUP->childs['users']->fn_pass] = array('type' => 'password', 'caption' => 'Пароль', 'mask' => array('min' => '6', 'fview' => 1));

    $UGROUP->childs['users']->id = null;

    list($DATA, $flag) = $UGROUP->regForm($param, $argForm);
    unset($DATA['form']['_info']);
    $html = transformPHP($DATA, $FUNCPARAM[0]);
}
setCss('login');
return $html;
