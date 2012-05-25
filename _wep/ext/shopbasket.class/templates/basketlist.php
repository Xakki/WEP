<?php
	function tpl_basketlist(&$data) {
		$html = '';
		//print_r('<pre>');print_r($data);
		if(isset($data['#list#']) and count($data['#list#'])) {
			global $_tpl,$HTML;
			$_tpl['styles']['../'.$HTML->_design.'/_shop/style/shopBasket'] = 1;
			$html .= '<table class="basketlist"><tr> 
				<th>Заказать
				<th>Фото
				<th>Наименование товара
				<th>Цена
				<th>Кол-во
				<th>Сумма
				<th>
			</tr>';
			$summ = 0;
			foreach($data['#list#'] as $r) {
				if(isset($r['s_img_product']) and $r['s_img_product']) {
					$img = $r['s_img_product'];
				} else
					$img = '_design/'.$HTML->_design.'/_shop/img/nofoto.gif';
				$html .= '<tr data-id="'.$r['id_product'].'" class="'.($r['checked']?'checked':'').'">
					<td><input type="checkbox" '.($r['checked']?'checked="checked"':'').'>
					<td><img src="/'.$img.'" alt="'.$r['name'].'"/>
					<td><a href="'.$data['#pageCat#'].'/product/alias_'.$r['id_product'].'.html" target="_blank">'.$r['name'].'</a>
					<td><span>'.$r['cost'].'</span> '.$data['#curr#'].'
					<td class="count"><input type="number" min="1" max="50" value="'.$r['count'].'"/>
					<td class="summ"><span>'.$r['summ'].'</span> '.$data['#curr#'].'
					<td class="dellink">
						<a href="##vkorziny" title="Удалить из корзины">
							<img src="/_design/'.$HTML->_design.'/_shop/img/basket-del.png" alt="Удалить из корзины"/>
						</a>
				</tr>';
				if($r['checked'])
					$summ += $r['summ'];
			}
			if(count($data['#delivery#'])>1) {
				$sitem = '<select name="typedelivery">';
				foreach($data['#delivery#'] as $rd) {
					if(!isset($valD)) $valD = $rd['id'];
					$sitem .= '<option value="'.$rd['id'].'" data-cost="'.$rd['cost'].'" data-minsumm="'.$rd['minsumm'].'">'.$rd['name'].' - '.$rd['cost'].' '.$data['#curr#'].($rd['minsumm']?', бесплатная доставка от '.$rd['minsumm'].' '.$data['#curr#']:'');
				}
				$sitem .= '</select>';
			}else {
				$temp = current($data['#delivery#']);
				$valD = $temp['id'];
				$sitem .= '<span>'.$temp['name'].' - '.$temp['cost'].' '.$data['#curr#'].'</span>';
			}
			if($data['#delivery#'][$valD]['minsumm']>=$summ or !$data['#delivery#'][$valD]['minsumm'])
				$summ += $data['#delivery#'][$valD]['cost'];

			$html .= '</table>
			<form type="GET">
				<div class="basketdiv">Тип доставки '.$sitem.'</div>
				<div class="basketdiv">Итого: <span id="basketitogo">'.$summ.'</span> '.$data['#curr#'].'</div>
				<div class="basketdiv"><input type="submit" value="Оформить заказ"></div>
			</form>
			';
		} else
			$html = '<div class="basket">Корзина пуста</div>';
		return $html;
	}