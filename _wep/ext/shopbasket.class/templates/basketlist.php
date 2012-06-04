<?php
	function tpl_basketlist(&$data) {
		global $_CFG;
		$html = '';
		//print_r('<pre>');print_r($data);
		if(isset($data['#list#']) and count($data['#list#'])) {
			$html .= '<table class="basketlist"><tr> 
				<th>№
				<th>Дата
				<th>Сумма
				<th>Тип платежа
				<th>Статус
			</tr>';
			$summ = 0;
			foreach($data['#list#'] as $r) {
				$html .= '
				<tr data-id="'.$r['id'].'">
					<td>'.$r['id'].'
					<td>'.static_main::_date($_CFG['wep']['timeformat'],$r['mf_timecr']).'
					<td class="summ"><span>'.$r['summ'].'</span> '.$data['#curr#'].'
					<td>'.$r['#paytype#'].'
					<td>'.$r['#laststatus#'].'
				</tr>';
			}
			$html .= '</table>';
		} 
		else
			$html = '<div class="basket">Корзина пуста</div>';
		return $html;
	}
