<?php
/**
 * Корзина - оформление заказа
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $html
 */

// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0])
		$FUNCPARAM[0] = '#shopbasket#basketlist';
	if(!isset($FUNCPARAM[1]))
		$FUNCPARAM[1] = '';

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон корзины-список'),
			'1'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница каталога'),
		);
		return $form;
	}

	if(!_new_class('shopbasket',$SHOPBASKET)) return false;
	if(!_new_class('shopdeliver',$SHOPDELIVER)) return false;
	if(!_new_class('pay', $PAY)) return false;

	if(isset($_GET['typedelivery'])) {
		// STEP 2
		return 'TODO : регистрация нового пользователя или подтверждения контактов';
	} 
	else {
		// STEP 1 
		$DATA = $SHOPBASKET->fBasketList();
		$DATA['#pageCat#'] = $this->getHref($FUNCPARAM[1]);
		$DATA['#page#'] = $this->getHref();
		$DATA['#delivery#'] = $SHOPDELIVER->qs('*','WHERE active=1','id');
		$DATA['#curr#'] = $PAY->config['curr'];
		return $HTML->transformPHP($DATA,$FUNCPARAM[0]);
	}