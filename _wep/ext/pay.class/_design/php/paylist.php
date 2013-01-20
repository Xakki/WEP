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
				<td>Название
				<td>Тип
				'.($data['#showUser#']?'':'<td>Плательщик <td>Получатель').'
				<td>Статус
				<td>Сумма<br/>'.$data['#curr#'].'
				<td>Дата
				<td>Баланс
			</tr>';
		$b  = 0;

		foreach($data['#list#'] as $k=>$r) 
		{
			if(!$r['status']) 
			{
				$r['#status#'] .= ' [<a href="?payid='.$r['id'].'" onclick="return wep.JSWin({\'type\':this});" target="_blank">Оплатить</a>]
				<br/><i>[до '.date('Y-m-d H:i',($r['mf_timecr']+($r['#lifetime#']*3600))).']</i>';
			}
			elseif($r['status']==1) 
			{
				if($data['userId']==$r['creater_id'])
					$b = bcsub($b, $r['cost'],2);
				else
					$b = bcadd($b, $r['cost'],2);
			}

			if($data['#showUser#'] && $data['userId']) 
			{
				if($data['userId']==$r['from_user'])
					$from_user = 'Вы';
				else
					$from_user = $data['#users#'][$r['from_user']]['name'].' ['.$data['#users#'][$r['from_user']]['id'].','.$data['#users#'][$r['from_user']]['gname'].']';
				
				if($data['userId']==$r['to_user'])
					$to_user = 'Вы';
				else
					$to_user = $data['#users#'][$r['to_user']]['name'].' ['.$data['#users#'][$r['to_user']]['id'].','.$data['#users#'][$r['to_user']]['gname'].']';
			}
				
			$html .= '<tr class="paylist'.$r['status'].'">
				<td>'.$r['id'].'
				<td>'.$r['name'].'
				<td>'.$r['#paytype#'].'
				'.($data['#showUser#']?'':'<td>'.$from_user.'<td>'.$to_user).'
				<td>'.$r['#status#'].'
				<td class="'.($r['#sign#']?'plus':'minus').'">'.round($r['cost'],2).'
				<td>'.$r['mf_timestamp'].'
				<td>'.($r['status']==1?$b:'').'
			</tr>';
		}//long2ip($r['mf_ipcreate'])
		$html .= '</table>
		<div>Баланс : '.round($data['#users#'][$data['userId']]['balance'],2).' руб.</div>';
	} else
		$html .= '<div class="error">Операций по счету нет.</div>';
	return $html;
}
