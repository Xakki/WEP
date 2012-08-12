<?php
	function tpl_basketlist(&$data) {
		global $_CFG, $HTML;
		$html = '';
		$url = explode('?',$_SERVER['REQUEST_URI']);
		if(isset($data['#list#']) and count($data['#list#'])) {
			$html .= '<table class="basketlist"><tr> 
				<th>№
				<th>Дата
				<th>Сумма
				<th>Метод оплаты
				<th>Товары
				<th>Статус
			</tr>';
			$summ = 0;
			foreach($data['#list#'] as $r) {
				$prod = '<ul>';
				foreach($r['#shopbasketitem#'] as $p) {
					$prod .= '<li>'.$p['product_name'].' ['.$p['count'].' шт. по '.$p['cost_item'].' '.$data['#curr#'].']';
				}
				$prod .= '</ul>';
				if($r['pay_id'])
					$link = '<a href="/_js.php?_modul=pay&_fn=showPayInfo&id='.$r['pay_id'].'" onclick="return wep.JSWin({type:this,onclk:\'reload\'});" target="_blank">'.$r['#laststatus#'].'</a>';
				else
					$link = 'Забронированно <a href="'.$url[0].'?basketpay='.$r['id'].'">Оформить заказ</a>';
				$html .= '
				<tr data-id="'.$r['id'].'">
					<td>'.$r['id'].'
					<td>'.static_main::_date($_CFG['wep']['timeformat'],$r['mf_timecr']).'
					<td class="summ"><span>'.$r['summ'].'</span> '.$data['#curr#'].'
					<td>'.$r['#paytype#'].'
					<td class="basketlist-basketitem">'.$prod.'
					<td>'.$link.'
				</tr>';
			}
			$html .= '</table>';
		} 
		else
			$html = '<div class="basket">'.static_render::message('Корзина пуста').'</div>';
		
		return $html;
	}
