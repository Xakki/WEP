<?php
/**
 * Оформление заказов 
 * @type Магазин Корзина
 * @tags basketitemlist
 * @ico default.png
 * @author Xakki
 * @version 0.1
 * @return string html
 */
	function tpl_basketitemlist(&$data) {
		global $_tpl, $_CFG;
		$html = '';

		if(isset($data['#list#']) and count($data['#list#'])) {
			global $HTML;
			$html .= '<table class="basket-list-item"><tr> 
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
					$img = MY_THEME.'_shop/img/nofoto.gif';
				$html .= '<tr data-id="'.$r['product_id'].'" class="'.($r['checked']?'checked':'').'">
					<td><input type="checkbox" '.($r['checked']?'checked="checked"':'').'>
					<td><img src="/'.$img.'" alt="'.$r['name'].'"/>
					<td><a href="'.$data['#pageCatalog#'].'/product/alias_'.$r['product_id'].'.html" target="_blank">'.$r['name'].'</a>
						'.(isset($r['sale'])?'<span class="prodlable sale" title="'.$r['sale']['name'].'">&#160;</span>':'').'
					<td><span>'.$r['cost'].'</span> '.$data['#curr#'].'
					<td class="count"><input type="number" min="1" max="50" value="'.$r['count'].'"/>
					<td class="summ"><span>'.($r['cost']*$r['count']).'</span> '.$data['#curr#'].'
					<td class="dellink">
						<a href="##vkorziny" title="Удалить из корзины">
							<img src="'.MY_THEME.'_shop/img/basket-del.png" alt="Удалить из корзины"/>
						</a>
				</tr>';
				if($r['checked'])
					$summ += ($r['cost']*$r['count']);
			}

			if(count($data['#delivery#'])>1) {
				$sitem = '<div id="typedelivery">';
				foreach($data['#delivery#'] as $rd) {
					if($rd['selected'] or !isset($valD))
						$valD = $rd['id'];
					$costText = '';
					if($rd['cost']>0)
						$costText = ' - '.$rd['cost'].' '.$data['#curr#'].($rd['minsumm']?', бесплатная доставка от '.$rd['minsumm'].' '.$data['#curr#']:'').'';
					$sitem .= '
						<label '.($rd['selected']?'class="select"':'').'>
							<i>'.$rd['name'].$costText.'</i>
							<input required="required" type="radio" id="typedeliveryradio'.$rd['id'].'"  name="typedelivery" value="'.$rd['id'].'" data-cost="'.$rd['cost'].'" data-minsumm="'.$rd['minsumm'].'" '.($rd['selected']?'checked="checked"':'').'/>
						</label>';
				}
				$sitem .= '</div>';
				//$_tpl['onload'] .= '$( "#typedelivery" ).buttonset();';
				//$_CFG['fileIncludeOption']['jquery-ui'] = true;
			}else {
				$temp = current($data['#delivery#']);
				$valD = $temp['id'];
				$sitem .= '<span>'.$temp['name'].' - '.$temp['cost'].' '.$data['#curr#'].'</span>';
			}
			if($data['#delivery#'][$valD]['minsumm']>=$summ or !$data['#delivery#'][$valD]['minsumm'])
				$summ += $data['#delivery#'][$valD]['cost'];

			$html .= '</table>
			<form type="GET">
				<div class="basketdiv"><div class="caption">Тип доставки</div> '.$sitem.'</div>
				<div class="basketdiv"><div class="caption">Итого:</div> <span id="basketitogo">'.$summ.'</span> '.$data['#curr#'].'</div>
				<div class="basketdiv"><input type="submit" class="sbmt" value="Оформить заказ"></div>
			</form>
			';
		} else
			$html = '<div class="basket">'.static_render::message('Корзина пуста').'</div>';
		return $html;
	}
