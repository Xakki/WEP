<?php
/**
 * OLD - Список счетов
 * @type Платежная система
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_list($data)
{
	global $_tpl;
	setCss('/../_pay/pay');
	$html = '';
	if (count($data['#list#'])) {
		$html .= '<h3>' . $data['#title#'] . '</h3>
			<table class="pay_list">
			<tr>
				<th>#</th>
				<th>Операция</th>
				<th>Сумма</th>
				<th>Статус</th>
				<th>Плат. система</th>
				<th>Время</th>
			</tr>';
		$color = array(
			0 => 'auto',
			1 => 'green',
			2 => 'red',
			3 => 'gray',
			4 => '#a151a1',
		);
		foreach ($data['#list#'] as $k => $r) {
			if (!$r['status']) {
				if ($r['#formType#'] === true)
					$r['#status#'] .= ' [<a data-data="payinfo=' . $r['id'] . '" class="goClick ajaxlink">Оплатить</a>]';
				elseif ($r['#formType#'])
					$r['#status#'] .= ' [<a data-data="payinfo=' . $r['id'] . '" class="goClick ajaxlink">Оплатить</a>]';
			}
			$html .= '<tr>
				<td>' . $r['id'] . '</td>
				<td>' . $r['name'] . ' ' . (!$r['status'] ? '[действителен до ' . date('Y-m-d H:i', ($r['mf_timecr'] + ($r['#lifetime#'] * 3600))) . ']' : '') . '</td>
				<td>' . round($r['cost'], 2) . ' ' . $data['#config#']['curr'] . '</td>
				<td style="color:' . $color[$r['status']] . ';">' . $r['#status#'] . '</td>
				<td>' . $r['#pay_modul#'] . '</td>
				<td>' . $r['mf_timestamp'] . '</td>
			</tr>';
		}
		$html .= '</table>';
		$_tpl['onload'] .= 'wep.clickAjax(\'.goClick\');';
	}
	else
		$html .= '<messages><notice>Операций по счёту нет.</notice></messages>';
	return $html . '<br/>';
}
