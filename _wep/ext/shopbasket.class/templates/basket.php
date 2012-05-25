<?php
	function tpl_basket(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			global $_tpl,$HTML,$PGLIST;
			$_tpl['styles']['../'.$HTML->_design.'/_shop/style/shopMain'] = 1;
			$_tpl['script']['../'.$HTML->_design.'/_shop/script/shop'] = 1;
			if(!isset($_tpl['onload'])) $_tpl['onload'] = '';
			$_tpl['onload'] .= ' wep.shop.basketContenId = '.$PGLIST->contentID.';';

			$html .= '<div id="basketBlock">';
			if($data['cnt'])
				$html .= '<a href="'.$data['#page#'].'.html">В корзине</a> '.$data['cnt'].' товаров на сумму '.$data['summ'];
			else
				$html .= 'Корзина пуста';
			$html .= '</div>';
		}
		return $html;
	}
