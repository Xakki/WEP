<?php
/**
 * Редактирование профиля
 * @ShowFlexForm true
 * @type Форма
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0]) $FUNCPARAM[0] = '#pg#formcreat';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '';
	_new_class('ugroup', $UGROUP);

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		//$temp = 'ownerlist';
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон формы'),
			'1'=>array('type'=>'list','listname'=>'userfieldlist', 'multiple'=>2, 'caption'=>'Выводимые поля'),
		);
		$this->_enum['userfieldlist'] = array();
		foreach($UGROUP->childs['users']->fields_form as $k=>$r) {
			$this->_enum['userfieldlist'][$k] = $r['caption'];
		}
		return $form;
	}

	if(!static_main::_prmUserCheck()) return false;

	$UGROUP->childs['users']->lang['Save and close'] = 'Сохранить';

	$DATA = array();
	$param = array('formflag'=>1);
	$argForm = array();
	foreach($FUNCPARAM[1] as $r) {
		if(isset($UGROUP->childs['users']->fields_form[$r]))
			$argForm[$r] = $UGROUP->childs['users']->fields_form[$r];
	}
	$argForm[$UGROUP->childs['users']->fn_pass] = array('type' => 'password', 'caption' => 'Для подтверждения введите пароль','mask'=>array('min' => '6','fview'=>1, 'password'=>'confirm'));

	$UGROUP->childs['users']->id = (int)$_SESSION['user']['id'];

	list($DATA[$FUNCPARAM[0]],$flag) = $UGROUP->regForm($param,$argForm);
	$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);

	return $html;
