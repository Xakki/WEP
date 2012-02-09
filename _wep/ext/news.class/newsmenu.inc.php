<?php
/**
 * Меню новостей
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */

	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = 'ndate'; //группировка [ndate, active]
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '#news#newsmenu';//php template


	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			0=>array('type'=>'text','caption'=>'Группировка'),
			1=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
		);
		return $form;
	}

		if(!_new_class('news',$MODUL)) {
			$html = '<div style="color:red;">'.date('H:i:s').' : Модуль news не установлен</div>';
		}
		else {
			$DATA = array();
			$DATA[$FUNCPARAM[1]] = $NEWS->fMenuNews($FUNCPARAM[0]);
			$html = $HTML->transformPHP($DATA,$FUNCPARAM[1]);
		}
	return $html;
