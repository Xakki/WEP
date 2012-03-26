<?php

function tpl_list($data)
{
	$html = '';
	if(count($data['#list#'])) {
		$html .= '<h3>'.$data['#title#'].'</h3>
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
			0=>'auto',
			1=>'green',
			2=>'red',
		);
		foreach($data['#list#'] as $k=>$r) {
			$html .= '<tr>
				<td>'.$r['id'].'</td>
				<td>'.$r['name'].'</td>
				<td>'.round($r['cost'],2).' руб.</td>
				<td style="color:'.$color[$r['status']].';">'.$r['#status#'].'</td>
				<td>'.$r['#pay_modul#'].'</td>
				<td>'.$r['mf_timestamp'].'</td>
			</tr>';
		}
		$html .= '</table>';
	} else
		$html .= '<messages><notice>Операций по счету нет.</notice></messages>';
	return $html.'<br/>';
}
