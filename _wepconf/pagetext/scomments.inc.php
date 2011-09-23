<?php
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0])) $FUNCPARAM[0] = 2415476;
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 10;
	if(!isset($FUNCPARAM[2])) $FUNCPARAM[2] = 496;
	if(!isset($FUNCPARAM[3])) $FUNCPARAM[3] = 'false';

	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$this->_enum['vkattach'] = array(
			'false'=>'нет',
			'true'=>'да',
		);
		$form = array(
			'0'=>array('type'=>'int', 'caption'=>'Вконтакте - apiId'),
			'1'=>array('type'=>'int', 'caption'=>'Вконтакте - limit'),
			'2'=>array('type'=>'int', 'caption'=>'Вконтакте - width'),
			'3'=>array('type'=>'list', 'listname'=>'vkattach', 'caption'=>'Вконтакте - attach',''=>'Разрешать добавление фаилов в коммент?'),
		);
		return $form;
	}
	global $_tpl;
	$_tpl['script']['vk'] = array('http://userapi.com/js/api/openapi.js?33');
	$_tpl['script']['vkinit'] = 'VK.init({apiId: '.$FUNCPARAM[0].', onlyWidgets: true});';
	$_tpl['onload'] .= 'VK.Widgets.Comments("vk_comments", {limit: '.$FUNCPARAM[1].', width: "'.$FUNCPARAM[2].'", attach: '.$FUNCPARAM[3].'});';
	$html = '<div id="vk_comments"></div>';

	return $html;
