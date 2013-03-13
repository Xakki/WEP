<?php
/**
 * Пополнение баланса пользователя
 * С выбором платежной системы для пополнения 
 * @ShowFlexForm true
 * @type Pay
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */
	// сначала задаем значения по умолчанию
	if(!isset($FUNCPARAM[0]) or $FUNCPARAM[0] == '') $FUNCPARAM[0] = 100;
	if(!isset($FUNCPARAM[1])) $FUNCPARAM[1] = '';
	//if($!isset($FUNCPARAM[2])) $FUNCPARAM[2] = '';



	// рисуем форму для админки чтобы удобно задавать параметры
	if(isset($ShowFlexForm)) { // все действия в этой части относительно модуля content
		//$temp = 'ownerlist';
		//$this->_enum['pagelist'] = $this->_getCashedList($temp);
		$form = array(
			0=>array('type'=>'int','caption'=>'Сумма пополнения счёта по умолчанию'),
			1=>array('type'=>'list','listname'=>'ownerlist','caption'=>'Страница списка счетов', 'mask'=>array('min'=>1)),
			//2=>array('type'=>'list','listname'=>'phptemplates','caption'=>'Шаблон', 'comment'=>$_CFG['lang']['tplComment']),
			
		);
		return $form;
	}
	// $FUNCPARAM[0] = (int)$FUNCPARAM[0]; // TODO

	setCss('/../_pay/pay');
	$html = '';

	if(!$Ctext)
		$html = '<p>Вы можете пополнить свой счет представленными ниже способами. Внимательно заполните необходимые поля и оплатите в установленный срок (индивидуальный для каждого способа). Вы получите уведомление (на Email или телефон) при изменении статуса счета.</p>';

	_new_class('pay', $PAY);


	$comm = 'Пополнение счёта пользователя "'.$_SESSION['user']['name'].'['.$_SESSION['user']['email'].']"';
	$payData = array(
		'_key' => 'ADDCASH'.$_SESSION['user']['id'], // Ключ
		'name' => $comm, // Коммент
		'paytype' => ADDCASH,
	);
	if($FUNCPARAM[1])
		$payData['paylink'] = $this->getHref($FUNCPARAM[1],true).'?payinfo=#id#';

	if(isset($_REQUEST['cost']) and $_REQUEST['cost'])
		$payData['cost'] = $_REQUEST['cost'];

	if(isset($this->pageParam[0]) and isset($PAY->childs[$this->pageParam[0]])) 
	{
		$pay_modul = $this->pageParam[0];
		$this->pageinfo['path'][$Chref.'/'.$pay_modul] = $PAY->childs[$pay_modul]->caption;
		$payData['pay_modul'] = $pay_modul;
	}

	$DATA = $PAY->billingForm($payData);

	$this->formFlag = $DATA['#resFlag#'];
	$html .= transformPHP($DATA, $DATA['tpl']);

	return $html;

