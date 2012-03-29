<?php
TODO:
/**
 * Список платежей
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $form
 * @return $html
 */
// Корзина
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '#pay#paylist';
	//if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 0;


	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		//$temp = 'ownerlist';
		//$this->_enum['pagelist'] = $this->_getCashedList($temp);
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон'),
			//'1'=>array('type'=>'list','listname'=>'ownerlist','caption'=>'Страница меню'),
		);
		return $form;
	}

	_new_class('pay', $PAY);
	$DATA = $PAY->displayListUser($_SESSION['user']['id']);
	$DATA['#title#'] = $Ctitle;// Заголовок контента
	$DATA['#pagemenu#'] = $this->getHref();// Адрес тек страницы
	$DATA = array($FUNCPARAM[0]=>$DATA);
	$html .= $HTML->transformPHP($DATA,$FUNCPARAM[0]);

	return $html;
