<?php
	function tpl_shopMenu(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			global $_tpl,$HTML;
			//$_tpl['styles']['shop'] = array('/'.static_main::relativePath(dirname(dirname(__FILE__))).'/style/menu.css');
			$_tpl['styles']['/_shop/style/shopMenu'] = 1;

			$html = '<div class="shop-menu">
			'.($data['#title#']?'<h3>'.$data['#title#'].'</h3>':'').'
			';
			$html .= tpl_shop_rev($data['#item#'],'',$data['#page#']);
			$html .= '</div>';
		}
		return $html;
	}
	function tpl_shop_rev(&$data,$pref='',$pgid=0) {
		$html = '<ul>';
		foreach($data as $k=>$r) {
			if(isset($r['#item#']) and count($r['#item#'])) {
				$html .= '<li class="sub">';
				//$pref .= ' - ';
				$sub = tpl_shop_rev($r['#item#'],$pref,$pgid);
			}
			else {
				$html .= '<li>';
				$sub = '';
			}
			$html .= '<a href="/'.$pgid.'/'.$r['path'].'.html" class="'.($r['#sel#']?'selected':'').'">'.$r['name'].'</a>'.$sub.'</li>';
		}
		$html .= '</ul>';
		return $html;
	}
