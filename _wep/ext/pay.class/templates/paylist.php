<?php

function tpl_paylist($data)
{
	global $_tpl;
	$_tpl['styles']['../default/_pay/pay'] = 1;
	$html = '';
	if(count($data['#list#'])) {
		$html .= '<h3>Начальный баланс 0 руб.</h3>';
		global $PGLIST;
		//if(isset($data['#users#'][1]) and isset($PGLIST->config['sitename'])) {
		//	$data['#users#'][1]['name'] = $PGLIST->config['sitename'];
		//}
		$html .= '<table class="pay_list">
			<tr>
				<td>#</td>
				<td>Операция</td>
				'.($data['#noUser#']?'':'<td>Плательщик / Получатель</td>').'
				<td>Статус</td>
				<td>Сумма<br/>'.$data['#curr#'].'</td>
				<td>Дата</td>
				<td>Баланс</td>
			</tr>';
		$b  = 0;
//print_r('<pre>');print_r($data);
		foreach($data['#list#'] as $k=>$r) {
			if(!$r['status']) {
				if($r['#formType#']===true)
					$r['#status#'] .= ' [<a href="/_js.php?_modul=pay&_fn=payFormBilling&id='.$r['id'].'" onclick="return wep.JSWin({\'type\':this});" target="_blank">Оплатить</a>]';
				elseif($r['#formType#'])
					$r['#status#'] .= ' [<a href="'.$r['#formType#'].'" target="_blank">Оплатить</a>]';
				$r['#status#'] .= '<br/><i>[до '.date('Y-m-d H:i',($r['mf_timecr']+($r['#lifetime#']*3600))).']</i>';
			}
			elseif($r['status']==1) {
				if($_SESSION['user']['id']==$r['creater_id'])
					$b = bcsub($b, $r['cost'],2);
				else
					$b = bcadd($b, $r['cost'],2);
			}

			if(isset($_SESSION['user']['id'])) {
				if($_SESSION['user']['id']==$r['creater_id'])
					$nm = 'user_id';
				else
					$nm = 'creater_id';
				$fromuser = $data['#users#'][$r[$nm]]['name'];
				if($data['#users#'][$r[$nm]]['level']<10 and $data['#users#'][$r[$nm]]['level']>0)
					$fromuser = $data['#users#'][$r[$nm]]['gname'].' №'.$data['#users#'][$r[$nm]]['id'].' '.$fromuser;
			}
				
			$html .= '<tr class="paylist'.$r['status'].'">
				<td>'.$r['id'].'</td>
				<td>'.$r['name'].'</td>
				'.($data['#noUser#']?'':'<td>'.$fromuser.'</td>').'
				<td>'.$r['#status#'].'</td>
				<td class="'.($r['#sign#']?'plus':'minus').'">'.round($r['cost'],2).'</td>
				<td>'.$r['mf_timestamp'].'</td>
				<td>'.($r['status']==1?$b:'').'</td>
			</tr>';
		}//long2ip($r['mf_ipcreate'])
		$html .= '</table>
		<div>Баланс : '.round($data['#users#'][$_SESSION['user']['id']]['balance'],2).' руб.</div>';
	} else
		$html .= '<div class="error">Операций по счету нет.</div>';
	return $html;
}
