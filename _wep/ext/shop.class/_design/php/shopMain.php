<?php
	function tpl_shopMain(&$data) {
		$html = '';
		if(isset($data) and count($data)) 
		{
			setCss('/_shop/style/shopMain');

			$html = '<div class="shopmain">';
			$html .= tpl_shopmain_rev($data['#item#'],'',$data['#page#']);
			$html .= '</div>';
		}
		return $html;
	}
	function tpl_shopmain_rev(&$data,$pref='',$pgid=0) {
		$html = '';
		foreach($data as $k=>$r) {
			$html .= '<div class="shop_item">';
			if($r['img']) {
				$html .= '<a href="/'.$pgid.'/'.$r['path'].'.html" class="itemimg"><img src="'.$r['img'].'" alt="'.$r['uiname'].'"/></a>';
			}
			$html .= '<a href="/'.$pgid.'/'.$r['path'].'.html" class="itemname'.($r['#sel#']?' selected':'').'">'.$r['uiname'].'</a>';
			/*if(isset($r['#item#']) and count($r['#item#'])) {
				$pref .= ' - ';
				$html .= tpl_shopmain_rev($r['#item#'],$pref,$pgid);
			}*/
			$html .= '</div>';
		}
		return $html;
	}
