<?php
	function tpl_menu(&$data) {
		$html = '';
		if(isset($data['#item#']) and count($data['#item#'])) {
			$html .= '<div class="menu">';
			//$last = count($data);
			foreach($data['#item#'] as $k=>$r) {
				if($r['sel']==2)
					$r['attr'] .= ' class="selected"';
				$html .= '<a href="'.$r['href'].'" '.$r['attr'].'>'.$r['name'].'</a>';
				/*if(strpos($r['attr'],'style="'))
					$r['attr'] = str_replace('style="','style="width:'.$prs.'%;',$r['attr']);
				else
					$r['attr'] .= ' style="width:'.$prs.'%;"';*/
				
				if(isset($r['#item#']) and count($r['#item#'])) {
					$html .= tpl_menu($r);
				}
			}
			$html .= '</div>';
		}
		return $html;
	}
