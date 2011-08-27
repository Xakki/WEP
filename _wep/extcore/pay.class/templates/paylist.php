<?php

function tpl_paylist($data)
{
	$html = '';
	if(count($data['#list#'])) {
		$html .= '<table border="1">
			<tr>
				<td>#</td>
				<td>Операция</td>
				<td>От кого</td>
				<td>Кому</td>
				<td>Сколько</td>
				<td>Время</td>
				<td>IP</td>
			</tr>';
		foreach($data['#list#'] as $k=>$r) {
			$html .= '<tr>
				<td>'.$r['id'].'</td>
				<td>'.$r['name'].'</td>
				<td>'.$data['#users#'][$r['creater_id']]['name'].'</td>
				<td>'.$data['#users#'][$r['user_id']]['name'].'</td>
				<td>'.round($r['cost'],2).' руб.</td>
				<td>'.$r['mf_timestamp'].'</td>
				<td>'.long2ip($_SERVER['REMOTE_ADDR']).'</td>
			</tr>';
		}
		$html .= '</table>
		<div>Баланс : '.round($data['#users#'][$_SESSION['user']['id']]['balance'],2).' руб.</div>';
	}
	return $html;
}
