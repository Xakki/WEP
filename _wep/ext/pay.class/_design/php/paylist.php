<?php
/**
 * Список счетов
 * @type Платежная система
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
function tpl_paylist($data)
{
	//TODO **************
	global $_tpl;
	setCss('/../_pay/pay');
	$html = '';
	if (count($data['#list#'])) {
		$currentBalance = $data['#users#'][$data['userId']]['balance'];
		global $PGLIST;
		//if(isset($data['#users#'][1]) and isset($PGLIST->config['sitename'])) {
		//	$data['#users#'][1]['name'] = $PGLIST->config['sitename'];
		//}
		$html .= '<table class="pay_list">
			<tr>
				<td>#
				<td>Тип
				<td>Название
				' . ($data['#showUser#'] ? '' : '<td>Плательщик <td>Получатель') . '
				<td>Статус
				<td>Сумма<br/>' . $data['#config#']['curr'] . '
				<td>Дата
				<td>Баланс
			</tr>';
		$b = 0;

		foreach ($data['#list#'] as $k => $r) {
			if (!$r['status']) {
				// onclick="return wep.JSWin({\'type\':this});"
				$r['#status#'] = '<a href="' . $data['#page#'] . '.html?payinfo=' . $r['id'] . '" title="Оплатите до ' . date('Y-m-d H:i', $r['#leftTime#']) . '">' . $r['#status#'] . '</a>';
			} elseif ($r['status'] == 1 && $r['from_user'] != $r['to_user']) {
				if ($data['userId'] == $r['from_user'])
					$b = bcsub($b, $r['cost'], 2);
				else
					$b = bcadd($b, $r['cost'], 2);
			}

			if ($data['#showUser#'] && $data['userId']) {
				if ($data['userId'] == $r['from_user'])
					$from_user = 'Вы';
				else
					$from_user = $data['#users#'][$r['from_user']]['name'] . ' [' . $data['#users#'][$r['from_user']]['id'] . ',' . $data['#users#'][$r['from_user']]['gname'] . ']';

				if ($data['userId'] == $r['to_user'])
					$to_user = 'Вы';
				else
					$to_user = $data['#users#'][$r['to_user']]['name'] . ' [' . $data['#users#'][$r['to_user']]['id'] . ',' . $data['#users#'][$r['to_user']]['gname'] . ']';
			}

			$html .= '<tr class="paylist' . $r['status'] . '">
				<td>' . $r['id'] . '
				<td>' . $r['#paytype#'] . '
				<td>' . $r['name'] . '
				' . ($data['#showUser#'] ? '' : '<td>' . $from_user . '<td>' . $to_user) . '
				<td>' . $r['#status#'] . '
				<td class="' . ($r['#sign#'] ? 'plus' : 'minus') . '">' . round($r['cost'], 2) . '
				<td>' . $r['mf_timestamp'] . '
				<td>' . ($r['status'] == 1 && $r['from_user'] != $r['to_user'] ? $b : ' - ') . '
			</tr>';
		}
		//long2ip($r['mf_ipcreate'])
		$html .= '</table>
		<h3>Итоговый баланс : ' . round($currentBalance, 2) . ' руб.</h3>';

		$html = '<h3>Начальный баланс ' . round(($currentBalance - $b), 2) . ' руб.</h3>' . $html;
	} else
		$html .= '<div class="error">Операций по счету нет.</div>';
	return $html;
}
