<?php
/**
 * Форма оплаты счета
 * @type Платежная система
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_billingForm($data)
{
	global $_tpl;
	setCss('/../_pay/pay');
	$currency = $data['#config#']['curr'];
	$html = '<div class="billingForm">';

	if(isset($data['#title#']) and $data['#title#'])
		$html .= '<h2>'.$data['#title#'].'</h2>';

	if($data['#resFlag#']===-2)
		$data['messages'][] = array('error', 'Ошибка! Не коректно введенные данные!');

	if($data['#resFlag#']===-3)
		$data['messages'][] = array('error', 'Произошла ошибка! Обратитесь к администрации за помощью!');

	$data['messages'][] = array('alert', $data['#comm#']);
	if($data['#summ#']>0)
		$data['messages'][] = array('alert', 'Сумма - '.number_format($data['#summ#'], 2, ',', ' ').' '.$currency);

	if(isset($data['messages']) and count($data['messages'])) {
		$html .= transformPHP($data['messages'], '#pg#messages');
	}

	// выводим форму выбора плат системы ии форму для выбранной плат системы
	if(isset($data['form'])) //#resFlag#==0
	{
		unset($data['form']['_info']);
		unset($data['messages']);
		$html .= transformPHP($data, '#pg#formcreat');
	}
	// Вывводим статус платежа
	else
	{
		$html .= '<br/><div class="paySpanMess" onclick="window.location.reload();">Обновите страницу, чтобы узнать состояния счёта.</div>';

	}

	$html .= '</div>';

	return $html;
}
