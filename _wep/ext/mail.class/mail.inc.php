<?php
/**
 * Форма "написать письмо"
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = 'formcreat';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '';


	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_enum['pagelist'] = array(
			0=>'Обратная связь',
			2=>'--');
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			'1'=>array('type'=>'text','caption'=>'Кому'),
			//'2'=>array('type'=>'list','listname'=>'typemlist','caption'=>'Тип письма'),
		);
		return $form;
	}
	if(!$Ctitle) $Ctitle = 'Отправка письма службе поддержки';

	global $MAIL;
	if(!$MAIL) _new_class('mail', $MAIL);

	$DATA = array();
	list($DATA[$FUNCPARAM[0]],$flag) = $MAIL->mailForm($FUNCPARAM[1]);
	if(isset($DATA[$FUNCPARAM[0]]['form']['_info']))
		$DATA[$FUNCPARAM[0]]['form']['_info']['caption'] = $Ctitle;

	if($flag==1) {
		$HTML->_templates = "waction";
		$html = $HTML->transformPHP($DATA[$FUNCPARAM[0]],'messages');
	}
	else
		$html = $HTML->transformPHP($DATA,$FUNCPARAM[0]);
	return $html;

