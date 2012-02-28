<?php
	function tpl_shopMenu(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			//global $_tpl;
			//$_tpl['styles']['shop'] = array('/'.static_main::relativePath(dirname(__DIR__)).'/style/menu.css');

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
			$html .= '<li>';
			$html .= '<a href="/'.$r['path'].'/'.$pgid.'.html" class="'.($r['#sel#']?'selected':'').'">';
			$html .= $r['name'].'</a>';
			if(isset($r['#item#']) and count($r['#item#'])) {
				//$pref .= ' - ';
				$html .= tpl_shop_rev($r['#item#'],$pref,$pgid);
			}
			$html .= '</li>';
		}
		$html .= '</ul><div class="clear"></div>';
		return $html;
	}
