<?php

function tpl_list($data)
{
	global $_tpl;
	$_tpl['styles']['../default/_pay/pay'] = 1;
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
			3=>'gray',
			4=>'#a151a1',
		);
		foreach($data['#list#'] as $k=>$r) {
			if(!$r['status']) {
				if($r['#formType#']===true)
					$r['#status#'] .= ' [<a href="/_js.php?_modul=pay&_fn=showPayInfo&id='.$r['id'].'" onclick="return wep.JSWin({type:this,onclk:\'reload\'});" target="_blank">Оплатить</a>]';
				elseif($r['#formType#'])
					$r['#status#'] .= ' [<a href="'.$r['#formType#'].'" target="_blank">Оплатить</a>]';
			}
			$html .= '<tr>
				<td>'.$r['id'].'</td>
				<td>'.$r['name'].' '.(!$r['status']?'[действителен до '.date('Y-m-d H:i',($r['mf_timecr']+($r['#lifetime#']*3600))).']':'').'</td>
				<td>'.round($r['cost'],2).' '.$data['#curr#'].'</td>
				<td style="color:'.$color[$r['status']].';">'.$r['#status#'].'</td>
				<td>'.$r['#pay_modul#'].'</td>
				<td>'.$r['mf_timestamp'].'</td>
			</tr>';
		}
		$html .= '</table>';
	} else
		$html .= '<messages><notice>Операций по счёту нет.</notice></messages>';
	return $html.'<br/>';
}
