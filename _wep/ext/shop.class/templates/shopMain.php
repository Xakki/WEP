<?php
	function tpl_shopMain(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			$html = '<div class="shopmain">';
			$html .= tpl_shopmain_rev($data['#item#'],'',$data['pgid']);
			$html .= '</div>';
		}
		return $html;
	}
	function tpl_shopmain_rev(&$data,$pref='',$pgid=0) {
		$html = '';
		foreach($data as $k=>$r) {
			$html .= '<div class="shop_item">';
			if($r['img_shop']) {
				$html .= '<a href="/'.$r['path'].'/'.$pgid.'.html" class="itemimg"><img src="'.$r['img_shop'].'" alt="'.$r['name'].'"/></a>';
			}
			$html .= '<a href="/'.$r['path'].'/'.$pgid.'.html" class="itemname'.($r['#sel#']?' selected':'').'">'.$r['name'].'</a>';
			/*if(isset($r['#item#']) and count($r['#item#'])) {
				$pref .= ' - ';
				$html .= tpl_shopmain_rev($r['#item#'],$pref,$pgid);
			}*/
			$html .= '</div>';
		}
		return $html;
	}
