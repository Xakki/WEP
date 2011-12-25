<?php
	function tpl_menu(&$data,$fl=false) {
		$html = '';
		if(isset($data['#item#']) and count($data['#item#'])) {
			if($fl)
				$html .= '<ul>';
			else
				$html .= '<ul class="menutop">';
			foreach($data['#item#'] as $k=>$r) {
				$html .= '<li class="'.($r['sel']?'selmenu':'').'"><a href="'.$r['href'].'" '.$r['attr'].'>'.$r['name'].'</a>';
				if(isset($r['#item#']) and count($r['#item#'])) {
					$html .= '<div class="menutop_sub"><div class="menutop_subimg"></div>'.tpl_menu($r,true).'</div>';
				}
				$html .= '</li>';
			}
			$html .= '</ul>';
		}
		return $html;
	}
