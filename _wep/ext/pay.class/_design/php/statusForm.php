<?php
/**
 * OLD - statusForm
 * @type Платежная система
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_statusForm($data)
{
	//print_r('<pre>');print_r($data);
	global $_tpl;
	setCss('/../_pay/pay');
	$html = '';
	

	if($data['#resFlag#']===-1)
	{
		$data['messages'][] = array('error', 'Ошибка! У вас не достаточно прав доступа, для просмотра этого счета!');
	}
	elseif(isset($data['showStatus']) and is_array($data['showStatus']))
	{
		$currency = $data['#config#']['curr'];
		$pd = $data['showStatus'];

		$data['messages'][] = array('alert', $pd['name']);

		$data['messages'][] = array('alert', 'Счёт <b>№'.$pd['id'].'</b>');

		$data['messages'][] = array('alert', 'Сумма - <b>'.number_format($pd['cost'], 2, ',', ' ').' '.$currency.'</b>');

		$data['messages'][] = array('alert', 'Статус - <b>'.$pd['#status#'].'</b>');

		if(isset($data['#payLink#']) and $data['#payLink#'] and $pd['status']<2)
			$data['messages'][] = array('payLink', '<a href="'.$data['#payLink#'].'" target="_blank">Оплатить</a>');

	}

	// TODO  - азобраться со статусом, хотяб константы запилить надо
	if(isset($data['confirmCancel']) and $data['confirmFlag']<1 and $pd['status']<2)
	{
		$data['messages'][] = array('confirmCancel'.($data['confirmFlag']<0?' mustShowBlock':''), '<a class="ajaxlink" onclick="$(\'.confirmCancel .divform\').toggle();return false;">Отменить счет</a>
		'.transformPHP($data['confirmCancel'], '#pg#formcreat'));
	}

	//После оплаты обновите <a href="javascript:window.location.reload();">страницу</a>, чтобы узнать состояние счёта.

	if(isset($data['messages']) and count($data['messages'])) 
	{
		$html .= '<div class="divform">'.transformPHP($data['messages'], '#pg#messages').'</div>';
	}


	if(isset($data['showFrom']))
	{
		//unset($data['form']['_info']);
		$data['showFrom']['ajaxForm'] = false;
		$html .= transformPHP($data['showFrom'], '#pg#formcreat');

	}

	return $html;
}
