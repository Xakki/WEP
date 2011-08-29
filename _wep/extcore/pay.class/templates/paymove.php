<?php

function tpl_paymove($data)
{
	$html = '';
	if(isset($data['#pay#']['respost']) and $data['#pay#']['respost'][0]==1) {
		global $PGLIST;
		$PGLIST->pageinfo['template'] = 'waction';
		//$html = $HTML->transformPHP($DATA['formcreat'],'messages');
		if(isset($_POST['plus']))
			$res = 'Счёт пользователя `'.$data['#pay#']['users'][$_POST['users']]['name'].'` пополнен!';
		else
			$res = 'Со счёта пользователя успешно сняты средства.';
		$html .= '<div class="messages"><div class="ok">'.$res.'</div></div>';
	} else {
		if(isset($data['#pay#']['respost'])) {
			if($data['#pay#']['respost'][0]==-1)
				$res = 'Указан не существующий клиент или клиент отключён';
			elseif($data['#pay#']['respost'][0]==-2)
				$res = ''.(isset($_POST['plus'])?'У вас':'У пользователя `'.$data['#pay#']['users'][$_POST['users']]['name'].'`').' не достаточно средств на счету ['.$data['#pay#']['respost'][1].'].';
			elseif($data['#pay#']['respost'][0]==-5)
				$res = 'Сумма должна быть больше нуля';
			else
				$res = 'Ошибка операции';
			$html .= '<div class="messages"><div class="error">'.$res.'</div></div>';
		}
		$html .= '<form method="POST">';
		if(isset($data['#pay#']) and count($data['#pay#'])) {
			$html .= '<div>Клиент</div>
			<select name="users">';
			foreach($data['#pay#']['users'] as $k=>$r)
				$html .= '<option value="'.$k.'">'.$r['name'].'['.(int)$r['balance'].']('.$r['firma'].')</option>';
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
