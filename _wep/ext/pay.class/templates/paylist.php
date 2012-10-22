<?php

function tpl_paylist($data)
{
	global $_tpl;
	$_tpl['styles']['/_pay/pay'] = 1;
	$html = '';
	if(count($data['#list#'])) {
		$html .= '<h3>Начальный баланс 0 руб.</h3>';
		global $PGLIST;
		//if(isset($data['#users#'][1]) and isset($PGLIST->config['sitename'])) {
		//	$data['#users#'][1]['name'] = $PGLIST->config['sitename'];
		//}
		$html .= '<table class="pay_list">
			<tr>
				<td>#
				<td>Операция
				'.($data['#noUser#']?'':'<td>Плательщик / Получатель').'
				<td>Статус
				<td>Сумма<br/>'.$data['#curr#'].'
				<td>Дата
				<td>Баланс
			</tr>';
		$b  = 0;

		foreach($data['#list#'] as $k=>$r) {
			if(!$r['status']) {
				if($r['#formType#']===true)
					$r['#status#'] .= ' [<a href="/_js.php?_modul=pay&_fn=showPayInfo&id='.$r['id'].'" onclick="return wep.JSWin({\'type\':this});" target="_blank">Оплатить</a>]';
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
				<td>'.$r['id'].'
				<td>'.$r['name'].'
				'.($data['#noUser#']?'':'<td>'.$fromuser).'
				<td>'.$r['#status#'].'
				<td class="'.($r['#sign#']?'plus':'minus').'">'.round($r['cost'],2).'
				<td>'.$r['mf_timestamp'].'
				<td>'.($r['status']==1?$b:'').'
			</tr>';
		}//long2ip($r['mf_ipcreate'])
		$html .= '</table>
		<div>Баланс : '.round($data['#users#'][$_SESSION['user']['id']]['balance'],2).' руб.</div>';
	} else
		$html .= '<div class="error">Операций по счету нет.</div>';
	return $html;
}
