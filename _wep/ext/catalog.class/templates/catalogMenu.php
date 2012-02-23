<?php
	function tpl_catalogMenu(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			$html = '<div class="leftblock_item"><div class="leftblock_catalog">';
			$html .= tpl_catalog_rev($data['#item#'],'',$data['pgid']);
			$html .= '</div></div>';
		}
		return $html;
	}
	function tpl_catalog_rev(&$data,$pref='',$pgid=0) {
		$html = '<ul>';
		foreach($data as $k=>$r) {
			$html .= '<li>';
			if($r['img_catalog']) {
				$html .= '<img src="'.$r['s_img_catalog'].'" alt="'.$r['name'].'"/>';
			}
			$html .= '<a href="/'.$r['path'].'/'.$pgid.'.html" class="'.($r['#sel#']?'selected':'').'">';
			$html .= $r['name'].'</a>';
			if(isset($r['#item#']) and count($r['#item#'])) {
				//$pref .= ' - ';
				$html .= tpl_catalog_rev($r['#item#'],$pref,$pgid);
			}
			$html .= '</li>';
		}
		$html .= '</ul><div class="clear"></div>';
		return $html;
	}
