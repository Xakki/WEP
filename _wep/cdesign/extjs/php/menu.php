<?php
	function tpl_menu(&$data) {
		$html = '';
		if(isset($data) and count($data)) {
			//$html .= '<div class="menu">';
			//$last = count($data);
			foreach($data as $k=>$r) {
				if($r['sel'])
					$r['name'] = '<span>'.$r['name'].'</span>';
				$html .= '<a href="'.$r['href'].'" '.$r['attr'].'>'.$r['name'].'</a>';
				if(count($r['#item#'])) {
					$html .= tpl_menu($r['#item#']);
				}
			}
			//$html .= '</div>';
		}
		return $html;
	}
