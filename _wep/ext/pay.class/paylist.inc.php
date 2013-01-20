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
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = '#pay#paylist';
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = 0;


	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) 
	{ // все действия в этой части относительно модуля content
		global $_CFG;
		$form = array(
			'0'=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон', 'comment'=>$_CFG['lang']['tplComment']),
			'1'=>array('type'=>'checkbox','caption'=>'Показывать имена мользователей участвующих в транзакции'),
			//'1'=>array('type'=>'list','listname'=>'ownerlist','caption'=>'Страница меню'),
		);
		return $form;
	}

	_new_class('pay', $PAY);
	$DATA = $PAY->getPayList(null, $_SESSION['user']['id']);
	$DATA['#title#'] = $Ctitle;// Заголовок контента
	$DATA['#pagemenu#'] = $this->getHref();// Адрес тек страницы
	$DATA['#showUser#'] = (bool)$FUNCPARAM[1];
	if(isset($_SESSION['user']['level']) and $_SESSION['user']['level']==0)
		$DATA['#showUser#'] = true;
	  
	$DATA = array($FUNCPARAM[0]=>$DATA);
	$html .= $HTML->transformPHP($DATA,$FUNCPARAM[0]);

	return $html;
