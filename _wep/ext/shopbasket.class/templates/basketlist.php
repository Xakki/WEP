<?php
	function tpl_basketlist(&$data) {
		global $_CFG, $HTML;
		$html = '';
		$url = explode('?',$_SERVER['REQUEST_URI']);

		if(isset($data['messages'])) {
			$html .= $HTML->transformPHP($data['messages'], '#pg#messages');
		}

		if(isset($data['#item#'])) {
			$html .= tpl_basketlist_item($data);
		}
		elseif(isset($data['#list#']) and count($data['#list#'])) {
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
					$link = '<a href="/_js.php?_modul=pay&_fn=showPayInfo&id='.$r['pay_id'].'" onclick="return wep.JSWin({type:this});" target="_blank">'.$r['#laststatus#'].'</a>';
				else
					$link = 'Забронированно <a href="'.$url[0].'?basketpay='.$r['id'].'">Оформить заказ</a>';
				$html .= '
				<tr data-id="'.$r['id'].'">
					<td><a href="'.$data['#page#'].'/'.$r['id'].'.html">'.$r['id'].'</a>
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

	function tpl_basketlist_item(&$data) {
		if(count($data['#item#'])) {
			$itemB = &$data['#item#'];
			$html = '
			<table class="basketInfo">
				<tr> <td>ФИО <td><b>'.$itemB['fio'].'</b>
				<tr> <td>Адрес доставки <td><b>'.$itemB['address'].'</b>
				<tr> <td>Телефон <td><b>'.$itemB['phone'].'</b>
				<tr> <td>Сумма покупки <td><b>'.$itemB['summ'].' '.$data['#curr#'].'</b>
				<tr> <td>Метод оплаты <td><b>'.$itemB['#paytype#'].'</b>
				<tr> <td>Доставка <td><b>'.$itemB['#delivery#']['name'].'</b>
				<tr> <td>Статус <td><b>'.$itemB['#laststatus#'].'</b>
			</table>
			<h3>Покупки</h3>
			<div class="basketItems">
				<table>
					<tr><th>Код <th>Наименование <th>Цена '.$data['#curr#'].'<th>Кол-во <th>Сумма '.$data['#curr#'].'';
				foreach($itemB['#shopbasketitem#'] as $r) {
					$html .= '<tr> <td>'.$r['product_id'].' <td>'.$r['product_name'].'<td>'.$r['cost_item'].'<td>'.$r['count'].'<td>'.($r['count']*$r['cost_item']);
				}
				$html .= '</table>
			</div>';
			//print_r('<pre>');print_r($data);
		}
		else {
			$html = '<div class="basket">'.static_render::message('Не верный адрес страницы либо данный заказ был удален').'</div>';
		}
		return $html;
	}
//'.($data['#moder#']?'<td><a href="'.$data['#pageUser#'].'/'.$r['creater_id'].'.html" target="_blank">'.$r['uname'].'</a>':'').'