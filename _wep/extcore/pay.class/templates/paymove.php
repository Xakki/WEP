<?php

function tpl_paymove($data)
{
	$html = '';
	if(isset($data['#pay#']['respost']) and $data['#pay#']['respost']) {
		global $PGLIST;
		$PGLIST->pageinfo['template'] = 'waction';
		//$html = $HTML->transformPHP($DATA['formcreat'],'messages');
		if($data['#pay#']['factor'])
			$res = 'Счёт пополнен!';
		else
			$res = 'Со счёта пользователя успешно сняты средства.';
		$html .= '<div>'.$res.'</div>';
	} else {
		if(isset($data['#pay#']['respost'])) {
			if($data['#pay#']['respost']==-1)
				$res = 'Ошибка операции';
			if($data['#pay#']['respost']==-2)
				$res = 'Сумма должна быть больше нуля';
			else
				$res = 'Указан не существующий клиент или клиент отключён';
		}
		$html .= '<form method="POST">';
		if(isset($data['#pay#']) and count($data['#pay#'])) {
			$html .= '<div>Клиент</div>
			<select name="users">';
			foreach($data['#pay#']['users'] as $k=>$r)
				$html .= '<option value="'.$r['id'].'">'.$r['name'].'['.(int)$r['balance'].']('.$r['firma'].')</option>';
			$html .= '</select>';
		}
		$html .= '<div>Сумма</div> <input type="text" value="0" name="pay"/>
		<div>
			<input type="submit" value="Пополнить" name="plus">
			<input type="submit" value="Снять со счёта" name="minus">
		</div>
		</form>';
	}
	return $html;
}
