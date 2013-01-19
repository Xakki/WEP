<?php
/**
 * Пополнение баланса пользователя
 * С выбором платежной системы для пополнения 
 * @ShowFlexForm false
 * @type Pay
 * @ico default.png
 * @author Xakki
 * @version 0.2
 * @return string html
 */

	$_tpl['styles']['/_pay/pay'] = 1;
	$html = '';

	_new_class('pay', $PAY);
	if(isset($this->pageParam[0]) and isset($PAY->childs[$this->pageParam[0]])) 
	{
		$pay_modul = $this->pageParam[0];
		$this->pageinfo['path'][$Chref.'/'.$pay_modul] = $PAY->childs[$pay_modul]->caption;
		$comm = 'Пополнение счёта пользователя "'.$_SESSION['user']['name'].'['.$_SESSION['user']['email'].']"';
		$payData = array(
			'_key' => 'ADDCASH'.$_SESSION['user']['id'], // Ключ
			'name' => $comm, // Коммент
			'paylink' => '', // TODO
			'paytype' => ADDCASH,
			'pay_modul' => $pay_modul
		);
		if(isset($_GET['cost']) and $_GET['cost'])
			$payData['cost'] = $_GET['cost'];

		$DATA = $PAY->billingForm($payData);

		$this->formFlag = $DATA['#resFlag#'];
		$html = $HTML->transformPHP($DATA, $DATA['tpl']);
	}
	else 
	{
		$html = '
		<p>Вы можете пополнить свой	счет представленными ниже способами. Внимательно заполните необходимые поля и оплатите в установленный срок (индивидуальный для каждого способа). Вы получите уведомление (на Email или телефон) при изменении статуса счета.</p>
		<div class="paytype">';

		_new_class('pay', $PAY);
		if(count($PAY->childs)) {
			if(isset($_GET['cost']) and $_GET['cost'])
				$cost = '?cost='.$_GET['cost'];
			else
				$cost = '';
			foreach($PAY->childs as &$child) {
				$html .= '<a class="ico_'.$child->_cl.'" href="'.$Chref.'/'.$child->_cl.'.html'.$cost.'" title="'.$child->caption.'">'.$child->caption.'</a>';
			}
			unset($child);
		}

		$html .= '</div>';
	}

	return $html;

