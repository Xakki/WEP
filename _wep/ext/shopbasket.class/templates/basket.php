<?php
	function tpl_basket(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			global $_tpl,$HTML,$PGLIST;
			$_tpl['styles']['../'.$HTML->_design.'/_shop/style/shopMain'] = 1;
			$_tpl['script']['../'.$HTML->_design.'/_shop/script/shop'] = 1;
			if(!isset($_tpl['onload'])) $_tpl['onload'] = '';
			$_tpl['onload'] .= ' wep.shop.basketContenId = '.$PGLIST->contentID.'; wep.shop.pageBasket="'.$data['#page#'].'.html";';

			$html .= '<div id="basketBlock"><i class="ico"></i>';
			if($data['cnt'])
				$html .= '<p>Товаров '.$data['cnt'].' шт.</p>
				<p>'.$data['summ'].' '.$data['#curr#'].'</p>';
			else
				$html .= '<p class="emptybasket">Корзина пуста</p>';
			$html .= '</div>';
		}
		return $html;
	}
