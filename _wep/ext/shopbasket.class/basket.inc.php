<?php
/**
 * Плавающая корзина
 * @ShowFlexForm true
 * @author Xakki
 * @version 0.1 
 * @return $html
 */

// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or !$FUNCPARAM[0])
		$FUNCPARAM[0] = '#shopbasket#basket';
	if(!isset($FUNCPARAM[1]))
		$FUNCPARAM[1] = '';

	// рисуем форму для админки чтобы удобно задавать параметры
	if (isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		$form = array(
			'0' => array('type' => 'list', 'listname' => 'phptemplates', 'caption' => 'Шаблон плавающей корзины'),
			'1'=>array('type'=>'list','listname'=>'ownerlist', 'caption'=>'Страница корзины'),
		);
		return $form;
	}

	if(!_new_class('shopbasket',$SHOPBASKET)) return false;
	if(!_new_class('shop',$SHOP)) return false;
	$SHOP->basketEnabled = true;

	$DATA = $SHOPBASKET->fBasket();
	$DATA['#page#'] = $this->getHref($FUNCPARAM[1]);
	return $HTML->transformPHP($DATA,$FUNCPARAM[0]);