<?php

function tpl_paylist($data)
{
	$html = '';
	if(count($data['#list#'])) {
		$html .= '<h3>Начальный баланс 0 руб.</h3>';
		if(isset($_SESSION['user']['id']))
			$data['#users#'][$_SESSION['user']['id']]['name'] = '-Вы-';
		global $PGLIST;
		if(isset($data['#users#'][1]) and isset($PGLIST->config['sitename'])) {
			$data['#users#'][1]['name'] = $PGLIST->config['sitename'];
		}
		$html .= '<table border="1">
			<tr>
				<td>#</td>
				<td>Операция</td>
				<td>От кого</td>
				<td>Кому</td>
				<td>Сколько</td>
				<td>Время</td>
				<td>Баланс</td>
			</tr>';
		$b  = 0;
		foreach($data['#list#'] as $k=>$r) {
			if($_SESSION['user']['id']==$r['creater_id'])
				$b  -= $r['cost'];
			else
				$b  += $r['cost'];
			$html .= '<tr>
				<td>'.$r['id'].'</td>
				<td>'.$r['name'].'</td>
				<td>'.$data['#users#'][$r['creater_id']]['name'].'</td>
				<td>'.$data['#users#'][$r['user_id']]['name'].'</td>
				<td>'.round($r['cost'],2).' руб.</td>
				<td>'.$r['mf_timestamp'].'</td>
				<td>'.$b.'</td>
			</tr>';
		}//long2ip($r['mf_ipcreate'])
		$html .= '</table>
		<div>Баланс : '.round($data['#users#'][$_SESSION['user']['id']]['balance'],2).' руб.</div>';
	} else
		$html .= '<div class="error">Операций по счету нет.</div>';
	return $html;
}
