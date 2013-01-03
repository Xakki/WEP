<?php
/**
 * statusForm
 * @type Платежная система
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_statusForm($data)
{
	global $_tpl, $HTML;
	//$_CFG['fileIncludeOption']['form'] = 1;
	$_tpl['styles']['/_pay/pay'] = 1;
	$html = '';
	$currency = $data['#config#']['curr'];

	print_r('<pre>');print_r($data);

	if($data['#resFlag#']===-1)
	{
		$data['messages'][] = array('error', 'Ошибка! У вас не достаточно прав доступа, для просмотра этого счета!');
	}
	elseif(isset($data['showStatus']) and is_array($data['showStatus']))
	{
		$pd = $data['showStatus'];

			$data['messages'][] = array('alert', $pd['name']);

			$data['messages'][] = array('alert', 'Сумма - '.$pd['cost'].' '.$currency);

			$data['messages'][] = array('alert', 'Статус - '.$pd['#status#']);

			if(isset($data['#payLink#']) and $data['#payLink#'] and $pd['status']<2)
				$data['messages'][] = array('payLink', '<a href="'.$data['#payLink#'].'" target="_blank">Оплатить</a>');

	}

	if(isset($data['messages']) and count($data['messages'])) {
		$html .= $HTML->transformPHP($data['messages'], '#pg#messages');
	}

	return $html;
}
