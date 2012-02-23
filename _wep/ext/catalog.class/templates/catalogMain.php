<?php
	function tpl_catalogMain(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			$html = '<div class="catalogmain">';
			$html .= tpl_catalogmain_rev($data['#item#'],'',$data['pgid']);
			$html .= '</div>';
		}
		return $html;
	}
	function tpl_catalogmain_rev(&$data,$pref='',$pgid=0) {
		$html = '';
		foreach($data as $k=>$r) {
			$html .= '<div class="catalog_item">';
			if($r['img_catalog']) {
				$html .= '<a href="/'.$r['path'].'/'.$pgid.'.html" class="itemimg"><img src="'.$r['s_img_catalog'].'" alt="'.$r['name'].'"/></a>';
			}
			$html .= '<a href="/'.$r['path'].'/'.$pgid.'.html" class="itemname'.($r['#sel#']?' selected':'').'">'.$r['name'].'</a>';
			/*if(isset($r['#item#']) and count($r['#item#'])) {
				$pref .= ' - ';
				$html .= tpl_catalogmain_rev($r['#item#'],$pref,$pgid);
			}*/
			$html .= '</div>';
		}
		return $html;
	}
